<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace modules\site\console\controllers;

/**
 * {@inheritdoc}
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * @var string Имя таблицы в которой будут сохранена история миграций
     */
    public $migrationTable = '{{%migration_site}}';

    /**
     * @var string Путь к файлами миграций
     */
    public $migrationPath  = '@modules/site/migrations';
}
