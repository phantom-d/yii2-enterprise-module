<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\models;

use enterprise\helpers\StringHelper;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class ActiveRecord
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    use \enterprise\traits\ModulesTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
            ],
        ];
    }

    /**
     * Возвращает родительский объект
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Запись в лог ошибки сохранения в базу данных
     *
     * @param array $errors Массив ошибок обработки
     */
    protected static function errorLogHandler($errors)
    {
        if (empty($errors)) {
            return;
        }

        $message = '';
        foreach ($errors as $name => $error) {
            $message .= "    {$name}:\n";
            foreach ($error as $value) {
                $message .= "        - {$value}\n";
            }
        }
        \Yii::warning(\Yii::t('common', "Ошибка сохранения:\n{errors}", ['errors' => $message]));
    }
}
