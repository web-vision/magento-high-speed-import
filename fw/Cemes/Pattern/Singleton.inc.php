<?php

abstract class Cemes_Pattern_Singleton
{
    /**
     * Prototyp der Methode zum erzeugen oder zurückgeben der Instanz.
     *
     * @return $this
     */
    public static function getInstance()
    {
        $className = get_called_class();
        if ($className::$instance === null) {
            $className::$instance = new $className();
        }

        return $className::$instance;
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }
}
