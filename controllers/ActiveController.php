<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
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
