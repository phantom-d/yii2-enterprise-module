<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace modules\site\console\services\pages;

/**
 * Class DefaultIndexService - Site service
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class DefaultIndexService extends \enterprise\Component
{
    /**
     * Test
     */
    public function run()
    {
        $this->module->services->site->console();
    }
}
