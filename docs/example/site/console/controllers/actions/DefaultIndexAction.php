<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace modules\site\console\controllers\actions;

/**
 * Class DefaultIndexAction
 *
 * @property \modules\site\console\services\pages\DefaultIndexService $page Сервис
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class DefaultIndexAction extends \enterprise\controllers\Action
{

    /**
     * @return integer
     */
    public function run()
    {
        $this->page->run();
        return \yii\console\ExitCode::OK;
    }
}
