<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport\services;

use mikemadisonweb\rabbitmq\Configuration;
use yii\web\BadRequestHttpException;

/**
 * RabbitMQ service
 *
 * Для использования данного сервиса необходимо добавить и настроить расширение `mikemadisonweb/yii2-rabbitmq: ^2.0.0`
 *
 * @property array $lastMessage Последнее отправленное сообщение
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class RabbitmqService extends BaseService
{
    /**
     * @var array Последнее отправленное сообщение
     */
    private $_lastMessage;

    /**
     * @var boolean Отправка сообщений из буфера
     */
    private $_sendBuffer = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->checkDB();
        $this->sendBuffer();
    }

    /**
     * Возвращает последнее отправленное сообщение
     *
     * @return array
     */
    public function getLastMessage()
    {
        return $this->_lastMessage;
    }

    /**
     * Отправляем сообщение в точку обмена, настроенную в конфигурации приложения.
     *
     * <table cellspacing="0">
     *     <tr><td>string</td><td><b>$data['version']</b></td><td>Версия сообщения</td></tr>
     *     <tr><td>string</td><td><b>$data['source']</b></td><td>Наименование источника данных</td></tr>
     *     <tr><td>string</td><td><b>$data['sourceDetail']</b></td>
     *         <td>
     *             Детальная информация об источнике данных
     *         </td>
     *     </tr>
     *     <tr><td>array</td><td><b>$data['data']</b></td><td>Данные для отправки</td></tr>
     *     <tr><td>array</td><td><b>$data['sourceData']</b></td><td>Исходные данные</td></tr>
     * </table><br>
     * <table cellspacing="0">
     *     <tr><td>string</td><td><b>$params['producer']</b></td><td>Producer</td></tr>
     *     <tr><td>string</td><td><b>$params['routing_key']</b></td><td>Routing key</td></tr>
     *     <tr><td>array</td><td><b>$params['client']</b></td><td>Connection parameters</td></tr>
     * </table>
     *
     * {@inheritdoc}
     * @throws \mikemadisonweb\rabbitmq\exceptions\RuntimeException
     * @throws \yii\web\BadRequestHttpException
     */
    public function send($url, $data, $params = [], $buffer = true)
    {
        if ($connection = $params['client'] ?? null) {
            $this->setConnection($connection);
            unset($params['client']);
        }
        extract((array)$params);
        /* @var $producer \mikemadisonweb\rabbitmq\components\Producer */
        $producer = $producer ?? 'default';
        $route = $routing_key ?? '';
        $return = false;

        try {
            $this->sendBuffer();
            if ($this->prepareMessage($data, $url, $params) &&
                $producer = $this->getClient()->getProducer($producer)
            ) {
                $producer->publish($this->_lastMessage, $url, $route);
                $return = true;
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            if ($url && $buffer) {
                $this->_sendBuffer = true;
                /* @var $model \enterprise\transport\models\rabbitmq\BufferedMessage */
                $model = $this->models->{'rabbitmq\bufferedMessage'};
                $model->setAttributes([
                    'exchange' => $url,
                    'params'   => $params,
                    'body'     => $data
                ]);
                if ($model->validate() && null === $model->findOne($model->id)) {
                    $model->save();
                }
            }
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     * @return \mikemadisonweb\rabbitmq\Configuration
     */
    protected function getClient(): Configuration
    {
        if (null === $this->client) {
            if ($this->connection && class_exists('mikemadisonweb\rabbitmq\Configuration')) {
                $this->client = \Yii::createObject('mikemadisonweb\rabbitmq\Configuration', $this->connection);
            } else {
                $this->client = isset(\Yii::$app->rabbitmq) ? \Yii::$app->rabbitmq : null;
            }
            if (empty($this->client)) {
                $message = 'You need to set component `mikemadisonweb/yii2-rabbitmq: ^2.0.0` to composer.json!';
                throw new \yii\InvalidConfigException($message);
            }
        }

        $this->client
            ->getConnection()
            ->getIO()
            ->check_heartbeat();

        return $this->client;
    }

    /**
     * Подготовка сообщения для отправки в RabbitMQ
     *
     * @param array $data Данные для отправки (See [[sendMessage]])
     * @param string $exchange Точка обмена
     * @param array $params Message params (See above)
     * @throws \yii\InvalidArgumentException
     * @return boolean
     *
     */
    protected function prepareMessage($data, $exchange, $params = [])
    {
        extract((array)$params);
        $producer = $producer ?? 'default';
        $route = $routing_key ?? '';
        $return = true;

        if (empty($exchange)) {
            throw new \yii\InvalidArgumentException('Не назначен обязательный параметр `$exchange`');
        }

        try {
            $data['type'] = $exchange;
            if (false === isset($data['source']) && isset($this->componentConfig['source'])) {
                $data['source'] = $this->componentConfig['source'];
            }
            /* @var $message \enterprise\transport\models\rabbitmq\Message */
            $message = $this->models->{'rabbitmq\message'};
            $message->setAttributes($data);
            $this->_lastMessage = $message->toArray();
        } catch (\mikemadisonweb\rabbitmq\exceptions\RuntimeException $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $this->_lastMessage = null;
            $return = false;
        }
        return $return;
    }

    /**
     * Отправляем сообщения, которые не ушли по какой либо причине.
     */
    protected function sendBuffer()
    {
        if (false === $this->_sendBuffer) {
            return;
        }
        $this->_sendBuffer = false;
        /* @var $message \enterprise\transport\models\rabbitmq\BufferedMessage */
        $model = $this->models->{'rabbitmq\bufferedMessage'};
        if ($model->find()->count()) {
            $errors = 0;
            while ($buffer = $model->find()->one()) {
                try {
                    if ($this->send($buffer->exchange, $buffer->body, $buffer->params, false)) {
                        $delete = $buffer->delete();
                    }
                } catch (\ErrorException $e) {
                    \Yii::error(PHP_EOL . $e->getMessage(), __METHOD__);
                    $this->_sendBuffer = true;
                }
            }
            if ($this->_sendBuffer) {
                \Yii::error('Проблемы с отправкой в очередь.', __METHOD__);
            }
        }
    }

    /**
     * Проверка наличия базы данных и таблицы
     */
    protected function checkDB()
    {
        /* @var $model \enterprise\transport\models\rabbitmq\BufferedMessage */
        $model = $this->models->{'rabbitmq\bufferedMessage'};
        try {
            $schema = $model->getTableSchema();
        } catch (\Exception $e) {
            $this->createTable();
        }
    }

    /**
     * Создание буферной таблицы
     */
    protected function createTable()
    {
        /* @var $model \enterprise\transport\models\rabbitmq\BufferedMessage */
        $model = $this->models->{'rabbitmq\bufferedMessage'};

        /* @var $migration \yii\db\Migration */
        $migration = \yii\di\Instance::ensure(\yii\db\Migration::class);
        $migration->db = $model->getDb();
        $migration->createTable($model->tableName(), [
            'id'       => $migration->primaryKey(),
            'exchange' => $migration->string(255),
            'body'     => $migration->binary(),
            'params'   => $migration->string(255),
        ]);
    }
}
