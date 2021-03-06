<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\components;

use yii\helpers\VarDumper;
use yii\log\Logger;

/**
 * FileTarget records log messages in a file. Overided.
 */
class FileTarget extends \yii\log\FileTarget
{
    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in Logger::messages.
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Exception) {
                $text = (string)$text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $micro = sprintf('%06d', ($timestamp - floor($timestamp)) * 1000000);
        $prefix = $this->getMessagePrefix($message);
        return date('Y-m-d H:i:s', $timestamp)
          . '.' . ($micro ? str_pad($micro, 6, '0') : '000000')
          . " {$prefix}[$level][$category] $text"
          . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }
}
