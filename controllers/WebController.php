<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\controllers;

/**
 * Description of WebController
 *
 * @property \enterprise\Module $module Base module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class WebController extends \yii\web\Controller
{

    use \enterprise\traits\ControllerTrait;

    /**
     * {@inheritdoc}
     */
    public function render($view, $params = [])
    {
        \Yii::beginProfile($view, get_called_class());
        $return = parent::render($view, $params);
        \Yii::endProfile($view, get_called_class());

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function renderPartial($view, $params = [])
    {
        \Yii::beginProfile($view, get_called_class());
        $return = parent::renderPartial($view, $params);
        \Yii::endProfile($view, get_called_class());

        return $return;
    }
}
