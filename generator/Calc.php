<?php
namespace CaptchaGenerator;

/**
 * Trait Calc
 *
 * @package CaptchaGenerator
 */
trait Calc
{
    /**
     * @param $min
     * @param $max
     *
     * @return int
     */
    private static function swapRand($min, $max)
    {
        if ($min > $max) list($min, $max) = [$max, $min];

        return mt_rand($min, $max);
    }

    /**
     * @param int $from
     * @param int $to
     *
     * @return string
     * @throws \Exception
     */
    public static function calcRandomColor($from = 0x00000000, $to = 0x7FFFFFFF)
    {
        list ($fr, $fg, $fb, $fa) = static::calcColorHexdec2rgba($from);
        list ($tr, $tg, $tb, $ta) = static::calcColorHexdec2rgba($to);

        return static::calcColorRgba2hexdec(
            (static::swapRand($fr, $tr) & 0xFF) >> 0,
            (static::swapRand($fg, $tg) & 0xFF) >> 0,
            (static::swapRand($fb, $tb) & 0xFF) >> 0,
            (static::swapRand($fa, $ta) & 0x7F) >> 0
        );
    }

    /**
     * @param $hex_dec
     *
     * @return array
     * @throws \Exception
     */
    private static function calcColorHexdec2rgba($hex_dec)
    {
        $int = static::calcColorHexdec2int($hex_dec);

        return self::calcColorInt2rgba($int);
    }

    /**
     * @param $r
     * @param $g
     * @param $b
     * @param $a
     *
     * @return string
     */
    private static function calcColorRgba2hexdec($r, $g, $b, $a = 0x00)
    {
        $result = 0x00000000;
        $result += ($a & 0x7F) * 0x01000000;
        $result += ($r & 0xFF) * 0x00010000;
        $result += ($g & 0xFF) * 0x00000100;
        $result += ($b & 0xFF) * 0x00000001;

        return sprintf('0x%08s', dechex($result));
    }

    /**
     * @param int $hex_color
     * @param int $min
     * @param int $max
     *
     * @return string
     * @throws \Exception
     */
    private static function calcColorShiftIn($hex_color = 0x00000000, $min = 0x00000000, $max = 0x7FFFFFFF)
    {
        list ($r, $g, $b, $a) = static::calcColorHexdec2rgba($hex_color);
        list ($min_r, $min_g, $min_b, $min_a) = static::calcColorHexdec2rgba($min);
        list ($max_r, $max_g, $max_b, $max_a) = static::calcColorHexdec2rgba($max);

        if ($min_r < $r) $min_r = $r;
        if ($min_g < $g) $min_g = $g;
        if ($min_b < $b) $min_b = $b;
        if ($min_a < $a) $min_a = $a;

        if ($max_r > $r) $max_r = $r;
        if ($max_g > $g) $max_g = $g;
        if ($max_b > $b) $max_b = $b;
        if ($max_a > $a) $max_a = $a;

        return static::calcColorRgba2hexdec(
            (static::swapRand($min_r, $max_r) & 0xFF) >> 0,
            (static::swapRand($min_g, $max_g) & 0xFF) >> 0,
            (static::swapRand($min_b, $max_b) & 0xFF) >> 0,
            (static::swapRand($min_a, $max_a) & 0x7F) >> 0
        );
    }

    /**
     * for random string
     *
     * @param int    $characters
     * @param string $letters
     *
     * @return string
     */
    private static function calcRandomCode($characters = 6, $letters = '23456789bcdfghjkmnpqrstvwxyz')
    {
        $code           = '';
        $characters     = abs($characters ?: 6);
        $letter_options = strlen($letters);
        for ($i = 0; $i < $characters; $i++) {
            $code .= mt_rand(0, 1)
                ? mb_strtolower($letters[mt_rand(0, $letter_options - 1)])
                : mb_strtoupper($letters[mt_rand(0, $letter_options - 1)]);
        }

        return $code;
    }

    /**
     * @param $hex_dec
     *
     * @return int
     */
    private static function calcColorHexdec2int($hex_dec)
    {
        return is_string($hex_dec)
            ? hexdec(strval($hex_dec)) & 0x7FFFFFFF
            : intval($hex_dec) & 0x7FFFFFFF;
    }

    /**
     * @param $int
     *
     * @return array
     * @throws \Exception
     */
    private static function calcColorInt2rgba($int)
    {
        $red   = ($int & 0x7FFFFFFF) >> 16;
        $green = ($int & 0x7FFFFFFF) >> 8;
        $blue  = ($int & 0x7FFFFFFF) >> 0;
        $alpha = ($int & 0x7FFFFFFF) >> 24;

        return [$red % 256, $green % 256, $blue % 256, $alpha % 256];
    }

