<?php

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @package     Fci_Model
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
abstract class Fci_Model_AbstractEntity extends Cemes_Object
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Returns the entity id.
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->getData($this->_idFieldName);
    }

    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setData($this->getIdFieldName(), (int)$id);

        return $this;
    }

    /**
     * Returns the resource of this entity.
     *
     * @return Fci_Model_Resource_AbstractResource
     */
    public abstract function getResource();

    /**
     * Returns the resource for attributes.
     *
     * @return Fci_Model_Resource_AbstractResource
     */
    public abstract function getAttributeResource();

    /**
     * Saves the category.
     *
     * @return $this
     */
    public function save()
    {
        if ($this->isNew()) {
            $this->getResource()->insert($this);
        } else {
            $this->getResource()->update($this);
        }

        return $this;
    }
}
