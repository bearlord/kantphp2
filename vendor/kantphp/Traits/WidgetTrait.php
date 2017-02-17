<?php

namespace Kant\Traits;

use Kant\KantFactory;

/**
 * Description of Widget
 *
 * @author Administrator
 */
trait WidgetTrait {

    /**
     * Widget
     * 
     * @param string $widgetname
     * @param string $method
     * @param array $data
     * @param boolean $return
     * @return boolean
     * @throws KantException
     */
    public function widget($widgetname, $method, $data = array(), $return = false) {
        $dispatcher = KantFactory::getConfig()->get('dispatcher');
        $module = isset($dispatcher['module']) ? ucfirst($dispatcher['module']) : '';
        $classname = ucfirst($widgetname) . 'Widget';
        if ($module) {
            $filepath = APP_PATH . 'Module' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Widget' . DIRECTORY_SEPARATOR . $classname . '.php';
        } else {
            $filepath = APP_PATH . 'Widget' . DIRECTORY_SEPARATOR . $classname . '.php';
        }
        if (file_exists($filepath)) {
            include_once $filepath;
            if (!class_exists($classname)) {
                throw new KantException("Class $classname does not exists");
            }
            if (!method_exists($classname, $method)) {
                throw new KantException("Method $method does not exists");
            }
            $widget = new $classname;
            $content = call_user_func_array(array($widget, $method), $data);
            if ($return) {
                return $content;
            } else {
                echo $content;
            }
        }
    }

}
