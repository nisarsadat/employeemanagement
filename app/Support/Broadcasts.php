<?php

namespace App\Support;

use Illuminate\Broadcasting\BroadcastException;
use Throwable;

class Broadcasts
{
    /**
     * Send a realtime event without failing the HTTP request when the
     * Reverb process is down. The saved record is still returned.
     */
    public static function send(object $event, bool $toOthers = false): void
    {
        try {
            $pending = broadcast($event);

            if ($toOthers) {
                $pending->toOthers();
            }

            unset($pending);
        } catch (BroadcastException $e) {
            report($e);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
