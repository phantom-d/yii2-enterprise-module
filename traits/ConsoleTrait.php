<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\traits;

use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * List of special parameters
 *
 * @property string $processName Process name
 * @property \yii\Action $action
 * @property string $pidPath Current process id
 * @property \enterprise\Module $module Parent module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
trait ConsoleTrait
{

    public $logConfig = [
        'dateTime' => true,
        'dir'      => 'cron/logs',
        'levels'   => ['error', 'warning', 'info'],
    ];

    /**
     * @var string Directory for save pid file
     */
    private $_pidDir = '@runtime/cron/pids';

    /**
     * @var string Module, controller and action underscore separated
     */
    private $_processName;

    /**
     * Get process name
     *
     * @return string
     */
    protected function getProcessName()
    {
        if (empty($this->_processName)) {
            $this->_processName = str_replace('/', '_', $this->route);
            if (isset($this->action) && $this->action->processNameArgs) {
                $params = \Yii::$app->request->params;
                array_shift($params);
                $this->_processName .= ' ' . implode(' ', $params);
            }
        }
        return $this->_processName;
    }

    /**
     * Get full path to pid file
     *
     * @return string
     */
    public function getPidPath()
    {
        $return = \Yii::getAlias($this->_pidDir)
            . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9-_.=]/', '-', $this->processName);

        $dir = StringHelper::dirname($return);
        FileHelper::createDirectory($dir);
        return $return;
    }

    /**
     * Check is process running by ID and name
     *
     * @param $pid Process ID
     * @param $name Process name
     * @return integer Elapsed time, seconds
     */
    public function isProcessRunning($pid, $name = '')
    {
        $return = 0;
        if ('' !== (string)$name) {
            $name = "| /usr/bin/env grep -i '{$name}'";
        }
        $command = "/usr/bin/env ps -p {$pid} -o args 2>&1 {$name}";
        if (boolval(`{$command}`)) {
            $command = "/usr/bin/env ps -p {$pid} -o etimes 2>&1 | tail -1 | /usr/bin/env awk '{ print $1 }'";
            $return = intval(`{$command}`);
        }
        return $return;
    }

    /**
     * Rename process name
     *
     * @param string $prefix Prefix for the process name
     */
    protected function renameProcess($prefix = '')
    {
        $name = $this->processName;
        if (false === empty($prefix)) {
            $name = $prefix . '-' . $name;
        }

        cli_set_process_title($this->processName);
    }

    /**
     * Get classname without namespace
     *
     * @return string
     */
    public function shortClassName($object = null)
    {
        $class = is_object($object) ? get_class($object) : get_called_class();
        return StringHelper::basename($class);
    }

    /**
     * Stop process and show or write message
     *
     * @param integer $code Code completion -1|0|1
     * @param string $message Message
     * @param integer $pid Process ID to kill
     */
    protected function halt($code, $message = null, $pid = null)
    {
        if ($message !== null) {
            if ($code == static::EXIT_CODE_ERROR) {
                \Yii::error($message);
                $message = Console::ansiFormat($message, [Console::FG_RED]);
            } else {
                YII_DEBUG && \Yii::info($message);
            }
            $this->_writeConsole($message);
        }
        if (-1 !== $code && null === $pid) {
            exit($code);
        }
        if ($pid) {
            $pid = intval($pid);
            if ($pid && false === posix_kill($pid, SIGTERM)) {
                $message = "Hard kill process with PID={$pid}";
                \Yii::error($message);
                $message = Console::ansiFormat($message, [Console::FG_RED]);
                $this->_writeConsole($message);
                posix_kill($pid, SIGKILL);
            }
        }
    }

    /**
     * Show message in console
     *
     * @param string $message Message
     */
    private function _writeConsole($message)
    {
        $out = Console::ansiFormat('[' . date('Y-m-d H:i:s') . '] ', [Console::BOLD]);
        $this->stdout($out . $message . "\n");
    }

    /**
     * Adjusting logger. You can override it.
     */
    protected function initLogger()
    {
        $this->logConfig['name'] = str_replace(' ', '_', $this->processName);
        if (YII_ENV_DEV) {
            $this->logConfig['levels'][] = 'trace';
            $this->logConfig['levels'][] = 'profile';
        }

        $this->module->componentsLogger($this->logConfig);
    }
}
