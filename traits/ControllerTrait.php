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
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
trait ControllerTrait
{

    /**
     * Allowed actions
     */
    protected $allowedActions = [];

    /**
     * @var array
     */
    public $controllerConfig;

    /**
     * Return allowed actions
     *
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->allowedActions;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $return = parent::beforeAction($action);

        $app = substr(strrchr(\Yii::getAlias('@app'), '/'), 1);
        $this->controllerConfig = $this->module->moduleConfig[$app]['controllers'][$this->id] ?? null;

        if (false === in_array($action->id, $this->allowedActions) &&
            false === ($action instanceof \enterprise\controllers\ActionModuleInterface)
        ) {
            if (YII_ENV_DEV) {
                $message = 'Класс {action} должен наследоваться от {actionModule}';
                $params = [
                    'action'       => get_class($action),
                    'actionModule' => Action::class,
                ];
            } else {
                $message = \Yii::t('yii', 'Internal server error');
                $params = [];
            }

            throw new \yii\InvalidConfigException(\Yii::t('common', $message, $params));
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function runAction($id, $params = [])
    {
        if (\Yii::$app->response instanceof \yii\web\Response) {
            if (false === \Yii::$app->response->isSuccessful) {
                return parent::runAction($id, $params);
            }
        }

        try {
            return parent::runAction($id, $params);
        } catch (\Exception $e) {
            \Yii::$app->getErrorHandler()->handleException($e);
        }
    }
}
