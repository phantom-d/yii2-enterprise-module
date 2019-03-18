<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\traits;

use enterprise\components\Response as BaseResponse;
use yii\rest\Serializer;
use yii\web\Response;

/**
 * List of special parameters
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
trait RestTrait
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->serializer = [
            'class'              => Serializer::class,
            'collectionEnvelope' => 'items',
        ];

        \Yii::$app->response->format = (\Yii::$app->response instanceof BaseResponse) ?
            BaseResponse::FORMAT_ENTERPRISE :
            Response::FORMAT_JSON;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if (\Yii::$app->response instanceof BaseResponse) {
            $behaviors['contentNegotiator']['formats']['application/json'] = BaseResponse::FORMAT_ENTERPRISE;
        } else {
            $behaviors['contentNegotiator']['formats']['application/json'] = \yii\web\Response::FORMAT_JSON;
        }

        return $behaviors;
    }
}
