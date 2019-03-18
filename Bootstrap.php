<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise;

use enterprise\helpers\ArrayHelper;
use enterprise\helpers\StringHelper;
use yii\helpers\FileHelper;

/**
 * {@inheritdoc}
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * {@inheritdoc}
     * @param \yii\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $appPath = \Yii::getAlias('@app');
        $basePath = StringHelper::dirname($appPath);
        $currentApp = StringHelper::basename($appPath);

        \Yii::setAlias('@modules', $basePath . '/modules');

        $components = Module::coreComponents();
        if ($components = Module::prepareComponents($components, $app)) {
            $app->setComponents($components);
        }

        $modules = $this->initCoreModules($app) + $this->initModules($app);
        $app->setModules($modules);
    }

    /**
     * Recursive initialization of modules
     *
     * @param \yii\Application $app
     */
    private function initCoreModules($app)
    {
        $appPath = \Yii::getAlias('@app');
        $basePath = __DIR__ . '/modules';
        $currentApp = StringHelper::basename($appPath);
        $return = [];

        foreach (glob($basePath . '/*', GLOB_ONLYDIR) as $dir) {
            $id = StringHelper::basename($dir);
            $namespace = str_replace([$basePath, '/'], ['\enterprise', '\\'], $dir);

            if (is_file($dir . '/Module.php')) {
                \Yii::setAlias('enterprise/' . $id, $dir);
            }

            if (class_exists($namespace . '\Module')) {
                $return['base-' . $id]['class'] = $namespace . '\Module';
            }

            $class = $namespace . "\\{$currentApp}\Bootstrap";
            if (false === ArrayHelper::isIn($class, $app->bootstrap)) {
                $component = null;
                if (class_exists($class)) {
                    $component = \Yii::createObject($class);
                }
                if ($component instanceof BootstrapInterface) {
                    \Yii::trace('Bootstrap with ' . $class . '::bootstrap()', __METHOD__);
                    $component->bootstrap($app);
                } else {
                    \Yii::trace('Bootstrap with ' . $class, __METHOD__);
                }
            }
        }
        return $return;
    }

    /**
     * Recursive initialization of modules
     *
     * @param \yii\Application $app
     * @param string $prefix
     * @param string $pattern
     */
    private function initModules($app, $prefix = '', $pattern = '')
    {
        $appPath = \Yii::getAlias('@app');
        $basePath = StringHelper::dirname($appPath);
        $currentApp = StringHelper::basename($appPath);
        $return = [];
        $modulesPath = \Yii::getAlias('@modules');

        if (false === is_dir($modulesPath)) {
            FileHelper::createDirectory($modulesPath);
            FileHelper::copyDirectory(__DIR__ . '/docs/example', $modulesPath);
        }
        foreach (glob($modulesPath . '/' . $pattern . '*', GLOB_ONLYDIR) as $dir) {
            $id = StringHelper::basename($dir);
            if (file_exists($dir . '/' . 'modules')) {
                $module = StringHelper::basename($dir);
                $pattern = ($pattern ? $pattern : '') . $module . '/modules/';
                $return[$id] = [
                    'modules' => $this->initModules($app, $prefix . $id . '/', $pattern)
                ];
            }
            $namespace = str_replace([$basePath, '/'], ['', '\\'], $dir);

            if (class_exists($namespace . '\Module')) {
                $return[$id]['class'] = $namespace . '\Module';
                \Yii::setAlias($prefix . $id, $dir);
            }

            $class = $namespace . "\\{$currentApp}\Bootstrap";
            if (false === ArrayHelper::isIn($class, $app->bootstrap)) {
                $component = null;
                if (class_exists($class)) {
                    $component = \Yii::createObject($class);
                }
                if ($component instanceof BootstrapInterface) {
                    \Yii::trace('Bootstrap with ' . $class . '::bootstrap()', __METHOD__);
                    $component->bootstrap($app);
                } else {
                    \Yii::trace('Bootstrap with ' . $class, __METHOD__);
                }
            }
        }
        return $return;
    }
}
