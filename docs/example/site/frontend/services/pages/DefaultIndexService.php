<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace modules\site\frontend\services\pages;

/**
 * Class DefaultIndexService - Site service
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class DefaultIndexService extends \enterprise\Component
{

    public function run()
    {
        return $this->module->services->site->index();
    }
}
