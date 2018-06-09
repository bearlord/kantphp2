<?php
/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Filemanager;


use Kant\Foundation\Component;
use Kant\Kant;

class I18n extends Component
{
    /**
     * @var Message source
     */
    public static $messageSource;


    public function init()
    {
        parent::init();
        self::setMessageSource();
    }

    /**
     * Get message source
     *
     * @return Message
     */
    public static function setMessageSource()
    {
        $sourceFile = KANT_PATH . '/Filemanager/I18n/' . Kant::$app->language . "/message.php";
        if (file_exists($sourceFile)) {
            self::$messageSource = include KANT_PATH . '/Filemanager/I18n/' . Kant::$app->language . "/message.php";
        }
    }

    /**
     * Translate message
     *
     * @param $message
     * @return mixed
     */
    public static function t($message, $params = [])
    {
        if (empty(self::$messageSource) || empty(self::$messageSource[$message])) {
            return $message;
        }
        $p = [];
        if (!empty($params)) {
            foreach ((array) $params as $name => $value) {
                $p['{' . $name . '}'] = $value;
            }
        }

        return ($p === []) ? self::$messageSource[$message] : strtr(self::$messageSource[$message], $p);
    }
}