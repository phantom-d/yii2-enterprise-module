<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\controllers;

/**
 * RestController
 *
 * @property \enterprise\Module $module Base module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class RestController extends \yii\rest\Controller
{

    use \enterprise\traits\ControllerTrait,
        \enterprise\traits\RestTrait;
}
