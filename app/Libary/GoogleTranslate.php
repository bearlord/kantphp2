<?php

class GoogleTranslate {

    private static $_instance = null;
    protected $translateApiUrl = "http://translate.google.cn/translate_a/single?client=t&sl=%s&tl=%s&hl=%s&dt=bd&dt=ex&dt=ld&dt=md&dt=qc&dt=rw&dt=rm&dt=ss&dt=t&dt=at&dt=sw&ie=UTF-8&oe=UTF-8&prev=btn&srcrom=1&ssel=3&tsel=3&q=%s";

    /**
     * Singleton instance
     * 
     * @return array
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 
     * @param string $text
     * @param string $sl source language
     * @param string $tl source language
     */
    public function translate($text, $sl = 'zh-cn', $tl = 'en') {
        $string = '';
        if (strlen($text) < 1) {
            return;
        }
        $matchCount = 1;
        if (preg_match_all('|。|i', $text, $match)) {
            $matchCount = count($match[0]);
        } else if (preg_match_all('/(\.\s)*(\r\n)+/i', $text, $match)) {
            //|\.\s\\r\\n|i
            $matchCount = count($match[0]);
        }
        $url = sprintf($this->translateApiUrl, $sl, $tl, $sl, urlencode($text));
        $content = file_get_contents($url);
        if (empty($content)) {
            return;
        }
        if (preg_match_all('|\[([^\[\]]*)\]|i', $content, $match)) {
            $match = $match[0];
            for ($i = 0; $i <= $matchCount; $i++) {
                $val = json_decode($match[$i]);
                $string .= $this->formatHtml($val[0]);
            }
            return nl2br($string);
        }
    }
    
    protected function formatHtml($str) {
        $str = str_replace('&nbsp;', " ", $str);
        $str = str_replace('&lt;', '<', $str);
        $str = str_replace('&&gt;', '>', $str);
        $str = str_replace("&amp;", '&', $str);
        $str = str_replace(array('& ldquo; ', '& ldquo;', '&ldquo;'), array('“', '“', '“'), $str);
        $str = str_replace(array(' & rdquo ;', '& rdquo;', '&rdquo;'), array('”', '”', '”'), $str);
        $str = str_replace('&middot;', '·', $str);
        $str = str_replace('&lsquo;', '‘', $str);
        $str = str_replace('&rsquo;', '’', $str);
        $str = str_replace('&hellip;', '…', $str);
        $str = str_replace('&mdash;', '—', $str);
        return $str;
    }

}
