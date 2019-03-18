<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise;

/**
 * Class Module - Base class for working with users modules
 *
 * Module configuration placed on several files (sorted by file merging):
 * - `{Module root dir}/config/main.php`
 * - `\Yii::$app->params['modules'][{Module ID}]`
 *   - `@common/config/params{-local}.php`
 *   - `@app/config/params{-local}.php`
 *
 * @property-read array $moduleConfig Module configuration
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Module extends \yii\base\Module
{
    /**
     * @var array Module containers
     */
    public $containers = [];

    /**
     * @var array Addition components
     */
    protected $addComponents = [];

    /**
     * @var array Module configuration
     */
    private $_config;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $appPath = substr(strrchr(\Yii::getAlias('@app'), '/'), 1);
        if ($this->controllerNamespace === null) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr($class, 0, $pos)
                    . '\\' . $appPath
                    . '\\controllers';
            }
        }

        $viewPath = $this->viewPath . DIRECTORY_SEPARATOR . $appPath;

        if (isset(\Yii::$app->view->theme)) {
            $viewPath = $this->getBasePath();
        }

        $this->setViewPath($viewPath);

        $containers = helpers\ArrayHelper::merge(self::coreComponents(), $this->containers);
        if ($components = self::prepareComponents($containers, $this)) {
            parent::setComponents($components);
        }
        if ($this->addComponents) {
            \Yii::$app->setComponents($this->addComponents);
        }
    }

    /**
     * Return configuration.
     *
     * @return array
     */
    public function getModuleConfig()
    {
        if (null === $this->_config) {
            if (is_file($this->basePath . '/config/main.php')) {
                $this->_config = require_once $this->basePath . '/config/main.php';
            }
            $params = \Yii::$app->params['modules'][$this->id] ?? [];
            $this->_config = helpers\ArrayHelper::merge($this->_config, $params);
        }
        return $this->_config;
    }

    /**
     * Prepare components for module
     *
     * @param array $containers
     * @param \yii\Module $module
     * @return array
     */
    public static function prepareComponents($containers, $module)
    {
        $return = [];
        foreach ($containers as $id => $params) {
            if (is_array($params)) {
                $params['class'] = Container::class;
                $params['module'] = &$module;
                $params['id'] = $id;
            } else {
                $id = $params;
                $params = [
                    'class'  => Container::class,
                    'module' => &$module,
                    'id'     => $id,
                ];
            }
            $return[$id] = $params;
        }
        return $return;
    }

    /**
     * Module core components
     *
     * @return array
     */
    public static function coreComponents()
    {
        return [
            'components',
            'models',
            'services' => [
                'suffix' => 'Service',
            ],
        ];
    }

    /**
     * Add target to Logger
     *
     * <table cellspacing="0">
     *     <tr><td>string</td><td><b>$params['name']</b></td><td>file name and selector of target</td></tr>
     *     <tr><td>string</td><td><b>$params['dir']</b></td><td>directory for save log files</td></tr>
     *     <tr><td>string</td><td><b>$params['class']</b></td><td>target class name with namespace</td></tr>
     *     <tr><td>array</td><td><b>$params['levels']</b></td><td>log levels</td></tr>
     *     <tr><td>array</td><td><b>$params['logVars']</b></td><td>global variables</td></tr>
     *     <tr><td>string</td><td><b>$params['prefix']</b></td><td>a PHP callable that returns a string to be prefixed
     * to every exported message.</td></tr>
     *     <tr><td>integer</td><td><b>$params['exportInterval']</b></td><td>how many messages should be accumulated
     * before they are exported</td></tr>
     *     <tr><td>array</td><td><b>$params['except']</b></td><td>exceptions logging application</td></tr>
     *     <tr><td>boolean</td><td><b>$params['dateTime']</b></td><td>add datetaime to filename</td></tr>
     *     <tr><td>boolean</td><td><b>$params['onlyOne']</b></td><td>file name and selector of target</td></tr>
     * </table>
     *
     * @param array $params Log target params (See above)
     * @throws \yii\InvalidParamException
     */
    public function componentsLogger($params)
    {
        $errors = [];
        if (false === isset($params['name']) || '' === strval($params['name'])) {
            $errors[] = '$params["name"]';
        }

        if ($errors) {
            $message = 'Missing required arguments: {params}';
            throw new \yii\InvalidParamException(\Yii::t('yii', $message, ['params' => implode(', ', $errors)]));
        }

        $dateTime = false;
        $dirName = 'logs/' . $this->id;
        $name = $params['name'];
        $onlyOne = false;

        unset($params['name']);

        if (isset($params['dir']) || array_key_exists('dir', $params)) {
            if ('' !== strval($params['dir'])) {
                $dirName = DIRECTORY_SEPARATOR . strval($params['dir']);
            }
            unset($params['dir']);
        }

        if (isset($params['dateTime']) || array_key_exists('dateTime', $params)) {
            $dateTime = boolval($params['dateTime']);
            unset($params['dateTime']);
        }

        if (isset($params['onlyOne']) || array_key_exists('onlyOne', $params)) {
            $onlyOne = boolval($params['onlyOne']);
            unset($params['onlyOne']);
        }

        if ((isset($params['class']) || array_key_exists('class', $params)) &&
            false === class_exists($params['class'])
        ) {
            unset($params['class']);
        }

        if ((isset($params['prefix']) || array_key_exists('prefix', $params)) &&
            false === is_callable($params['prefix'])
        ) {
            unset($params['prefix']);
        }

        if (empty($params['levels'])) {
            $params['levels'] = ['error', 'warning', 'info', 'trace',];
            if (YII_DEBUG) {
                $params['levels'] += ['profile'];
            }
        }
        if (empty($params['except'])) {
            $params['except'] = ['yii\db\*'];
        }
        if (empty($params['logVars'])) {
            $params['logVars'] = [];
        }
        if (empty($params['exportInterval'])) {
            $params['exportInterval'] = 1;
        }

        $targets = \Yii::$app->getLog()->targets;
        if ($onlyOne) {
            foreach ($targets as $target) {
                $target->enabled = false;
            }
        }

        $config = [
            'class'  => components\FileTarget::class,
            'prefix' => function () {
                return '';
            },
        ];

        $target = \Yii::createObject(helpers\ArrayHelper::merge($config, $params));
        if (isset($target->logFile)) {
            $date = date('Y-m-d');
            $target->logFile = \Yii::$app->getRuntimePath()
                . DIRECTORY_SEPARATOR . trim($dirName, '/')
                . DIRECTORY_SEPARATOR . $date
                . DIRECTORY_SEPARATOR . strval($name) . '_'
                . (boolval($dateTime) ? date('Y-m-d_H-i-s') : $date)
                . '.log';
            $target->init();
        }

        $targets[$name] = $target;
        \Yii::$app->getLog()->targets = $targets;
        \Yii::$app->getLog()->init();
    }
}
