<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace modules\site\frontend\controllers;

/**
 * Default controller
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class DefaultController extends \enterprise\controllers\WebController
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => yii\web\ErrorAction::class,
            'index' => actions\DefaultIndexAction::class,
        ];
    }
}
