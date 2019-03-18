<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\helpers;

use bupy7\bbcode\Parser;
use yii\di\Instance;

/**
 * StringHelper
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Возвращает отформатированный массив для вывода
     *
     * ```php
     *
     * $config = [
     *     'id'                  => 'app-frontend',
     *     'basePath'            => dirname(__DIR__),
     *     'bootstrap'           => [],
     *     'controllerNamespace' => 'frontend\controllers',
     *     'components'          => [
     *         'user'         => [
     *             'identityClass'   => 'common\models\User',
     *             'enableAutoLogin' => true,
     *             'identityCookie'  => ['name' => '_identity-frontend', 'httpOnly' => true],
     *         ],
     *         'session'      => [
     *             'name' => 'advanced-frontend',
     *         ],
     *         'errorHandler' => [
     *             'errorAction' => 'site/error',
     *         ],
     *     ],
     * ];
     *
     * echo StringHelper::formatArray('config', $config, 0);
     *
     * ```
     *
     * Результат:
     *
     * ````
     *
     * config:
     *     id: string(12) "app-frontend"
     *     basePath: string(34) "/var/www/frontend"
     *     bootstrap:
     *     controllerNamespace: string(20) "frontend\controllers"
     *     components:
     *         user:
     *             identityClass: string(18) "common\models\User"
     *             enableAutoLogin: bool(true)
     *             identityCookie:
     *                 name: string(18) "_identity-frontend"
     *                 httpOnly: bool(true)
     *         session:
     *             name: string(17) "advanced-frontend"
     *         errorHandler:
     *             errorAction: string(10) "site/error"
     *
     * ````
     *
     * @param string $name Наименование данных
     * @param array $data Данные
     * @param integer $tab Количество отступов слева|>=0
     * @return string
     */
    public static function formatArray($name, $data, $tab = 1)
    {
        ini_set('xdebug.overload_var_dump', false);
        $tab       = intval($tab);
        $return    = '';
        $tabString = '    ';

        if (mb_strlen(strval($name)) > 0) {
            $return = str_repeat($tabString, $tab) . "{$name}:\n";
        }

        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $return .= static::formatArray($key, ArrayHelper::toArray($value), $tab + 1);
                } else {
                    $return .= str_repeat($tabString, $tab + 1) . "{$key}: ";
                    ob_start();
                    var_dump(htmlspecialchars($value));
                    $return .= ob_get_clean();
                }
            }
        }
        return $return;
    }

    /**
     *
     * @param type $string
     * @return type
     */
    public static function bbcodeParser($string)
    {
        /* @var $parser Parser */
        $parser = Instance::ensure(Parser::class);
        $parser->addCodeDefinitionSet(Instance::ensure(BBCodeDefinitionSet::class));

        $parser->parse($string);

        return $parser->getAsHTML();
    }
}
