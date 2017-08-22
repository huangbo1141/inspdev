<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit82c510a8c3a7de9fa7a6850477ca3230
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Ddeboer\\Transcoder\\Tests\\' => 25,
            'Ddeboer\\Transcoder\\' => 19,
            'Ddeboer\\Imap\\Tests\\' => 19,
            'Ddeboer\\Imap\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ddeboer\\Transcoder\\Tests\\' => 
        array (
            0 => __DIR__ . '/..' . '/ddeboer/transcoder/tests',
        ),
        'Ddeboer\\Transcoder\\' => 
        array (
            0 => __DIR__ . '/..' . '/ddeboer/transcoder/src',
        ),
        'Ddeboer\\Imap\\Tests\\' => 
        array (
            0 => __DIR__ . '/..' . '/ddeboer/imap/tests',
        ),
        'Ddeboer\\Imap\\' => 
        array (
            0 => __DIR__ . '/..' . '/ddeboer/imap/src',
        ),
    );

    public static $prefixesPsr0 = array (
        's' => 
        array (
            'stringEncode' => 
            array (
                0 => __DIR__ . '/..' . '/paquettg/string-encode/src',
            ),
        ),
        'P' => 
        array (
            'PHPHtmlParser' => 
            array (
                0 => __DIR__ . '/..' . '/paquettg/php-html-parser/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit82c510a8c3a7de9fa7a6850477ca3230::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit82c510a8c3a7de9fa7a6850477ca3230::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit82c510a8c3a7de9fa7a6850477ca3230::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
