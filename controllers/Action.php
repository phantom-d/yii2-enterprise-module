<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\controllers;

use yii\helpers\Inflector;

/**
 * Class Action - Базовое действие для контроллеров
 *
 * @property boolean $enableCsrfValidation Whether to enable CSRF validation for this action.<br/><br/>
 *                                         <b>Must be added to action class to use!</b><br/>
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
abstract class Action extends \yii\base\Action implements ActionModuleInterface
{
    /**
     * @var \enterprise\Module Текущий модуль
     */
    protected $module;

    /**
     * @var \enterprise\Service Сервис для работы с контроллером
     */
    protected $page;

    /**
     * @var bool Режим запуска только одного процесса одновременно
     */
    protected $singleMode = false;

    /**
     * @var boolen Use action arguments in process name
     */
    protected $processNameArgs = false;

    /**
     * @var boolen Write alert to log about running process
     */
    protected $processAlert = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (isset($this->enableCsrfValidation)) {
            $this->controller->enableCsrfValidation = boolval($this->enableCsrfValidation);
        }

        parent::init();

        if (null === $this->module) {
            $this->module = &$this->controller->module;
        }

        if (empty($this->page)) {
            $this->page = Inflector::id2camel("{$this->controller->id}-{$this->id}");
        }

        $this->page = $this->module
            ->services->getObject('pages\\' . $this->page, [], true);
    }

    /**
     * @return boolean
     */
    public function getSingleMode()
    {
        return boolval($this->singleMode);
    }

    /**
     * @return boolean
     */
    public function getProcessNameArgs()
    {
        return boolval($this->processNameArgs);
    }

    /**
     * @return boolean
     */
    public function getProcessAlert()
    {
        return boolval($this->processAlert);
    }
}
