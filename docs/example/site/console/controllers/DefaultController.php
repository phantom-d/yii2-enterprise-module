<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace modules\site\console\controllers;

use yii\console\Controller;

/**
 * Default controller for the `site` module
 */
class DefaultController extends \enterprise\controllers\ConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => actions\DefaultIndexAction::class,
        ];
    }
}
