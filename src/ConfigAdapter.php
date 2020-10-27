<?php

declare(strict_types=1);

namespace Dog\Alarm;

use Exception;
use Hyperf\Contract\ConfigInterface as HyperfConfigInterface;
use Hyperf\Utils\ApplicationContext;
use Illuminate\Container\Container as LaravelContainer;
use RuntimeException;
use Throwable;

class ConfigAdapter
{
    /**
     * @var ConfigInterface
     */
    public static $config;

    /**
     * 判断是否可以自动适配.
     *
     * @return bool
     */
    public static function canAdapt()
    {
        try {
            static::adapter();
            return true;
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param ConfigInterface $configer
     */
    public static function setConfiger($configer)
    {
        static::$config = $configer;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return static::adapter()->get($key, $default);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return static::adapter()->has($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        return static::adapter()->set($key, $value);
    }

    /**
     * @throws RuntimeException
     * @return ConfigInterface
     */
    protected static function adapter()
    {
        if (! is_null(static::$config)) {
            return static::$config;
        }

        // 探测hyperf
        if (class_exists(ApplicationContext::class)) {
            static::$config = ApplicationContext::getContainer()->get(HyperfConfigInterface::class);
            return static::$config;
        }

        // 探测laravel、lumen version5~7
        if (class_exists(LaravelContainer::class)) {
            static::$config = LaravelContainer::getInstance()->make('config');
            return static::$config;
        }

        // 探测fend，引静态方法无法兼容，暂时不兼容

        throw new RuntimeException(
            'config class detect failed in ConfigAdapter, please manual set config class using ' .
            'ConfigAdapter::setConfiger($configer)'
        );
    }
}
