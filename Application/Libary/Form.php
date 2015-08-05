<?php

class Form {

    /**
     * selection box
     */
    public static function select($array = array(), $id = 0, $str = '', $default_option = '') {
        $string = '<select ' . $str . '>';
        $default_selected = (empty($id) && $default_option) ? 'selected' : '';
        if ($default_option)
            $string .= "<option value='' $default_selected>$default_option</option>";
        if (!is_array($array) || count($array) == 0)
            return false;
        $ids = array();
        if (isset($id))
            $ids = explode(',', $id);
        foreach ($array as $key => $value) {
            $selected = in_array($key, $ids) ? ' selected' : '';
            $string .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $string .= '</select>';
        return $string;
    }

    /**
     * 复选框
     * 
     * @param $array 选项 二维数组
     * @param $id 默认选中值，多个用 '逗号'分割
     * @param $str 属性
     * @param $defaultvalue 是否增加默认值 默认值为 -99
     * @param $width 宽度
     */
    public static function checkbox($array = array(), $id = '', $str = '', $defaultvalue = '', $width = 0, $field = '') {
        $string = '';
        $id = trim($id);
        if ($id != '')
            $id = strpos($id, ',') ? explode(',', $id) : array($id);
        if ($defaultvalue)
            $string .= '<input type="hidden" ' . $str . ' value="-99">';
        $i = 1;
        foreach ($array as $key => $value) {
            $key = trim($key);
            $checked = ($id && in_array($key, $id)) ? 'checked' : '';
            if ($width)
                $string .= '<label class="ib" style="width:' . $width . 'px">';
            $string .= '<input type="checkbox" ' . $str . ' id="' . $field . '_' . $i . '" ' . $checked . ' value="' . htmlspecialchars($key) . '"> ' . htmlspecialchars($value);
            if ($width)
                $string .= '</label>';
            $i++;
        }
        return $string;
    }

    /**
     * 单选框
     * 
     * @param $array 选项 二维数组
     * @param $id 默认选中值
     * @param $str 属性
     */
    public static function radio($array = array(), $id = 0, $str = '', $width = 0, $field = '') {
        $string = '';
        foreach ($array as $key => $value) {
            $checked = trim($id) == trim($key) ? 'checked' : '';
            if ($width)
                $string .= '<label class="ib" style="width:' . $width . 'px">';
            $string .= '<input type="radio" ' . $str . ' id="' . $field . '_' . htmlspecialchars($key) . '" ' . $checked . ' value="' . $key . '"> ' . $value;
            if ($width)
                $string .= '</label>';
        }
        return $string;
    }

}

?>
