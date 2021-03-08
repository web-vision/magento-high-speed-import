<?php
/**
 * This class represents an observable object, or "data"
 * in the model-view paradigm. It can be subclassed to represent an
 * object that the application wants to have observed.
 * 
 * An observable object can have one or more observers. An observer
 * may be any object that implements interface Observer. After an
 * observable instance changes, an application calling the
 * Observable's notifyObservers method
 * causes all of its observers to be notified of the change by a call
 * to their update method.
 * 
 * The order in which notifications will be delivered is unspecified.
 * The default implementation provided in the Observable class will
 * notify Observers in the order in which they registered interest, but
 * subclasses may change this order, use no guaranteed order, deliver
 * notifications on separate threads, or may guarantee that their
 * subclass follows this order, as they choose.
 * 
 * Note that this notification mechanism is has nothing to do with threads
 * and is completely separate from the wait and notify
 * mechanism of class Object.
 * 
 * When an observable object is newly created, its set of observers is
 * empty. Two observers are considered the same if and only if the
 * equals method returns true for them.
 *
 * @author  Chris Warth
 * @version 1.39, 11/17/05
 */
class Cemes_Pattern_Observable {
    /**
     * @var bool
     */
    private $changed = false;
    /**
     * @var array
     */
    private $obs = array();

    /**
     * Adds an observer to the set of observers for this object, provided
     * that it is not the same as some observer already in the set.
     * The order in which notifications will be delivered to multiple
     * observers is not specified. See the class comment.
     *
     * @param Cemes_Pattern_Observer $o an observer to be added.
     * @throws NullPointerException if the parameter o is null.
     */
    public function addObserver(Cemes_Pattern_Observer &$o) {
        if ($o == null)
            throw new NullPointerException();
        if (!in_array($o, $this->obs)) {
            $this->obs[] = $o;
        }
    }

    /**
     * Deletes an observer from the set of observers of this object.
     * Passing null to this method will have no effect.
     *
     * @param Cemes_Pattern_Observer $o the observer to be deleted.
     */
    public function deleteObserver(Cemes_Pattern_Observer $o) {
        $index = array_search($o, $this->obs);
        if($index)
            unset($this->obs[$index]);
    }

    /**
     * If this object has changed, as indicated by the
     * hasChanged method, then notify all of its observers
     * and then call the clearChanged method to indicate
     * that this object has no longer changed.
     *
     * Each observer has its update method called with two
     * arguments: this observable object and the arg argument.
     *
     * @param Object $arg any Object.
     * @return mixed
     */
    public function notifyObservers($arg = null) {
        if (!$this->changed)
            return;
        $arrLocal = $this->obs;
        $this->clearChanged();

        for ($i = count($arrLocal)-1; $i>=0; $i--)
            $arrLocal[$i]->update($this, $arg);
    }

    /**
     * Clears the observer list so that this object no longer has any observers.
     */
    public function deleteObservers() {
	    $this->obs = array();
    }

    /**
     * Marks this Observable object as having been changed; the
     * hasChanged method will now return true.
     */
    protected function setChanged() {
	    $this->changed = true;
    }

    /**
     * Indicates that this object has no longer changed, or that it has
     * already notified all of its observers of its most recent change,
     * so that the hasChanged method will now return false.
     * This method is called automatically by the
     * notifyObservers methods.
     */
    protected function clearChanged() {
	    $this->changed = false;
    }

    /**
     * Tests if this object has changed.
     *
     * @return bool true if and only if the setChanged
     *          method has been called more recently than the
     *          clearChanged method on this object;
     *          false otherwise.
     */
    public function hasChanged() {
	    return changed;
    }

    /**
     * Returns the number of observers of this Observable object.
     *
     * @return int the number of observers of this object.
     */
    public function countObservers() {
	    return count($this->obs);
    }
}
