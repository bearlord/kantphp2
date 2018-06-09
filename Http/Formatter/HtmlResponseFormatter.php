<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Http\Formatter;

use Kant\Foundation\Component;

/**
 * HtmlResponseFormatter formats the given data into an HTML response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HtmlResponseFormatter extends Component implements ResponseFormatterInterface
{

    /**
     *
     * @var string the Content-Type header for the response
     */
    public $contentType = 'text/html';

    /**
     * Formats the specified response.
     * 
     * @param Response $response
     *            the response to be formatted.
     */
    public function format($response)
    {
        if (stripos($this->contentType, 'charset') === false) {
            $response->headers->set('charset', $response->charset);
        }
        $response->headers->set('Content-Type', $this->contentType);
        if ($response->data !== null) {
            $response->content = $response->data;
        }
    }
}
