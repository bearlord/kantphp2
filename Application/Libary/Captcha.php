<?php

/**
 * 生成验证码
 * @author chenzhouyu
 * 类用法
 * $checkcode = new Captcha();
 * $checkcode->doimage();
 * //取得验证
 * $_SESSION['code']=$checkcode->getCode();
 */
class Captcha {

    //验证码的宽度
    public $width = 130;
    //验证码的高
    public $height = 50;
    //设置字体的地址
    private $font;
    //设置字体色
    public $fontColor;
    //设置随机生成因子
    public $charset = 'abcdefghkmnprstuvwyzABCDEFGHKLMNPRSTUVWYZ23456789';
    //设置背景色
    public $background = '#FFFFFF';
    //生成验证码字符数
    public $codeLength = 4;
    //字体大小
    public $fontSize = 20;
    //是否画干扰线
    public $line = false;
    //验证码
    private $code;
    //图片内存
    private $img;
    //文字X轴开始的地方
    private $xstart;

    function __construct() {
        $this->font = PUBLIC_PATH . 'fonts' . DIRECTORY_SEPARATOR . 'FreeSans.ttf';
    }

    /**
     * 生成随机验证码。
     */
    protected function createCode() {
        $code = '';
        $charset_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codeLength; $i++) {
            $code .= $this->charset[rand(1, $charset_len)];
        }
        $this->code = $code;
    }

    /**
     * 获取验证码
     */
    public function getCode() {
        return strtolower($this->code);
    }

    /**
     * 生成图片
     */
    public function doImage() {
        $code = $this->createCode();
        $this->img = imagecreatetruecolor($this->width, $this->height);
        if (!$this->fontColor) {
            $this->fontColor = imagecolorallocate($this->img, rand(0, 156), rand(0, 156), rand(0, 156));
        } else {
            $this->fontColor = imagecolorallocate($this->img, hexdec(substr($this->fontColor, 1, 2)), hexdec(substr($this->fontColor, 3, 2)), hexdec(substr($this->fontColor, 5, 2)));
        }
        //设置背景色
        $background = imagecolorallocate($this->img, hexdec(substr($this->background, 1, 2)), hexdec(substr($this->background, 3, 2)), hexdec(substr($this->background, 5, 2)));
        //画一个柜形，设置背景颜色。
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $background);
        $this->createFont();
        if ($this->line) {
            $this->createLine();
        }
        $this->output();
    }

    /**
     * 生成文字
     */
    private function createFont() {
        $x = $this->width / $this->codeLength;
        for ($i = 0; $i < $this->codeLength; $i++) {
            imagettftext($this->img, $this->fontSize, rand(-15, 15), $x * $i + rand(5, 10), $this->height / 1.4, $this->fontColor, $this->font, $this->code[$i]);
            if ($i == 0)
                $this->xstart = $x * $i + 5;
        }
    }

    /**
     * 画线
     */
    private function createLine() {
        imagesetthickness($this->img, 1);
        $xpos = ($this->fontSize * 2) + rand(-5, 5);
        $width = $this->width / 2.66 + rand(3, 10);
        $height = $this->fontSize * 2.14;

        if (rand(0, 100) % 2 == 0) {
            $start = rand(0, 66);
            $ypos = $this->height / 2 - rand(10, 30);
            $xpos += rand(5, 15);
        } else {
            $start = rand(180, 246);
            $ypos = $this->height / 2 + rand(10, 30);
        }

        $end = $start + rand(75, 110);

        imagearc($this->img, $xpos, $ypos, $width, $height, $start, $end, $this->fontColor);

        if (rand(1, 75) % 2 == 0) {
            $start = rand(45, 111);
            $ypos = $this->height / 2 - rand(10, 30);
            $xpos += rand(5, 15);
        } else {
            $start = rand(200, 250);
            $ypos = $this->height / 2 + rand(10, 30);
        }

        $end = $start + rand(75, 100);

        imagearc($this->img, $this->width * .75, $ypos, $width, $height, $start, $end, $this->fontColor);
    }

    /**
     * 输出图片
     */
    private function output() {
        header("content-type:image/png\r\n");
        imagepng($this->img);
        imagedestroy($this->img);
    }

}
