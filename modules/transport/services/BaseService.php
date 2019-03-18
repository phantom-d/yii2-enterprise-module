<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport\services;

use yii\Exception;
use yii\InvalidConfigException;

/**
 * Base transport service
 *
 * @property-write array $connection Connection parameters
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class BaseService extends \enterprise\Component
{
    /**
     * @var array Connection parameters
     */
    protected $connection;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * @param array $params Set connection parameters (see above)
     * @return $this
     */
    public function setConnection($params)
    {
        $this->connection = $params;
        $this->client = null;
        return $this;
    }

    /**
     * Send data
     *
     * <table cellspacing="0">
     *     <tr><td>array</td><td><b>$params['client']</b></td><td>Connection parameters</td></tr>
     * </table>
     *
     * @param string $url Exchange
     * @param mixed $data Data for sending (see above)
     * @param array $params Parameters (see above)
     * @param boolean $buffer Save to buffer
     */
    abstract public function send(string $url, array $data, array $params = [], bool $buffer = true);

    /**
     * Connection
     */
    abstract protected function getClient();
}
