<?php

class Fci_Objects_Option
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Fci_Enum_OptionType
     */
    protected $type;

    /**
     * @var int
     */
    protected $required = 0;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var Fci_Enum_PriceType
     */
    protected $price_type;

    /**
     * @var string
     */
    protected $sku;

    /**
     * @var array
     */
    protected $additional;

    /**
     * @param array $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return array
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Fci_Enum_PriceType $price_type
     */
    public function setPriceType($price_type)
    {
        $this->price_type = $price_type;
    }

    /**
     * @return Fci_Enum_PriceType
     */
    public function getPriceType()
    {
        return $this->price_type;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = (int)$required;
    }

    /**
     * @return int
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Fci_Enum_OptionType $type
     */
    public function setType(Fci_Enum_OptionType $type)
    {
        $this->type = $type;
    }

    /**
     * @return Fci_Enum_OptionType
     */
    public function getType()
    {
        return $this->type;
    }
}
