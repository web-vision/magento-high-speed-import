<?php

abstract class Fci_Objects_AbstractProcessor
{
    abstract public function pre(&$productArray, $profile);

    abstract public function post(Fci_Model_AbstractEntity $product, $profile);
}
