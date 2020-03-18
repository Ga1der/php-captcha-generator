<?php

use CaptchaGenerator\CaptchaGenerator;

include realpath(__DIR__ . "/./Calc.php");
include realpath(__DIR__ . "/./BaseCaptchaGenerator.php");
include realpath(__DIR__ . "/./CaptchaGenerator.php");

list ($text, $imageBase64) = (new CaptchaGenerator([
    'color_text'       => 0x00FF3333,
    'color_background' => CaptchaGenerator::calcRandomColor(0x00003333, 0x0000FFFF),
    'width'            => 320,
    'height'           => 90,
]))->lazy()->imageJpeg(100);

echo $text . PHP_EOL;
file_put_contents(__FILE__ . '.jpg', base64_decode($imageBase64));

