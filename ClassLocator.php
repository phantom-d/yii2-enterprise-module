<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise;

use yii\base\Component;
use yii\UnknownClassException;
use yii\UnknownMethodException;
use yii\di\Instance;
use yii\helpers\Inflector;

/**
 * Abstract class ClassLocator - Base class for working with module layers
 *
 * @property boolean $strict Строгий контроль получения объекта
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class ClassLocator extends Component
{

    /**
     * @var string Component ID
     */
    public $id = '';

    /**
     * @var string Default namespace
     */
    public $defaultNamespace;

    /**
     * @var string Current namespace
     */
    public $namespace;

    /**
     * @var \enterprise\Module|\yii\web\Application|\yii\console\Application Parent module
     */
    public $module;

    /**
     * @var boolean Search class throw parent module
     */
    public $throwParents = true;

    /**
     * @var boolean Passing parameters through the constructor
     */
    public $constructor = false;

    /**
     * @var string Suffix for class name
     */
    public $suffix = '';

    /**
     * @var boolean Get object with exception
     */
    private $_strict = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->defaultNamespace === null) {
            $this->defaultNamespace = 'common\\';
            if (false === ($this->module instanceof \yii\Application)) {
                $namespace = helpers\StringHelper::dirname($this->module->className());
                $offset = ($this->module instanceof \yii\Application) ? mb_stripos($namespace, '\\') + 1 : 0;
                $this->defaultNamespace = mb_substr($namespace, $offset) . '\\';
            }
            $this->defaultNamespace .= $this->id;
        }
        if ($this->namespace === null) {
            $namespace = helpers\StringHelper::dirname($this->module->className());
            $offset = ($this->module instanceof \yii\Application) ? mb_stripos($namespace, '\\') + 1 : 0;
            $this->namespace = mb_substr($namespace, $offset) . '\\';

            if (false === ($this->module instanceof \yii\Application)) {
                $appPath = explode('/', \Yii::getAlias('@app'));
                $this->namespace .= end($appPath) . '\\';
            }
            $this->namespace .= $this->id;
        }
        parent::init();
    }

    /**
     * Get module service/model/component object
     *
     * @param string $name Class name
     * @param array $params Class parameters
     * @param boolean $strict Strict mode
     * @param boolean $parent Throw parent modules
     *
     * @return \yii\Object
     *
     * @throws UnknownClassException
     */
    public function getObject($name, $params = [], $strict = null, $parent = null)
    {
        if (isset($strict)) {
            $this->_strict = boolval($strict);
        }
        if (isset($parent)) {
            $this->throwParents = boolval($parent);
        }
        $name = trim($name, '\\');
        try {
            $prefix = '';
            if (mb_strpos($name, '\\')) {
                $array = explode('\\', $name);
                $name = array_pop($array);
                $prefix = implode('\\', $array) . '\\';
            }
            $className = '\\' . $prefix . Inflector::id2camel($name, '_') . strval($this->suffix);
            $class = $this->namespace . $className;

            if (false === class_exists($class)) {
                $class = $this->defaultNamespace . $className;
            }

            /* @var $class \enterprise\Component */
            if (class_exists($class)) {
                $classSingleton = defined("{$class}::SINGLETON") ? $class::SINGLETON : false;
                $singleton = $params['__singleton'] ?? $classSingleton;
                if ($singleton) {
                    if (\Yii::$container->hasSingleton($class)) {
                        $clone = $params['__clone'] ?? false;
                        $return = \Yii::$container->get($class);
                        return $clone ? clone $return : $return;
                    }
                }
                if (array_key_exists('__clone', $params)) {
                    unset($params['__clone']);
                }
                if (array_key_exists('__singleton', $params)) {
                    unset($params['__singleton']);
                }
                if ((boolean)$this->constructor) {
                    $reflector = new \ReflectionClass($class);
                    /* @var $parameters \ReflectionParameter */
                    $parameters = $reflector->getMethod('__construct')->getParameters();
                    $args = [];
                    if (1 === count($parameters)) {
                        $args[] = $params;
                    } else {
                        foreach ($parameters as $parameter) {
                            $parameterName = $parameter->getName();
                            $args[$parameterName] = null;
                            if (isset($params[$parameterName])) {
                                $args[$parameterName] = $params[$parameterName];
                            } elseif ($parameter->isOptional()) {
                                $args[$parameterName] = $parameter->getDefaultValue();
                            }
                        }
                    }
                    $return = $reflector->newInstanceArgs(array_values($args));
                    if ($reflector->hasProperty('module')) {
                        $return->module = &$this->module;
                    }
                    if ($singleton) {
                        \Yii::$container->setSingleton($class, $return);
                    }
                    return $return;
                }
                $properties = get_class_vars($class);
                if (array_key_exists('module', $properties) && false === isset($params['module'])) {
                    $params['module'] = &$this->module;
                }
                $return = Instance::ensure($params, $class);
                if ($singleton) {
                    \Yii::$container->setSingleton($class, $return);
                }
                return $return;
            }

            if ($this->_strict) {
                if (YII_ENV_DEV) {
                    $message = \Yii::t('yii', 'Calling unknown class: {class}', ['class' => $class]);
                } else {
                    $message = \Yii::t('yii', 'Internal server error');
                }
                throw new UnknownClassException($message);
            } else {
                $this->_strict = true;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $params)
    {
        try {
            $parts = explode('-', Inflector::camel2id($name));

            $names = [];
            $object = null;

            while (count($parts)) {
                $lastPart = array_pop($parts);
                array_unshift($names, $lastPart);

                $classParts = Inflector::id2camel(implode('-', $parts));

                $class = ucfirst($classParts);
                $this->_strict = false;
                if ($object = $this->getObject($class)) {
                    break;
                }
            }

            if (empty($object)) {
                if (YII_ENV_DEV) {
                    $message = \Yii::t('yii', 'Not found model class: {class}', ['class' => $name]);
                } else {
                    $message = \Yii::t('yii', 'Internal server error');
                }
                throw new UnknownClassException($message);
            }

            $methodParts = Inflector::id2camel(implode('-', $names));

            $method = lcfirst($methodParts);

            if ($method && method_exists($object, $method)) {
                return call_user_func_array([$object, $method], $params);
            }

            if ($this->throwParents && $this->module && $this->module->module) {
                return call_user_func_array([$this->module->module->{$this->id}, $name], $params);
            }

            if (YII_ENV_DEV) {
                $message = \Yii::t(
                    'yii', //
                    'Calling unknown method: {class}::{method}', //
                    ['class' => $object->className(), 'method' => $method,]
                );
            } else {
                $message = \Yii::t('yii', 'Internal server error');
            }

            throw new UnknownMethodException($message);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($object = $this->getObject($name, [], false)) {
            return $object;
        }
        if ($this->throwParents && $this->module && $this->module->module) {
            return $this->module->module->{$this->id}->{$name};
        }
        return parent::__get($name);
    }
}
