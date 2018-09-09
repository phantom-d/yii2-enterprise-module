<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace modules\site\frontend\controllers\actions;

/**
 * Class DefaultIndexAction
 *
 * @property \modules\site\frontend\services\pages\DefaultIndexService $page Сервис
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class DefaultIndexAction extends \enterprise\controllers\Action
{

    /**
     * @return string
     */
    public function run()
    {
        return $this->controller->render('index', $this->page->run());
    }
}
