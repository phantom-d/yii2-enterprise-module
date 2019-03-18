<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\transport\components;

use yii\web\BadRequestHttpException;
use yii\web\RequestParserInterface;

/**
 * The request parser for xml
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class XmlParser extends \enterprise\Component implements RequestParserInterface
{
    /**
     * If parser result as array, this is default
     * @var boolean
     */
    public $asArray = true;

    /**
     * Whether throw the [[BadRequestHttpException]] if the process error.
     * @var boolean
     */
    public $throwException = true;

    /**
     * @inheritdoc
     */
    public function parse($rawBody, $contentType)
    {
        libxml_use_internal_errors(true);

        $result = simplexml_load_string($rawBody, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($result === false) {
            $errors = libxml_get_errors();
            $latestError = array_pop($errors);
            $error = [
                'message' => $latestError->message,
                'type'    => $latestError->level,
                'code'    => $latestError->code,
                'file'    => $latestError->file,
                'line'    => $latestError->line,
            ];
            if ($this->throwException) {
                throw new BadRequestHttpException($latestError->message);
            }
            return $error;
        }

        if (!$this->asArray) {
            return $result;
        }

        return json_decode(json_encode($result), true);
    }
}
