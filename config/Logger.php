<?php

class Logger
{
    private static ?string $logDir = null;

    private static function init(): void
    {
        if (self::$logDir === null) {
            self::$logDir = __DIR__ . '/../logs';
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0777, true);
            }
        }
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $logFile = self::$logDir . "/app-{$date}.log";

        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $formattedContext = '';
        if (!empty($context)) {
            $formattedContext = ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $logEntry = "[{$time}] [{$ip}] [{$uri}] [{$level}]: {$message}{$formattedContext}" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
}
