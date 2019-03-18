<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport;

use yii\helpers\FileHelper;

/**
 * RabbitMQ module definition class
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Module extends \enterprise\Module
{
    /**
     * {@inheritdoc}
     */
    protected $addComponents = [
        'base-sqlite' => [
            'class' => 'yii\db\Connection',
            'dsn'   => 'sqlite:@console/runtime/filedb/base.sqlite',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $path = explode(':', $this->addComponents['base-sqlite']['dsn'])[1];
        FileHelper::createDirectory(\Yii::getAlias(dirname($path)));
        parent::init();
    }
}
