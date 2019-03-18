<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport\services;

use yii\Exception;
use yii\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * SOAP service
 *
 * Для использования данного сервиса необходимо добавить и настроить расширение `ext-soap: *`
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class SoapService extends BaseService
{
    /**
     * <table cellspacing="0">
     *     <tr>
     *         <td>string</td>
     *         <td><b>$params['url']</b></td>
     *         <td>URI файла WSDL или NULL, если работа происходит в режиме не-WSDL.</td>
     *     </tr>
     *     <tr>
     *         <td>array</td>
     *         <td><b>$params['options']</b></td>
     *         <td>Массив настроек (see [[\SoapClient::SoapClient]])</td></tr>
     * </table>
     *
     * @param mixed $params
     * @return $this
     */
    public function setConnection($params)
    {
        $data['url'] = $params['url'] ?? null;
        $data['options'] = $params['options'] ?? null;
        return parent::setConnection($data);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function send($url, $data, $params = [], $buffer = true)
    {
        try {
            if ($connection = $params['client'] ?? null) {
                $this->setConnection($connection);
                unset($params['client']);
            }
            return call_user_func_array([$this->getClient(), $url], $data);
        } catch (\SoapFault $e) {
            throw new \yii\web\BadRequestHttpException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \yii\InvalidConfigException
     * @throws \yii\Exception
     * @return \SoapClient
     */
    protected function getClient(): \SoapClient
    {
        if (null === $this->client) {
            $url = $this->connection['url'] ?? null;
            $options = $this->connection['options'] ?? null;
            if (empty($url) && false === isset($options['location'], $options['uri'])) {
                throw new InvalidConfigException('You need to set property `connection`!');
            }
            try {
                $this->client = new \SoapClient($url, $options);
            } catch (\SoapFault $e) {
                throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        return $this->client;
    }
}
