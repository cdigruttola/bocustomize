<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1ce66f59002e12622e51cc8f3adba8ee
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'cdigruttola\\Module\\Bocustomize\\' => 31,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'cdigruttola\\Module\\Bocustomize\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Bocustomize' => __DIR__ . '/../..' . '/bocustomize.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1ce66f59002e12622e51cc8f3adba8ee::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1ce66f59002e12622e51cc8f3adba8ee::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1ce66f59002e12622e51cc8f3adba8ee::$classMap;

        }, null, ClassLoader::class);
    }
}
