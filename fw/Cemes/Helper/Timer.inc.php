<?php
if (!$GLOBALS['CEMES']['ACTIVE'])
    die('Framework ist nicht aktiv');
/*
 * Singleton-Klasse
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 *
 * Cemes_Config liest diverse Config dateien und kann sie auch beschreiben
 */

class Cemes_Helper_Timer {
    /**
     * is timer already running? if not hold the start time
     *
     * @var boolean
     */
    private $_running = false;

    /**
     * saves the elapsed time
     *
     * @var float
     */
    private $_elapsed = 0.;

    /**
     * function to start the timer
     *
     * @return void
     */
    public function start() {
        if($this->_running !== false) {
            trigger_error('Timer has already been started', E_USER_NOTICE);
            return false;
        }
        else
            $this->_running = microtime(true);
    }

    /**
     * function to stop the timer
     *
     * @return void
     */
    public function stop() {
        if($this->_running === false) {
            trigger_error('Timer has already been stopped/paused or has not been started', E_USER_NOTICE);
            return false;
        } else {
            $this->_elapsed += microtime(true) - $this->_running;
            $this->_running = false;
        }
    }

    /**
     * reset the timer
     *
     * @return void
     */
    public function reset() {
        $this->_elapsed = 0.;
    }

    /**
     * function to get the summed time in human readable format
     *
     * @value int
     * @return int|float
     */
    public function get() {
        // stop timer if it is still running
        if($this->_running !== false) {
            trigger_error('Forcing timer to stop', E_USER_NOTICE);
            $this->stop();
        }

        list($s, $ms) = explode('.', $this->_elapsed);
        $time = '0.'.$ms;
        if($s != 0) {
            $m = (int)($s / 60);
            $time = $s.'.'.$ms;
        }
        if($m != 0) {
            $s -= $m * 60;
            $h = (int)($m / 60);
            $time = $m.':'.$s.'.'.$ms;
        }
        if($h != 0) {
            $m -= $h * 60;
            $time = $h.':'.$m.':'.$s.'.'.$ms;
        }

        return $time;
    }

    /**
     * The elapsed time in milliseconds.
     *
     * @return float
     */
    public function getElapsed()
    {
        return $this->_elapsed;
    }
}
