<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport\models\rabbitmq;

use enterprise\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Description of BufferedMessage
 *
 * @property string $id         Идентификатор
 * @property string $exchange   Точка обмена
 * @property array  $params     Параметры отправки сообщения в RabbitMQ
 * @property array  $body       Данные
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class BufferedMessage extends \enterprise\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return \Yii::$app->get('base-sqlite');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['exchange', 'params', 'body'], 'string'],
            [['exchange', 'params', 'body'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        if (false === is_string($this->params)) {
            $value = ArrayHelper::toArray($this->params);
            $this->params = Json::encode($value);
        }
        if (false === is_string($this->body)) {
            $value = ArrayHelper::toArray($this->body);
            $this->body = Json::encode($value);
        }
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        $this->after();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->after();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Prepare for usig json as object
     */
    private function after()
    {
        if (is_string($this->body)) {
            $this->body = Json::decode($this->body);
        }
        if (is_string($this->params)) {
            $this->params = Json::decode($this->params);
        }
    }
}
