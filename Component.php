<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise;

use yii\di\Instance;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class Component - Класс для работы с сервисным слоем (бизнес логика)
 *
 * @method string getId() Return component ID
 * @method string getComponentType() Return component type
 * @method string getComponentPath() Return component path
 * @method string getParentModule() Return parent module
 *
 * @property-read string $id Component ID
 * @property-read string $componentType Component type
 * @property-read string $componentPath Component path
 * @property-read array $componentConfig Component configuration
 * @property \enterprise\Module $module Parent module
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Component extends \yii\base\Component
{

    use \enterprise\traits\ModulesTrait;

    /**
     * Add singleton to container
     */
    const SINGLETON = false;

    /**
     * @var array Configuration
     */
    private $_config;

    /**
     * Return configuration
     *
     * @return array
     */
    public function getComponentConfig()
    {
        if (null === $this->_config) {
            if (__CLASS__ === get_class($this)) {
                return [];
            }
            $parentConfig = [];
            $parentClass = get_parent_class($this);
            $reflector = new \ReflectionClass($parentClass);
            if ($reflector->hasMethod(__FUNCTION__) && $reflector->isInstantiable()) {
                $component = Instance::ensure($parentClass);
                $parentConfig = $component->getComponentConfig();
            }
            $config = $this->module->moduleConfig[$this->getComponentPath()][$this->getId()] ?? [];
            $this->_config = helpers\ArrayHelper::merge($parentConfig, $config);
        }
        return $this->_config;
    }
}
