<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\components;

use enterprise\components\EnterpriseResponseFormatter;

/**
 * Class Response
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Response extends \yii\web\Response
{

    const FORMAT_ENTERPRISE = 'enterprise_json';

    public $formatters = [
        self::FORMAT_ENTERPRISE => [
            'class'       => EnterpriseResponseFormatter::class,
            'prettyPrint' => YII_DEBUG,
        ],
    ];
}
