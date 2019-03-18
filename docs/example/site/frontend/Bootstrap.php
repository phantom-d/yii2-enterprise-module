<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace modules\site\frontend;

/**
 * {@inheritdoc}
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * {@inheritdoc}
     * @param \yii\web\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            '/' => 'site/default/index',
        ]);
    }
}
