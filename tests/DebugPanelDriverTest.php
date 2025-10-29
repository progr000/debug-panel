<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Maksym\DebugPanel\DebugPanelDriver;

class DebugPanelDriverTest extends TestCase
{
    /** @var DebugPanelDriver */
    private static $debug_instance;

    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
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
     */
    public function testSetAndGet()
    {
        $res = self::$debug_instance->_get("sqlLog");
        $this->assertEmpty($res);
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
     */
    public function testShowDebugPanel()
    {
        $res = self::$debug_instance->showDebugPanel();
        $this->assertContains("div.phpdebugbar-dump-console pre", $res);
        $this->assertContains("function showPanel()", $res);
        $this->assertContains('<a class="phpdebugbar-restore-btn">Debug</a>', $res);
    }
}