<?php
/**
 * A class can implement the Observer interface when it
 * wants to be informed of changes in observable objects.
 *
 * @author  Chris Warth
 * @version 1.20, 11/17/05
 */
interface Cemes_Pattern_Observer {
    /**
     * This method is called whenever the observed object is changed. An
     * application calls an Observable object's
     * notifyObservers method to have all the object's
     * observers notified of the change.
     *
     * @abstract
     * @param Cemes_Pattern_Observable $o the observable object.
     * @param String $arg an argument passed to the notifyObservers method.
     */
    function update(Cemes_Pattern_Observable $o, $arg);
}
