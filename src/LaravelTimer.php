<?php

namespace Astatroth\LaravelTimer;

use Log;
use Request;

class LaravelTimer
{

    protected $timers;

    /**
     * Sets the timer with the specified name.
     *
     * @param $name
     */
    public function start($name)
    {
        $this->timers[$name]['start'] = microtime(true);
        $this->timers[$name]['count'] = isset($this->timers[$name]['count']) ? ++$this->timers[$name]['count'] : 1;
    }

    /**
     * Reads the timer current time without stopping it.
     *
     * @param $name
     * @return float
     */
    public function read($name)
    {
        if (isset($this->timers[$name]['start'])) {
            $stop = microtime(true);
            $diff = round(($stop - $this->timers[$name]['start']) * 1000, 2);

            if (isset($this->timers[$name]['time'])) {
                $diff += $this->timers[$name]['time'];
            }

            return $diff;
        }

        return $this->timers[$name]['time'];
    }

    public function stop($name)
    {
        if (isset($this->timers[$name]['start'])) {
            $stop = microtime(true);
            $diff = round(($stop - $this->timers[$name]['start']) * 1000, 2);

            if (isset($this->timers[$name]['time'])) {
                $this->timers[$name]['time'] += $diff;
            } else {
                $this->timers[$name]['time']  = $diff;
            }

            unset($this->timers[$name]['start']);
        }

        return $this->timers[$name];
    }

    public function startAndLog($name, $includeRequestSignature = true)
    {
        $executionTime  = $this->start($name);

        $message = $this->getRequestSignature() . ' ' . microtime(true) . ' ' . $name . ' Started' . $executionTime;


        Log::info($message);
    }

    public function stopAndLog($name, $includeRequestSignature = true)
    {
        $executionTime  = $this->stop($name)['time'];

        $message = $this->getRequestSignature() . ' ' . microtime(true) . ' ' . $name . ' Execution Time(sec):' . $executionTime;


        Log::info($message);
    }

    public function getRequestSignature()
    {
        $str = $_SERVER['HTTP_USER_AGENT'] ?? '' . \Request::ip() . \Request::route()->uri() . \Request::all();
        $signature = hash_hmac('sha256', $str, config('app.key'));

        return $signature;
    }
}
