<?php

namespace CaptchaGenerator;
/**
 * Class BaseCaptchaGenerator
 *
 * @package CaptchaGenerator
 *
 * @property int    code_length
 * @property string color_background
 * @property string $color_text
 * @property string color_noise_lines
 * @property string color_noise_dots
 * @property string color_noise_circles
 * @property int    count_noise_lines
 * @property int    count_noise_dots
 * @property int    count_noise_circles
 * @property int    height
 * @property int    width
 * @property string color_background_secondary
 * @property string font
 * @property int    font_size
 * @property float  font_size_flex
 * @property int    text_angle
 */
abstract class BaseCaptchaGenerator
{
    use Calc;
    protected $im;
    protected $text;

    protected $code_length;
    protected $color_background;
    protected $color_text;
    protected $color_noise_lines;
    protected $color_noise_dots;
    protected $color_noise_circles;
    protected $count_noise_lines;
    protected $count_noise_dots;
    protected $count_noise_circles;
    protected $height;
    protected $width;
    protected $color_background_secondary;
    protected $font;
    protected $font_size;
    protected $font_size_flex;
    protected $text_angle;

    /**
     * CaptchaGenerator constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    final public function __construct(array $config)
    {
        foreach ($config as $attr => $value) {
            $this->$attr = $value;
        }

        $this->code_length                = isset($this->code_length                /**/) ? $this->code_length                /**/ : 6;
        $this->color_text                 = isset($this->color_text                 /**/) ? $this->color_text                 /**/ : static::calcRandomColor(0x00000000, 0x008f8f8f);
        $this->color_background           = isset($this->color_background           /**/) ? $this->color_background           /**/ : static::calcRandomColor(0x00909000, 0x00ffff00);
        $this->color_background_secondary = isset($this->color_background_secondary /**/) ? $this->color_background_secondary /**/ : static::calcColorShiftIn($this->color_background, $this->color_background, $this->color_text);
        $this->color_noise_lines          = isset($this->color_noise_lines          /**/) ? $this->color_noise_lines          /**/ : static::calcColorShiftIn($this->color_text, $this->color_background, $this->color_text);
        $this->color_noise_dots           = isset($this->color_noise_dots           /**/) ? $this->color_noise_dots           /**/ : static::calcColorShiftIn($this->color_text, $this->color_background, $this->color_text);
        $this->color_noise_circles        = isset($this->color_noise_circles        /**/) ? $this->color_noise_circles        /**/ : static::calcColorShiftIn($this->color_text, $this->color_background, $this->color_text);
        $this->count_noise_lines          = isset($this->count_noise_lines          /**/) ? $this->count_noise_lines          /**/ : mt_rand($this->code_length * 3, $this->code_length * 5);
        $this->count_noise_dots           = isset($this->count_noise_dots           /**/) ? $this->count_noise_dots           /**/ : mt_rand($this->code_length * 3, $this->code_length * 5);
        $this->count_noise_circles        = isset($this->count_noise_circles        /**/) ? $this->count_noise_circles        /**/ : mt_rand($this->code_length * 3, $this->code_length * 5);
        $this->height                     = isset($this->height                     /**/) ? $this->height                     /**/ : mt_rand(90, 90);
        $this->width                      = isset($this->width                      /**/) ? $this->width                      /**/ : ceil($this->height * (9 / 16) * $this->code_length);
        $this->font                       = isset($this->font                       /**/) ? $this->font                       /**/ : realpath(__DIR__ . '/./font/ljk_WC Mano Negra Bta.otf');
        $this->font_size                  = isset($this->font_size                  /**/) ? $this->font_size                  /**/ : floor($this->height * 0.6);
        $this->font_size_flex             = isset($this->font_size_flex             /**/) ? $this->font_size_flex             /**/ : 0.4;
        $this->text_angle                 = isset($this->text_angle                 /**/) ? $this->text_angle                 /**/ : mt_rand(35, 85);

        $im   = imagecreatetruecolor($this->width, $this->height);
        $text = static::calcRandomCode($this->code_length);

        $this->text = $text;
        $this->im   = $im;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param $hex_color
     */
    final public function gdFillColor($hex_color)
    {
        $im = $this->im;
        imagefill($im, 0, 0, self::calcColorHexdec2int($hex_color));
    }

    /**
     * @param $count
     * @param $hex_color
     */
    final public function gdDrawLines($count, $hex_color)
    {
        $im     = $this->im;
        $width  = imagesx($im);
        $height = imagesy($im);
        $count  = abs($count);
        for ($i = 0; $i < $count; $i++) {
            imageline($im,
                mt_rand(0, $width),
                mt_rand(0, $height),
                mt_rand(0, $width),
                mt_rand(0, $height),
                self::calcColorHexdec2int($hex_color)
            );
        }
    }

    /**
     * @param $count
     * @param $hex_color
     */
    final public function gdDrawDots($count, $hex_color)
    {
        $im     = $this->im;
        $width  = imagesx($im);
        $height = imagesy($im);
        $count  = abs($count);
        for ($i = 0; $i < $count; $i++) {
            imagefilledellipse($im,
                mt_rand(0, $width),
                mt_rand(0, $height),
                mt_rand(2, 3),
                mt_rand(2, 3),
                self::calcColorHexdec2int($hex_color)
            );
        }
    }

    /**
     * @param $count
     * @param $hex_color
     */
    final public function gdDrawCircles($count, $hex_color)
    {
        $im     = $this->im;
        $width  = imagesx($im);
        $height = imagesy($im);
        $count  = abs($count);
        for ($i = 0; $i < $count; ++$i) {
            $segments = static::calcWobbleCirclesSegments($width, $height, $count, $i);
            foreach ($segments as list($x1, $y1, $x2, $y2)) {
                imageline($im, $x1, $y1, $x2, $y2, self::calcColorHexdec2int($hex_color));
            }
        }
    }

    /**
     * @param $text
     * @param $hex_color
     */
    final public function gdDrawTextStraight($text, $hex_color)
    {
        $im = $this->im;
        list ($x, $y, $font_size, $angle) = self::calcTextPosition(
            imagesx($im),
            imagesy($im),
            $text,
            $this->font,
            0.8 * $this->font_size,
            1.0 * $this->font_size,
            $this->text_angle
        );
        imagettftext($im,
            $font_size,
            $angle,
            $x,
            $y,
            self::calcColorHexdec2int($hex_color),
            $this->font,
            $text
        );
    }

    /**
     * @param $text
     * @param $hex_color
     */
    final public function gdDrawTextDistorted($text, $hex_color)
    {
        $im     = $this->im;
        $width  = imagesx($im);
        $height = imagesy($im);
        $items  = static::calcTextDistorted(
            $width, $height, $text,
            $this->font,
            $this->font_size,
            $this->font_size_flex,
            $this->text_angle
        );

        foreach ($items as list(
                 $x, $y,
                 $letter, $font_size,
                 $angle
        )) {
            imagettftext($im, $font_size, $angle,
                $x, $y,
                self::calcColorHexdec2int($hex_color),
                $this->font, $letter
            );
        }
    }
}
