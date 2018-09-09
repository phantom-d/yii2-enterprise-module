<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\helpers;

use bupy7\bbcode\definitions\DefaultCodeDefinitionSet;
use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use yii\di\Instance;
use yii\validators\UrlValidator;

/**
 * Description of DefaultCodeDefinitionSet
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class BBCodeDefinitionSet extends DefaultCodeDefinitionSet
{

    public function __construct()
    {
        parent::__construct();

        foreach ($this->definitions as $key => $value) {
            /* @var $value CodeDefinition */
            if (in_array($value->getTagName(), ['url', 'img'])) {
                unset($this->definitions[$key]);
            }
        }

        $urlValidator = Instance::ensure(UrlValidator::class);

        // [url] link tag
        $builder = new CodeDefinitionBuilder('url', '<a href="{param}">{param}</a>');
        $builder->setParseContent(false)->setBodyValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        // [url=http://example.com] link tag
        $builder = new CodeDefinitionBuilder('url', '<a href="{option}">{param}</a>');
        $builder->setUseOption(true)->setParseContent(true)->setOptionValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        // [img] image tag
        $builder = new CodeDefinitionBuilder('img', '<img src="{param}" />');
        $builder->setUseOption(false)->setParseContent(false)->setBodyValidator($urlValidator);
        array_push($this->definitions, $builder->build());

        // [img=alt text] image tag
        $builder = new CodeDefinitionBuilder('img', '<img src="{param}" alt="{option}" />');
        $builder->setUseOption(true)->setParseContent(false)->setBodyValidator($urlValidator);
        array_push($this->definitions, $builder->build());
    }
}
