<?php

namespace Kant\Traits;
use Kant\KantFactory;

class LangTrait {

    /**
     * Get current user defined language
     * 
     * @return
     */
    public function getLang() {
        static $lang = null;
        if (empty($lang)) {
            $lang = !empty($_COOKIE['lang']) ? $_COOKIE['lang'] : KantFactory::getConfig()->get('lang');
            if (empty($lang)) {
                $lang = 'en_US';
            }
        }
        return $lang;
    }

    /**
     * Language localization
     * 
     * @staticvar array $LANG
     * @param string $language
     * @return array
     */
    public function lang($language = 'no_language') {
        static $LANG = array();
        if (!$LANG) {
            $lang = $this->getLang();
            require KANT_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'System.php';
            if (file_exists(APP_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'App.php')) {
                require APP_PATH . 'Locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'App.php';
            }
        }
        if (!array_key_exists($language, $LANG)) {
            return $language;
        } else {
            $language = $LANG[$language];
            return $language;
        }
    }

}
