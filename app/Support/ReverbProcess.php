<?php

namespace App\Support;

class ReverbProcess
{
    /**
     * Start the websocket server from the web request when it is not already
     * listening. Herd and XAMPP can serve the site without a terminal command.
     */
    public static function ensureRunning(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $port = (int) config('reverb.servers.reverb.port', 6001);
        if (self::portOpen($port)) {
            return;
        }

        $lockPath = storage_path('framework/reverb-start.lock');
        $lock = @fopen($lockPath, 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }

            return;
        }

        try {
            if (! self::portOpen($port)) {
                self::start($port);
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private static function portOpen(int $port): bool
    {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);
        if (! is_resource($socket)) {
            return false;
        }

        fclose($socket);

        return true;
    }

    private static function start(int $port): void
    {
        if (! function_exists('exec') && ! function_exists('popen')) {
            return;
        }

        $php = self::phpBinary();
        $artisan = base_path('artisan');
        $log = storage_path('logs/reverb.log');

        if (PHP_OS_FAMILY === 'Windows') {
            if (! function_exists('popen')) {
                return;
            }

            $command = 'start /B "" '.self::quote($php).' '.self::quote($artisan).' reverb:start';
            $handle = @popen('cmd /c '.$command.' >> '.self::quote($log).' 2>&1', 'r');
            if (is_resource($handle)) {
                pclose($handle);
            }

            return;
        }

        if (! function_exists('exec')) {
            return;
        }

        $command = self::quote($php).' '.self::quote($artisan).' reverb:start >> '.self::quote($log).' 2>&1 &';
        @exec($command);
    }

    private static function phpBinary(): string
    {
        $binary = PHP_BINARY;

        if (preg_match('/fpm|cgi|phpdbg/i', $binary)) {
            $directory = dirname($binary);
            foreach ([$directory.DIRECTORY_SEPARATOR.'php', $directory.DIRECTORY_SEPARATOR.'php.exe'] as $candidate) {
                if (is_file($candidate)) {
                    return $candidate;
                }
            }

            return 'php';
        }

        return $binary;
    }

    private static function quote(string $value): string
    {
        return '"'.str_replace('"', '\"', $value).'"';
    }
}
