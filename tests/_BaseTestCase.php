<?php

namespace Tests;

use Maksym\Config\ConfigDriver;
use Maksym\Config\ConfigException;
use PHPUnit\Framework\TestCase;

abstract class _BaseTestCase extends TestCase
{
    /**
     * @var ConfigDriver
     */
    protected static $config_instance;

    /**
     * @return void
     * @throws ConfigException
     */
    public static function setUpBeforeClass()
    {
        self::$config_instance = ConfigDriver::getInstance(__DIR__ . DIRECTORY_SEPARATOR . 'config');
    }
}
