<?php

/**
 * @copyright Copyright (c) 2018, Anton Ermolovich <anton.ermolovich@gmail.com>
 * @license http://www.yiiframework.com/license/
 */

namespace enterprise\helpers;

/**
 * Description of UrlValidator
 *
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 */
class UrlValidator extends \JBBCode\validators\UrlValidator
{

    /**
     * Returns true iff $input is a valid url.
     *
     * @param $input  the string to validate
     */
    public function validate($input)
    {
        $input = parse_url($input, PHP_URL_HOST) ?: "http://{$_SERVER['HTTP_HOST']}/" . $input;

        $valid = filter_var($input, FILTER_VALIDATE_URL);
        return !!$valid;
    }
}
