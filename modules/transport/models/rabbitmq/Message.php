<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\transport\models\rabbitmq;

use enterprise\helpers\StringHelper;
use yii\helpers\Json;

/**
 * Description of Message
 *
 * @property string $version        Версия сообщения
 * @property string $source         Наименование источника данных
 * @property string $sourceDetail   Детальная информация об источнике данных
 * @property string $type           Тип сообщения (exchange/queue)
 * @property array  $data           Данные, которые передаём в сообщении
 * @property array  $sourceData     Источник данных, из которых брали информацию
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Message extends \enterprise\models\Model
{

    /**
     * @var array Информационный заголовок. (мета данные)
     */
    private $_message = [
        'info'   => [
            // exchange/queue
            'type'          => '',
            // Версия сообщения
            'version'       => '1.0',
            // Наименование источника данных
            'source'        => '',
            // Детальная информация об источнике
            'source_detail' => '',
            // Дата и время сообщения в формате ISO 8601 с часовым поясом
            'date'          => '',
            // Идентификатор сообщения. MD5 хэш данных
            'sign'          => '',
        ],
        'data'   => null,
        'source' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'source', 'sourceDetail', 'type', 'data', 'sourceData', ], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'version'       => 'Версия сообщения',
            'source'        => 'Наименование источника данных',
            'source_detail' => 'Детальная информация об источнике данных',
            'type'          => 'Тип сообщения (exchange/queue)',
            'data'          => 'Данные, которые передаём в сообщении',
            'source_data'   => 'Источник данных, из которых брали информацию',
        ];
    }

    /**
     * @param string $value
     */
    public function setVersion($value)
    {
        $value = StringHelper::byteLength(strval($value)) ? strval($value) : '1.0';
        $this->_message['info']['version'] = $value;
        return $this;
    }

    /**
     * @param string $value
     */
    public function setSource($value)
    {
        $this->_message['info']['source'] = strval($value);
        return $this;
    }

    /**
     * @param string $value
     */
    public function setSourceDetail($value)
    {
        $this->_message['info']['source_detail'] = strval($value);
        return $this;
    }

    /**
     * @param string $value
     */
    public function setType($value)
    {
        $this->_message['info']['type'] = strval($value);
        return $this;
    }

    /**
     * @param array $value
     */
    public function setData($value)
    {
        $this->_message['data'] = empty($value) ? null : (array)$value;
        return $this;
    }

    /**
     * @param array $value
     */
    public function setSourceData($value)
    {
        $this->_message['source'] = empty($value) ? null : (array)$value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $this->_message['info']['date'] = date('c');
        $this->_message['info']['sign'] = md5(Json::encode($this->_message['data']));
        return $this->_message;
    }
}
