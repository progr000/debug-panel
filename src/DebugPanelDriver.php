<?php

namespace Maksym\DebugPanel;

use Maksym\Config\ConfigException;
use stdClass;

class DebugPanelDriver extends stdClass
{
    const DEBUG_CSS_FILE = '/Assets/panel.css';
    const DEBUG_JS_FILE = '/Assets/panel.js';
    const DEBUG_HTML_FILE = '/Assets/panel.php';

    /** @var self */
    private static $instance;

    /** @var array */
    private $containers;

    /** @var array */
    private $timingData = array();


    /**
     * @return DebugPanelDriver
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor, load data into itself from config
     */
    private function __construct()
    {
        $this->timingData['BootStart'] = microtime(true);
    }

    /**
     * @param string $container
     * @param array|string $data
     * @return void
     * @throws ConfigException
     */
    public function _set($container_name, $data)
    {
        if (config('IS_DEBUG', false)) {
            if (!isset($this->containers[$container_name])) {
                $this->containers[$container_name] = array();
            }

            $this->containers[$container_name] = array_merge(
                $this->containers[$container_name],
                (is_array($data) ? $data : array($data))
            );
        }
    }

    /**
     * @param string $container
     * @return mixed
     * @throws ConfigException
     */
    public function _get($container_name)
    {
        if (config('IS_DEBUG', false)) {
            return isset($this->containers[$container_name])
                ? $this->containers[$container_name]
                : null;
        } else {
            return array("This works only in debug mode, please put IS_DEBUG => true into config/main.php");
        }
    }

    /**
     * @return void
     */
    public function setBootTiming()
    {
        $this->timingData['BootFinish'] = microtime(true);
        $this->timingData['AppStart'] = microtime(true);
    }

    /**
     * @return void
     */
    public function setAppTiming()
    {
        $this->timingData['AppFinish'] = microtime(true);
    }

    /**
     * @param array $vars
     * @return string
     * @throws ConfigException
     */
    public function showDebugPanel($vars = array())
    {
        if (config('SHOW_DEBUG_PANEL', false)) {
            return
                $this->getPanelCss() .
                PHP_EOL .
                $this->getPanelHtml($vars) .
                PHP_EOL .
                $this->getPanelJs() .
                PHP_EOL;
        }

        return '';
    }

    /**
     * @return string
     * @throws ConfigException
     */
    private function getPanelCss()
    {
        if (file_exists(__DIR__ . self::DEBUG_CSS_FILE)) {
            return "<style>" . self::minimize(file_get_contents(__DIR__ . self::DEBUG_CSS_FILE)) . "</style>";
            //return "<style>" . file_get_contents(__DIR__ . self::DEBUG_CSS_FILE) . "</style>";
        }

        return '';
    }

    /**
     * @return string
     * @throws ConfigException
     */
    private function getPanelJs()
    {
        if (file_exists(__DIR__ . self::DEBUG_JS_FILE)) {
            return "<script>" . self::minimize(file_get_contents(__DIR__ . self::DEBUG_JS_FILE)) . "</script>";
            //return "<script>" . file_get_contents(__DIR__ . self::DEBUG_JS_FILE) . "</script>";
        }

        return '';
    }

    /**
     * @param array $vars
     * @return false|string
     * @throws ConfigException
     */
    private function getPanelHtml($vars = array())
    {
        if (file_exists(__DIR__ . self::DEBUG_JS_FILE)) {

            ob_start();
            ob_implicit_flush(false);

            $__containers = $this->containers;
            foreach ($vars as $key => $value) {
                $__containers[$key] = $value;
            }
            $__timeline = $this->timingData;
            $__memory = memory_get_usage();

            include(__DIR__ . self::DEBUG_HTML_FILE);
            $buffer = ob_get_contents();
            ob_end_clean();

            return $buffer;
        }

        return '';
    }

    /**
     * TODO: create separate helper on packagist.org and move this function there (and other helpful functions)
     * @param string $str
     * @return string
     * @throws ConfigException
     */
    private static function minimize($str)
    {
        if (!config('minimize-plain-css-js', false)) {
            return $str;
        }
        return minimize($str);
    }
}