<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\traits;

use enterprise\helpers\StringHelper;
use yii\helpers\Inflector;

/**
 * List of special parameters
 *
 * @property string $id Component id
 * @property string $componentType Component type
 * @property string $componentPath Component path
 * @property string $shortName Short class name without namespace
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
trait ModulesTrait
{

    /**
     * @var \enterprise\Module|\yii\web\Application|\yii\console\Application Parent module
     */
    public $module;

    /**
     * @var string Component id
     */
    private $_id;

    /**
     * @var string Component type
     */
    private $_type;

    /**
     * @var string Component path
     */
    private $_path;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();
        if (empty($this->module)) {
            $this->module = $this->getParentModule();
        }
    }

    /**
     * Get component id
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->_id) {
            $type = $this->getComponentType();
            $suffix = isset($this->{$type}) ? $this->{$type}->suffix : '';
            $this->_id = Inflector::camel2id(
                StringHelper::basename(get_called_class(), $suffix)
            );
        }
        return $this->_id;
    }

    /**
     * Get component type
     *
     * @return string
     */
    public function getComponentType()
    {
        if (null === $this->_type) {
            $search = explode('\\', $this->getComponentPath());
            foreach ($search as $containerId) {
                if (isset($this->{$containerId})) {
                    $this->_type = $containerId;
                    break;
                }
            }
        }
        return $this->_type;
    }

    /**
     * Get component path
     *
     * @return string
     */
    public function getComponentPath()
    {
        if (null === $this->_path) {
            $class = get_called_class();
            $modulePath = StringHelper::dirname(get_class($this->module));
            $class = str_replace($modulePath, '', $class);
            $this->_path = StringHelper::dirname(trim($class, '\\'));
        }
        return $this->_path;
    }

    /**
     * Parent module
     *
     * @return \enterprise\Module|\yii\web\Application|\yii\console\Application
     */
    public function getParentModule()
    {
        /* @var $return \enterprise\Module|\yii\web\Application|\yii\console\Application */
        $return = \Yii::$app;
        $calledClass = preg_split('/\\\/', get_called_class(), -1, PREG_SPLIT_NO_EMPTY);
        $first = (int)('modules' === $calledClass[0]);
        $decrement = (int)('Module' !== array_pop($calledClass));
        $calledClass = array_slice($calledClass, $first, count($calledClass) - $decrement);
        if ($first) {
            foreach ($calledClass as $id) {
                if ($return->hasModule($id)) {
                    $return = $return->getModule($id);
                }
            }
        } else {
            foreach (\Yii::$app->getModules() as $module) {
                $tempClass = $calledClass;
                if (is_object($module)) {
                    $nameSpace = StringHelper::dirname(get_class($module));
                    while ($tempClass) {
                        $classNameSpace = implode('\\', $tempClass);
                        if ($classNameSpace === $nameSpace) {
                            $return = $module;
                            break 2;
                        }
                        array_pop($tempClass);
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Return short class name without namespace
     *
     * @return string
     */
    public function getShortName()
    {
        return StringHelper::basename(get_called_class());
    }

    /**
     * Getter magic method.
     * This method is overridden to support accessing components like reading properties.
     *
     * @param string $name component or property name
     * @return mixed the named property value
     */
    public function __get($name)
    {
        if ($this instanceof \yii\db\ActiveRecordInterface) {
            return parent::__get($name);
        }

        if ($this->module->has($name)) {
            return $this->module->get($name);
        }

        return parent::__get($name);
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the named component is loaded.
     * @param string $name the property name or the event name
     * @return bool whether the property value is null
     */
    public function __isset($name)
    {
        if ($this instanceof \yii\db\ActiveRecordInterface) {
            return parent::__isset($name);
        }

        if ($this->module->has($name)) {
            return true;
        }

        return parent::__isset($name);
    }
}
