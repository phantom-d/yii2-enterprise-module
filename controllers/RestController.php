<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
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
