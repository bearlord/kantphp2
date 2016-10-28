<?php

namespace Kant\Behavior;
use Kant\Registry\KantRegistry;

class TokenBuildBehavior {

    /**
     * Run
     * 
     * @param string $content
     */
    public function run(&$content) {
        $tokenSwitch = KantRegistry::get('config')->get('token.switch');
        if ($tokenSwitch) {
            list($tokenName, $tokenKey, $tokenValue) = $this->getToken();
            $input_token = '<input type="hidden" name="' . $tokenName . '" value="' . $tokenKey . '_' . $tokenValue . '" />';
            $meta_token = '<meta name="' . $tokenName . '" content="' . $tokenKey . '_' . $tokenValue . '" />';
            if (strpos($content, '{__TOKEN__}')) {
                $content = str_replace('{__TOKEN__}', $input_token, $content);
            } elseif (preg_match('/<\/form(\s*)>/is', $content, $match)) {
                $content = str_replace($match[0], $input_token . $match[0], $content);
            }
            $content = str_ireplace('</head>', $meta_token . '</head>', $content);
        } else {
            $content = str_replace('{__TOKEN__}', '', $content);
        }
    }

    /**
     * Get Token
     * @return type
     */
    private function getToken() {
        $tokenConfig = KantRegistry::get('config')->get("token");
        $tokenName = !empty($tokenConfig['name']) ? $tokenConfig['name'] : "__hash__";
        $tokenType = !empty($tokenConfig['type']) ? $tokenConfig['type'] : "md5";
        $tokenReset = !empty($tokenConfig['reset']) ? $tokenConfig['reset'] : false;
        if (!isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = array();
        }
        $tokenKey = md5($_SERVER['REQUEST_URI']);
        if (isset($_SESSION[$tokenName][$tokenKey])) {
            $tokenValue = $_SESSION[$tokenName][$tokenKey];
        } else {
            $tokenValue = is_callable($tokenType) ? $tokenType(microtime(true)) : md5(microtime(true));
            $_SESSION[$tokenName][$tokenKey] = $tokenValue;
            if (IS_AJAX && $tokenReset)
                header($tokenName . ': ' . $tokenKey . '_' . $tokenValue); 
        }
        return array($tokenName, $tokenKey, $tokenValue);
    }

}
