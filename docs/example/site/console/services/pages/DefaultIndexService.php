<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
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
