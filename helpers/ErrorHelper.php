<?php

namespace app\helpers;

use Yii;
use yii\log\Logger;

class ErrorHelper
{
    public static function handleError(\Exception $e): void
    {
        $errorMessage = "Error: " . $e->getMessage() . "\n" . "File: " . $e->getFile() . "\n" . "Line: " . $e->getLine();
        $logger = Yii::getLogger();
        $logger->log($errorMessage, Logger::LEVEL_ERROR, 'import-errors');
    }
}