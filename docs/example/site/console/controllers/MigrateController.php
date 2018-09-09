<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
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
