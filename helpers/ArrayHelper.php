<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\helpers;

use yii\helpers\ArrayHelper as BaseArrayHelper;
use yii\helpers\Json;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\UnsetArrayValue;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * You can use [[UnsetArrayValue]] object to unset value from previous array or
     * [[ReplaceArrayValue]] to force replace former value instead of recursive merging.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function extendMerge($a, $b)
    {
        $args = func_get_args();
        $res  = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $key => $value) {
                if ($value instanceof UnsetArrayValue) {
                    unset($res[$key]);
                } elseif ($value instanceof ReplaceArrayValue) {
                    $res[$key] = $value->value;
                } elseif (is_int($key)) {
                    if (isset($res[$key])) {
                        $res[$key] = $value;
                    } else {
                        $res[] = $value;
                    }
                } elseif (is_array($value) && isset($res[$key]) && is_array($res[$key])) {
                    $res[$key] = self::merge($res[$key], $value);
                } else {
                    $res[$key] = $value;
                }
            }
        }

        return $res;
    }

    /**
     * Проверка наличия массива
     *
     * @param array|\Traversable $var
     */
    public static function arrayExist($var)
    {
        if (parent::isTraversable($var)) {
            foreach ($var as $value) {
                if (parent::isTraversable($value)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Замена точек в ключах массива
     *
     * @param string|array $param
     * @param bool $start
     * @return array|string
     */
    public static function dotRemover($param, $start = true)
    {
        $return = null;

        if (is_string($param)) {
            try {
                $data = Json::decode($param);
            } catch (\Exception $e) {
                return $param;
            }
        } else {
            $data = $param;
        }

        if ($data) {
            $result = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = static::dotRemover($value, false);
                } elseif (is_object($value)) {
                    $value = static::dotRemover(static::toArray($value), false);
                }
                $result[str_replace('.', '_', $key)] = $value;
            }
            if ($result) {
                $return = $result;
            }
        }

        return $start ? Json::encode($return) : $return;
    }
}
