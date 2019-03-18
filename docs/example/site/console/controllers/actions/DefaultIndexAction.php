<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
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
