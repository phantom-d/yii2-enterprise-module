<?php

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
