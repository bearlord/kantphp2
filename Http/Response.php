<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Http;

use Kant\Kant;
use Kant\Http\BaseResponse;
use Kant\Http\Formatter\ResponseFormatterInterface;
use Kant\Exception\InvalidConfigException;
use Kant\Exception\InvalidParamException;
use Kant\Exception\HttpException;

class Response extends BaseResponse
{

    use ResponseTrait;

    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';

    /**
     *
     * @var string the response format. This determines how to convert [[data]] into [[content]]
     *      when the latter is not set. The value of this property must be one of the keys declared in the [[formatters] array.
     *      By default, the following formats are supported:
     *     
     *      - [[FORMAT_RAW]]: the data will be treated as the response content without any conversion.
     *      No extra HTTP header will be added.
     *      - [[FORMAT_HTML]]: the data will be treated as the response content without any conversion.
     *      The "Content-Type" header will set as "text/html".
     *      - [[FORMAT_JSON]]: the data will be converted into JSON format, and the "Content-Type"
     *      header will be set as "application/json".
     *      - [[FORMAT_JSONP]]: the data will be converted into JSONP format, and the "Content-Type"
     *      header will be set as "text/javascript". Note that in this case `$data` must be an array
     *      with "data" and "callback" elements. The former refers to the actual data to be sent,
     *      while the latter refers to the name of the JavaScript callback.
     *      - [[FORMAT_XML]]: the data will be converted into XML format. Please refer to [[XmlResponseFormatter]]
     *      for more details.
     *     
     *      You may customize the formatting process or support additional formats by configuring [[formatters]].
     * @see formatters
     */
    public $format = self::FORMAT_HTML;

    /**
     *
     * @var array the formatters for converting data into the response content of the specified [[format]].
     *      The array keys are the format names, and the array values are the corresponding configurations
     *      for creating the formatter objects.
     * @see format
     * @see defaultFormatters
     */
    public $formatters = [];

    /**
     *
     * @var mixed the original response data. When this is not null, it will be converted into [[content]]
     *      according to [[format]] when the response is being sent out.
     * @see content
     */
    public $data;

    /**
     *
     * @var string the response content. When [[data]] is not null, it will be converted into [[content]]
     *      according to [[format]] when the response is being sent out.
     * @see data
     */
    public $content;

    /**
     *
     * @var resource|array the stream to be sent. This can be a stream handle or an array of stream handle,
     *      the begin position and the end position. Note that when this property is set, the [[data]] and [[content]]
     *      properties will be ignored by [[send()]].
     */
    public $stream;

    /**
     *
     * @var string the charset of the text response. If not set, it will use
     *      the value of [[Application::charset]].
     */
    public $charset;

    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
        if ($this->charset === null) {
            $this->charset = Kant::$app->charset;
        }
        $this->formatters = array_merge($this->defaultFormatters(), $this->formatters);
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Factory method for chainability.
     *
     * Example:
     *
     * return Response::create($body, 200)
     * ->setSharedMaxAge(300);
     *
     * @param mixed $content
     *            The response content, see setContent()
     * @param int $status
     *            The response status code
     * @param array $headers
     *            An array of response headers
     *            
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }

    /**
     *
     * @return array the formatters that are supported by default
     */
    protected function defaultFormatters()
    {
        return [
            self::FORMAT_HTML => 'Kant\Http\Formatter\HtmlResponseFormatter',
            self::FORMAT_XML => 'Kant\Http\Formatter\XmlResponseFormatter',
            self::FORMAT_JSON => 'Kant\Http\Formatter\JsonResponseFormatter',
            self::FORMAT_JSONP => [
                'class' => 'Kant\Http\Formatter\JsonResponseFormatter',
                'useJsonp' => true
            ]
        ];
    }

    /**
     * Ready for sending the response.
     * The default implementation will convert [[data]] into [[content]] and set headers accordingly.
     * 
     * @throws InvalidConfigException if the formatter for the specified format is invalid or [[format]] is not supported
     */
    protected function ready()
    {
        if ($this->stream !== null) {
            return;
        }

        if (isset($this->formatters[$this->format])) {
            $formatter = $this->formatters[$this->format];
            if (!is_object($formatter)) {
                $this->formatters[$this->format] = $formatter = Kant::createObject($formatter);
            }

            if ($formatter instanceof ResponseFormatterInterface) {
                $formatter->format($this);
            } else {
                throw new InvalidConfigException("The '{$this->format}' response formatter is invalid. It must implement the ResponseFormatterInterface.");
            }
        } elseif ($this->format === self::FORMAT_RAW) {
            if ($this->data !== null) {
                $this->content = $this->data;
            }
        } else {
            throw new InvalidConfigException("Unsupported response format: {$this->format}");
        }

        if (is_array($this->content)) {
            throw new InvalidParamException('Response content must not be an array.');
        } else if ($this->content instanceof BaseResponse) {
            return $this->content->send();
        } elseif (is_object($this->content)) {
            if (method_exists($this->content, '__toString')) {
                $this->content = $this->content->__toString();
            } else {
                throw new InvalidParamException('Response content must be a string or an object implementing __toString().');
            }
        }
    }

    /**
     * Set the content on the response.
     *
     * @param mixed $content            
     * @return $this
     */
    public function setContent($content)
    {
        $this->data = $content;
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    public function sendContent()
    {
        if ($this->stream === null) {
            echo $this->content;
            return $this;
        }

        set_time_limit(0); // Reset time limit for big files
        $chunkSize = 8 * 1024 * 1024; // 8MB per chunk

        if (is_array($this->stream)) {
            list ($handle, $begin, $end) = $this->stream;
            fseek($handle, $begin);
            while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
                if ($pos + $chunkSize > $end) {
                    $chunkSize = $end - $pos + 1;
                }
                echo fread($handle, $chunkSize);
                flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
            }
            fclose($handle);
        } else {
            while (!feof($this->stream)) {
                echo fread($this->stream, $chunkSize);
                flush();
            }
            fclose($this->stream);
        }
        return $this;
    }

    /**
     * Add queued cookies to response
     */
    public function AddQueuedCookiesToResponse()
    {
        foreach (Kant::$app->cookie->getQueuedCookies() as $cookie) {
            $this->headers->setCookie($cookie);
        }
    }

    /**
     * Sets the response status code based on the exception.
     * @param \Exception|\Error $e the exception object.
     * @throws InvalidParamException if the status code is invalid.
     * @return $this the response object itself
     * @since 2.0.12
     */
    public function setStatusCodeByException($e)
    {
        if ($e instanceof HttpException) {
            $this->setStatusCode($e->statusCode);
        } else {
            $this->setStatusCode(500);
        }
        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return Response
     */
    public function send()
    {
        $this->ready();
        parent::send();
    }
}
