<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitfa9fa4aad86bf9f48298e1bed9694093
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitfa9fa4aad86bf9f48298e1bed9694093', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitfa9fa4aad86bf9f48298e1bed9694093', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitfa9fa4aad86bf9f48298e1bed9694093::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
