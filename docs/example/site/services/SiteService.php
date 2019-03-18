<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace modules\site\services;

use yii\helpers\Console;

/**
 * Class SiteService - Site service
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class SiteService extends \enterprise\Component
{
    /**
     * Sample logic
     *
     * @return array
     */
    public function index()
    {
        return [];
    }

    /**
     * Sample logic
     *
     * @return array
     */
    public function console()
    {
        Console::ansiFormat('Test message!', [Console::FG_GREEN]);
    }
}
