<?php
namespace Kant\Pathinfo;

class Pathinfo
{

    private static $_instance;

    /**
     * Get Pathinfo object
     *
     * @return type
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Parse Pathinfo
     */
    public function parsePathinfo()
    {
        $pathinfo = "";
        if (PHP_SAPI == 'cli') {
            $pathinfo = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            return $pathinfo;
        }
        // if PATH_INFO_REPAIR is false
        if (PATH_INFO_REPAIR == false) {
            if (isset($_SERVER['PATH_INFO'])) {
                $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
            } else {
                $pathinfoFetch = [
                    'ORIG_PATH_INFO',
                    'REDIRECT_PATH_INFO',
                    'REDIRECT_URL'
                ];
                foreach ($pathinfoFetch as $type) {
                    if (!empty($_SERVER[$type])) {
                        $pathinfo = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                        break;
                    }
                }
            }
            return $pathinfo;
        } else {
            foreach (array(
                'REQUEST_URI',
                'HTTP_X_REWRITE_URL',
                'argv'
            ) as $var) {
                if (!empty($_SERVER[$var])) {
                    $requestUri = $_SERVER[$var];
                    if ($var == 'argv') {
                        $requestUri = @strtolower($requestUri[1]);
                    }
                    break;
                }
            }
            $scriptName = strtolower(ltrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
            // url as [/index.php?module=demo&ctrl=index&act=index] or [/index.php/demo/index/index]
            if (strpos($requestUri, "index.php") !== false) {
                $parse = parse_url($requestUri);
                // url as [/index.php?module=demo&ctrl=index&act=index]
                if (!empty($parse['query']) && strpos($parse['query'], 'module') !== false) {
                    $pathinfo = "";
                } else {
                    // url as [/index.php/demo/index/index]
                    $pathinfo = ltrim(str_replace($scriptName, '', $requestUri));
                    if (strpos($pathinfo, "index.php/") !== false) {
                        $pathinfo = str_replace("index.php/", "", $pathinfo);
                    }
                }
            } else {
                // url as [/demo/index/index]
                $pathinfo = ltrim(str_replace($scriptName, '', $requestUri));
            }
        }
        
        return $pathinfo;
    }
}