    /**
     * @param     $wp_width
     * @param     $wp_height
     * @param     $text
     * @param     $font
     * @param     $size
     * @param int $angle
     *
     * @return array
     */
    private static function calcTextRandomCenter($wp_width, $wp_height, $text, $font, $size, $angle = 8)
    {
        $size = intval($size);
        list (
            $lower_left_x,
            $lower_left_y,
            $lower_right_x,
            $lower_right_y,
            $upper_right_x,
            $upper_right_y,
            $upper_left_x,
            $upper_left_y,
            ) = imagettfbbox($size, $angle, $font, $text);
        $xr = abs(max($lower_right_x, $upper_right_x));
        $yr = abs(max($upper_right_y, $upper_left_y));
        $x  = intval(($wp_width - $xr) / 2);
        $y  = intval(($wp_height + $yr) / 2);

        return array($x, $y);
    }

    /**
     * @param $x
     * @param $y
     * @param $radius
     * @param $line_width
     * @param $wobble_edges
     * @param $wobble_ratio
     *
     * @return array
     */
    private static function calcCircleWobbleSegments($x, $y, $radius, $line_width, $wobble_edges, $wobble_ratio)
    {
        static $radians = 360 / 180 * M_PI;
        $radius                 = abs($radius ?: 1);
        $radian_fragment_length = (1 / ($radians * $radius));

        /** @var array $segments */
        $segments = [];
        for ($section_radian = 0; $section_radian < $radians; $section_radian += $radian_fragment_length) {
            $_a                 = 0.5 + (0.5 * sin($section_radian * $wobble_edges));
            $radius_coefficient = (1 - $wobble_ratio * $_a);
            $segment_radius     = ($radius * $radius_coefficient);
            $segment_top_left_x = ($x + cos($section_radian) * $segment_radius);
            $segment_top_left_y = ($y + sin($section_radian) * $segment_radius);

            $segments[] = [
                $segment_top_left_x + 0,
                $segment_top_left_y + 0,
                $segment_top_left_x + $line_width,
                $segment_top_left_y + $line_width,
            ];
        }

        return $segments;
    }

    /**
     * @param $image_height
     * @param $image_width
     * @param $count
     * @param $i
     * @param $size_coefficient
     *
     * @return array
     */
    private static function calcSegmentPosition($image_height, $image_width, $count, $i, $size_coefficient)
    {
        $port_size = $image_height * 0.2 * $size_coefficient;
        $x_min     = $image_width / (1 + $count) * (1 + $i);
        $x_shift   = $image_width / (1 + $count) + (0.5 * $port_size);
        $x         = $x_min + $x_shift;
        $y         = mt_rand($image_height * 0.1, $image_height * 0.9);

        return [$x, $y, $port_size];
    }

    /**
     * @param $image_width
     * @param $image_height
     * @param $count
     * @param $i
     *
     * @return array|array[]
     */
    private static function calcWobbleCirclesSegments($image_width, $image_height, $count, $i)
    {
        list ($x, $y, $radius) = static::calcSegmentPosition(
            $image_height, $image_width, $count, $i,
            self::calcRandomFloat(0, 1, 4)
        );

        return static::calcCircleWobbleSegments($x, $y, $radius,
            1 * mt_rand(0, 1),
            1 * mt_rand(1, 4),
            1 * self::calcRandomFloat(0.0, 0.3, 1)
        );
    }

    /**
     * @param int $min
     * @param int $max
     * @param int $precision
     *
     * @return float|int
     */
    private static function calcRandomFloat($min = 0, $max = 1, $precision = 0)
    {
        $precision = intval(abs($precision));
        $c         = doubleval("1e{$precision}");
        $min       = floor($min * $c);
        $max       = ceil($max * $c);

        return mt_rand($min, $max) / $c;
    }

    /**
     * @param $wp_width
     * @param $wp_height
     * @param $text
     * @param $font
     * @param $font_size_min
     * @param $font_size_max
     * @param $max_angle
     *
     * @return array
     */
    private static function calcTextPosition($wp_width, $wp_height, $text, $font, $font_size_min, $font_size_max, $max_angle)
    {
        $font_size = mt_rand($font_size_min, $font_size_max);
        $angle     = rand(-1 * $max_angle, $max_angle);
        list($x, $y) = static::calcTextRandomCenter(
            $wp_width, $wp_height,
            $text, $font, $font_size, $angle
        );

        return [$x, $y, $font_size, $angle];
    }

    /**
     * @param $width
     * @param $height
     * @param $text
     * @param $font
     * @param $font_size
     * @param $font_size_flex
     * @param $text_angle
     *
     * @return array
     */
    private static function calcTextDistorted($width, $height, $text, $font, $font_size, $font_size_flex, $text_angle)
    {
        $letters_count = strlen($text);
        $wp_width      = $width / $letters_count;
        $wp_height     = $height;
        $font_size_min = ceil($font_size * (1 - $font_size_flex));
        $font_size_max = floor($font_size * 1);

        $results = [];
        for ($i = 0; $i < $letters_count; $i++) {
            $letter = $text[$i];
            list ($x, $y, $font_size, $angle) = static::calcTextPosition(
                $wp_width, $wp_height,
                $letter, $font,
                $font_size_min, $font_size_max,
                rand(-1 * $text_angle, $text_angle)
            );
            $x = floor($x + $wp_width * $i);

            $results[] = [
                $x, $y,
                $letter, $font_size,
                $angle,
            ];
        }

        return $results;
    }
}
