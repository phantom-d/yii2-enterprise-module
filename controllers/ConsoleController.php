<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\controllers;

declare(ticks = 1);

use enterprise\traits\ConsoleTrait;
use enterprise\traits\ControllerTrait;
use yii\InvalidConfigException;

/**
 * Description of ConsoleController
 *
 * @property \enterprise\Module $module Base module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class ConsoleController extends \yii\console\Controller
{
    use ConsoleTrait,
        ControllerTrait {
        ControllerTrait::beforeAction as parentBeforeAction;
    }

    /**
     * {@inheritdoc}
     *
     * @param Action $action
     * @throws InvalidConfigException
     * @return bool whether the action should continue to run.
     */
    public function beforeAction($action)
    {
        $return = $this->parentBeforeAction($action);

        if (false === empty($action->singleMode)) {
            $this->initLogger();

            $this->renameProcess();
            $params = [
                'action' => $this->processName,
            ];

            $pidFile = $this->pidPath;
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                if ($elapsedTime = $this->isProcessRunning($pid, $this->processName)) {
                    $message = null;
                    $pidKill = null;
                    if ($action->processAlert) {
                        $message = \Yii::t('common', "Another '{action}' is already running.", $params);
                    }
                    if ($this->controllerConfig) {
                        $timeOut = intval($this->controllerConfig['actions'][$action->id]['restart'] ?? 0);
                        if ($timeOut && $elapsedTime > $timeOut) {
                            $params['pid'] = $pid;
                            $message = \Yii::t('common', "Action '{action}' with PID={pid} was expired.", $params);
                            $pidKill = $pid;
                        }
                    }
                    $this->halt(self::EXIT_CODE_ERROR, $message, $pidKill);
                }
            }
            file_put_contents($pidFile, getmypid());
            \Yii::info(\Yii::t('common', 'Start action: {action}', $params));
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        if (false === empty($action->singleMode)) {
            $params = [
                'action' => $this->route,
            ];
            \Yii::info(\Yii::t('common', 'End action: {action}', $params));

            $pidFile = $this->pidPath;
            if (file_exists($pidFile)) {
                unlink($pidFile);
            }
        }

        return parent::afterAction($action, $result);
    }
}
