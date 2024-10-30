<?php

namespace Coyote\Traits;

use DateTime;
use PAC_Vendor\Monolog\Handler\ErrorLogHandler;
use PAC_Vendor\Monolog\Logger;

trait LoggerTrait
{
    private static int $LOG_INFO = 10;
    private static int $LOG_WARNING = 20;
    private static int $LOG_ERROR = 30;
    private static int $LOG_DEBUG = 40;

    private static function getLogLevelName(int $level): string
    {
        return [
            self::$LOG_INFO => 'INFO',
            self::$LOG_WARNING => 'WARNING',
            self::$LOG_ERROR => 'ERROR',
            self::$LOG_DEBUG => 'DEBUG',
        ][$level];
    }

    private static function getMonologLevelName(int $level): string
    {
        return [
            self::$LOG_INFO => Logger::INFO,
            self::$LOG_WARNING => Logger::WARNING,
            self::$LOG_ERROR => Logger::ERROR,
            self::$LOG_DEBUG => Logger::DEBUG
        ][$level];
    }

    public static function logInfo(string $message, $data = []): void
    {
        self::log($message, $data, self::$LOG_INFO, get_called_class());
    }

    public static function logWarning(string $message, $data = []): void
    {
        self::log($message, $data, self::$LOG_WARNING, get_called_class());
    }

    public static function logDebug(string $message, $data = []): void
    {
        defined('COYOTE_API_CLIENT_DEBUG') &&
        COYOTE_API_CLIENT_DEBUG &&
        self::log($message, $data, self::$LOG_DEBUG, get_called_class());
    }

    public static function logError(string $message, $data = []): void
    {
        self::log($message, $data, self::$LOG_ERROR, get_called_class());
    }

    private static function log(string $message, array $payload, int $level, string $class): void
    {
        if (class_exists('PAC_Vendor\Monolog\Logger')) {
            $level = self::getMonologLevelName($level);
            self::logger($class)->log($level, $message, array_merge($payload, ['class' => $class]));
        } else {
            $level = self::getLogLevelName($level);
            $datetime = (new DateTime())->format('Y-m-d H:i:s');
            $serializedPayload = count($payload) ? print_r($payload, true) : '[]';
            error_log("[{$datetime}] {$class}.{$level}: \"{$message}\" {$serializedPayload}");
        }
    }

    private static function logger(string $class): Logger
    {
        $matches = [];
        preg_match('/\w+$/', $class, $matches);
        $logger = new Logger($matches[0]);
        $logger->pushHandler(new ErrorLogHandler());
        return $logger;
    }
}
