<?php

namespace Tests;

use Maksym\DebugPanel\DebugPanelDriver;
use Maksym\Config\ConfigException;

class DebugPanelDriverTest extends _BaseTestCase
{
    /** @var DebugPanelDriver */
    private static $debug_instance;

    /**
     * @return void
     * @throws ConfigException
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$config_instance->set('IS_DEBUG', true);
        self::$debug_instance = DebugPanelDriver::getInstance();
    }

    /**
     * @return void
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('Maksym\DebugPanel\DebugPanelDriver', self::$debug_instance);
    }

    /**
     * @return void
     * @throws ConfigException
     */
    public function testSetAndGet()
    {
        self::$config_instance->set('IS_DEBUG', false);
        $res = self::$debug_instance->_get("sqlLog");
        $this->assertContains("This works only in debug mode, please put IS_DEBUG => true into config/main.php", $res);
        self::$config_instance->set('IS_DEBUG', true);
        self::$debug_instance->_set("sqlLog", 'SELECT version()');
        self::$debug_instance->_set("sqlLog", array('SELECT 1'));
        $res = self::$debug_instance->_get("sqlLog");
        $this->assertContains('SELECT 1', $res);
        $this->assertContains('SELECT version()', $res);
        $res = self::$debug_instance->_get("notExistContainer");
        $this->assertNull($res);
    }

    /**
     * @return void
     */
    public function testSetBootTiming()
    {
        self::$debug_instance->setBootTiming();
        $class = new \ReflectionClass(self::$debug_instance);
        $property = $class->getProperty('timingData');
        $property->setAccessible(true);
        $first = $property->getValue(self::$debug_instance);
        usleep(30);
        self::$debug_instance->setBootTiming();
        $second = $property->getValue(self::$debug_instance);
        $this->assertNotEquals($first, $second);
    }

    /**
     * @return void
     */
    public function testSetAppTiming()
    {
        self::$debug_instance->setAppTiming();
        $class = new \ReflectionClass(self::$debug_instance);
        $property = $class->getProperty('timingData');
        $property->setAccessible(true);
        $first = $property->getValue(self::$debug_instance);
        usleep(30);
        self::$debug_instance->setAppTiming();
        $second = $property->getValue(self::$debug_instance);
        $this->assertNotEquals($first, $second);
    }

    /**
     * @return void
     * @throws ConfigException
     */
    public function testShowDebugPanel()
    {
        config()->set('SHOW_DEBUG_PANEL', true);
        $res = self::$debug_instance->showDebugPanel();
        $this->assertContains("div.phpdebugbar-dump-console pre", $res);
        $this->assertContains("function showPanel()", $res);
        $this->assertContains('<a class="phpdebugbar-restore-btn">Debug</a>', $res);

        config()->set('SHOW_DEBUG_PANEL', false);
        $res = self::$debug_instance->showDebugPanel();
        $this->assertNotContains("function showPanel()", $res);
        $this->assertEmpty($res);
    }
}