<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\I18n;

use Kant\Kant;
use Kant\Foundation\Component;
use Kant\Exception\InvalidConfigException;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 *
 * I18N is configured as an application component in [[\Kant\Application]] by default.
 * You can access that instance via `Kant::$app->i18n`.
 *
 * @property MessageFormatter $messageFormatter The message formatter to be used to format message via ICU
 *           message format. Note that the type of this property differs in getter and setter. See
 *           [[getMessageFormatter()]] and [[setMessageFormatter()]] for details.
 *          
 */
class I18N extends Component
{

    /**
     *
     * @var array list of [[MessageSource]] configurations or objects. The array keys are message
     *      category patterns, and the array values are the corresponding [[MessageSource]] objects or the configurations
     *      for creating the [[MessageSource]] objects.
     *     
     *      The message category patterns can contain the wildcard '*' at the end to match multiple categories with the same prefix.
     *      For example, 'app/*' matches both 'app/cat1' and 'app/cat2'.
     *     
     *      The '*' category pattern will match all categories that do not match any other category patterns.
     *     
     *      This property may be modified on the fly by extensions who want to have their own message sources
     *      registered under their own namespaces.
     *     
     *      The category "kant" and "app" are always defined. The former refers to the messages used in the Kant core
     *      framework code, while the latter refers to the default message category for custom application code.
     *      By default, both of these categories use [[PhpMessageSource]] and the corresponding message files are
     *      stored under "@kant/messages" and "@app/messages", respectively.
     *     
     *      You may override the configuration of both categories.
     */
    public $translations;

    /**
     * Initializes the component by configuring the default message categories.
     */
    public function init()
    {
        parent::init();
        if (!isset($this->translations['kant']) && ! isset($this->translations['kant*'])) {
            $this->translations['kant'] = [
                'class' => 'Kant\I18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => KANT_PATH . '/Messages/'
            ]
            // 'basePath' => '@kant/messages',
            ;
        }
        if (!isset($this->translations['app']) && ! isset($this->translations['app*'])) {
            $this->translations['app'] = [
                'class' => 'Kant\I18n\PhpMessageSource',
                'sourceLanguage' => Kant::$app->sourceLanguage,
                'basePath' => APP_PATH . '/messages/'
            ]
            // 'basePath' => '@app/messages',
            ;
        }
    }

    /**
     * Translates a message to the specified language.
     *
     * After translation the message will be formatted using [[MessageFormatter]] if it contains
     * ICU message format and `$params` are not empty.
     *
     * @param string $category
     *            the message category.
     * @param string $message
     *            the message to be translated.
     * @param array $params
     *            the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language
     *            the language code (e.g. `en-US`, `en`).
     * @return string the translated and formatted message.
     */
    public function translate($category, $message, $params, $language)
    {
        $messageSource = $this->getMessageSource($category);
        $translation = $messageSource->translate($category, $message, $language);
        if ($translation === false) {
            return $this->format($message, $params, $messageSource->sourceLanguage);
        } else {
            return $this->format($translation, $params, $language);
        }
    }

    /**
     * Formats a message using [[MessageFormatter]].
     *
     * @param string $message
     *            the message to be formatted.
     * @param array $params
     *            the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language
     *            the language code (e.g. `en-US`, `en`).
     * @return string the formatted message.
     */
    public function format($message, $params, $language)
    {
        $params = (array) $params;
        if ($params === []) {
            return $message;
        }
        if (preg_match('~{\s*[\d\w]+\s*,~u', $message)) {
            $formatter = $this->getMessageFormatter();
            $result = $formatter->format($message, $params, $language);
            if ($result === false) {
                $errorMessage = $formatter->getErrorMessage();
                Kant::warning("Formatting message for language '$language' failed with error: $errorMessage. The message being formatted was: $message.", __METHOD__);
                
                return $message;
            } else {
                return $result;
            }
        }
        
        $p = [];
        foreach ($params as $name => $value) {
            $p['{' . $name . '}'] = $value;
        }
        
        return strtr($message, $p);
    }

    /**
     *
     * @var string|array|MessageFormatter
     */
    private $_messageFormatter;

    /**
     * Returns the message formatter instance.
     * 
     * @return MessageFormatter the message formatter to be used to format message via ICU message format.
     */
    public function getMessageFormatter()
    {
        if ($this->_messageFormatter === null) {
            $this->_messageFormatter = new MessageFormatter();
        } elseif (is_array($this->_messageFormatter) || is_string($this->_messageFormatter)) {
            $this->_messageFormatter = Kant::createObject($this->_messageFormatter);
        }
        
        return $this->_messageFormatter;
    }

    /**
     *
     * @param string|array|MessageFormatter $value
     *            the message formatter to be used to format message via ICU message format.
     *            Can be given as array or string configuration that will be given to [[Kant::createObject]] to create an instance
     *            or a [[MessageFormatter]] instance.
     */
    public function setMessageFormatter($value)
    {
        $this->_messageFormatter = $value;
    }

    /**
     * Returns the message source for the given category.
     * 
     * @param string $category
     *            the category name.
     * @return MessageSource the message source for the given category.
     * @throws InvalidConfigException if there is no message source available for the specified category.
     */
    public function getMessageSource($category)
    {
        if (isset($this->translations[$category])) {
            $source = $this->translations[$category];
            if ($source instanceof MessageSource) {
                return $source;
            } else {
                return $this->translations[$category] = Kant::createObject($source);
            }
        } else {
            // try wildcard matching
            foreach ($this->translations as $pattern => $source) {
                if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                    if ($source instanceof MessageSource) {
                        return $source;
                    } else {
                        return $this->translations[$category] = $this->translations[$pattern] = Kant::createObject($source);
                    }
                }
            }
            // match '*' in the last
            if (isset($this->translations['*'])) {
                $source = $this->translations['*'];
                if ($source instanceof MessageSource) {
                    return $source;
                } else {
                    return $this->translations[$category] = $this->translations['*'] = Kant::createObject($source);
                }
            }
        }
        
        throw new InvalidConfigException("Unable to locate message source for category '$category'.");
    }
}
