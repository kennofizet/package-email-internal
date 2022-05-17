<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3a5f58f3bbdb2c80d55bf071c9a717fe
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Kennofizet\\EmailInternal\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Kennofizet\\EmailInternal\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3a5f58f3bbdb2c80d55bf071c9a717fe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3a5f58f3bbdb2c80d55bf071c9a717fe::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3a5f58f3bbdb2c80d55bf071c9a717fe::$classMap;

        }, null, ClassLoader::class);
    }
}
