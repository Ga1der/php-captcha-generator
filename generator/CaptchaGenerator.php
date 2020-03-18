<?php

namespace CaptchaGenerator;

/**
 * Class CaptchaGenerator
 *
 * @package CaptchaGenerator
 */
final class CaptchaGenerator extends BaseCaptchaGenerator
{
    /**
     * @return $this
     * @throws \Exception
     */
    public function lazy()
    {
        $text = $this->text;
        $text = mb_strtoupper($text);

        $this->gdFillColor($this->color_background_secondary);
        $this->gdDrawLines(ceil($this->count_noise_lines / 2), $this->color_noise_lines);
        $this->gdFillColor($this->color_background);
        $this->gdDrawCircles(floor($this->count_noise_circles / 2), $this->color_noise_circles);
//        $this->gdDrawTextStraight($text, $this->color_text);
        $this->gdDrawTextDistorted($text, $this->color_text);
        $this->gdDrawLines(floor($this->count_noise_lines / 2), $this->color_noise_lines);
        $this->gdDrawCircles(ceil($this->count_noise_circles / 2), $this->color_noise_circles);
        $this->gdDrawDots(ceil($this->count_noise_dots / 1), $this->color_noise_dots);

        return $this;
    }

    /**
     * @param int $quality
     *
     * @return array
     */
    public function imageJpeg($quality = 100)
    {
        $im   = $this->im;
        $text = $this->text;

        ob_start();
        imagejpeg($im, NULL, $quality);
        $image_data        = ob_get_contents();
        $image_data_base64 = base64_encode($image_data);
        imagedestroy($im);
        ob_end_clean();

        return [$text, $image_data_base64];
    }
}
