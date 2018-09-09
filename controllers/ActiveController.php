<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\controllers;

/**
 * ActiveController
 *
 * @property \enterprise\Module $module Base module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class ActiveController extends \yii\rest\ActiveController
{

    use \enterprise\traits\ControllerTrait,
        \enterprise\traits\RestTrait;
}
