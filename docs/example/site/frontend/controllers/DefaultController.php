<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
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
