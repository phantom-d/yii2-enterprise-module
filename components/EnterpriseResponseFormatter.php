<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\components;

use enterprise\controllers\ActionModuleInterface;
use enterprise\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Description of EnterpriseResponseFormatter
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class EnterpriseResponseFormatter extends \yii\web\JsonResponseFormatter
{
    /**
     * @var array Structure
     */
    private $_sendData = [
        'success' => true,
        'info'    => [
            'date'  => null,
            'links' => null,
            'meta'  => null,
        ],
        'data'    => null,
    ];

    /**
     * {@inheritdoc}
     */
    protected function formatJson($response)
    {
        if ($response->data !== null) {
            $response->data = $this->formatBase($response->data);
        }
        parent::formatJson($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatJsonp($response)
    {
        if (is_array($response->data) && isset($response->data['data'], $response->data['callback'])) {
            $response->data['data'] = $this->formatBase($response->data['data']);
        }
        parent::formatJsonp($response);
    }

    private function formatBase($data)
    {
        $controller = \Yii::$app->controller;
        $allowed = isset($controller->allowedActions) ? array_flip($controller->allowedActions) : [];

        if ($controller->action instanceof ActionModuleInterface) {
            $allowed = array_flip($controller->allowedActions);
            if (isset($allowed[$controller->action->id])) {
                return $data;
            }
        }

        $return = $this->_sendData;
        $return['info']['date'] = date('c');

        if (\Yii::$app->getErrorHandler()->exception || $data instanceof \Exception) {
            $return['success'] = false;
            $return['data'] = $this->prepareError($data);
        } else {
            if (is_array($data)) {
                if (isset($data['_links'])) {
                    $return['info']['links'] = $data['_links'];
                    unset($data['_links']);
                }
                if (isset($data['_meta'])) {
                    $return['info']['meta'] = $data['_meta'];
                    unset($data['_meta']);
                    if (isset($data['items'])) {
                        $return['data'] = $data['items'];
                        unset($data['items']);
                    }
                }
            }
            if ($data) {
                if (empty($return['data'])) {
                    $return['data'] = $data;
                } else {
                    $return['data'] = ArrayHelper::extendMerge($return['data'], $data);
                }
            }
        }
        return $return;
    }

    private function prepareError($data)
    {
        if ($data instanceof \Exception) {
            $exception = $data;
        } else {
            $exception = \Yii::$app->getErrorHandler()->exception;
        }

        $name = 'Exception';
        if (method_exists($exception, 'getName')) {
            $name = $exception->getName();
            if ($exception->getPrevious()) {
                $name = $exception->getPrevious()->getName();
            }
        }

        $status = 400;
        if (isset($exception->statusCode)) {
            $status = (int)$exception->statusCode;
        }

        if ($data instanceof \Exception) {
            \Yii::$app->response->setStatusCode($status, $name);
        }

        $code = $exception->getCode();
        if (empty($code)) {
            $code = $status;
        }

        try {
            $message = Json::decode($exception->getMessage());
        } catch (\Exception $e) {
            $message = $exception->getMessage();
        }

        if (empty($message)) {
            $message = \Yii::t('yii', $name);
        }

        $return = [
            'error' => [
                'code'    => $code,
                'name'    => \Yii::t('yii', $name) . ' (#' . $status . ')',
                'message' => $message,
            ]
        ];

        if (YII_ENV_DEV) {
            $return['error']['file'] = $exception->getFile();
            $return['error']['line'] = $exception->getLine();

            if ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }

            $return['trace'] = preg_split(
                '!\n?[#0-9]+\s!',
                $exception->getTraceAsString(),
                -1,
                PREG_SPLIT_NO_EMPTY
            );
        }

        return $return;
    }
}
