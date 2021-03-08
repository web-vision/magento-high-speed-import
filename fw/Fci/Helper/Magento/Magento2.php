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
 * @package     Fci_Helper
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Magento_Magento2 extends Fci_Helper_Magento_AbstractMagento
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objManager;

    /**
     * @var array
     */
    protected static $_tables = [
        'websites'     => 'store_website',
        'stores'       => 'store',
        'store_groups' => 'store_group',
        'index'        => 'indexer_state',
    ];

    /**
     * @inheritDoc
     */
    public function getDatabaseConfig()
    {
        $envData = include dirname(ROOT_BASEDIR) . CDS . 'app' . CDS . 'etc' . CDS . 'env.php';

        return $envData['db']['connection']['default'];
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        include_once dirname(ROOT_BASEDIR) . CDS . 'app' . CDS . 'bootstrap.php';
        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);

        $this->_objManager = $bootstrap->getObjectManager();

        /** @var \Magento\Framework\App\State $state */
        $state = $this->_objManager->get('Magento\Framework\App\State');
        $state->setAreaCode('frontend');

        // remove magento error handler
        restore_error_handler();
    }

    /**
     * @inheritDoc
     */
    public function setLocale($storeCode)
    {
        $config = $this->_objManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $locale = $config->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );

        $localeInterface = $this->_objManager->create('Magento\Framework\Locale\ResolverInterface');

        $localeInterface->setLocale($locale);
        $localeInterface->setDefaultLocale($locale);
    }

    /**
     * @inheritDoc
     */
    public function setCurrentStore($storeCode)
    {
        $this->_objManager->create('\Magento\Store\Model\StoreManagerInterface')->setCurrentStore($storeCode);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeOptions($attributeSourceModel)
    {
        return $this->_objManager->create($attributeSourceModel)->getAllOptions();
    }

    /**
     * @inheritDoc
     */
    public function getTable($internalTableName)
    {
        return static::$_tables[$internalTableName];
    }

    /**
     * @inheritDoc
     */
    public function unload()
    {
        // unimplemented as Magento 2 has nothing comparable to Magento 1
    }

    /**
     * @inheritDoc
     */
    public function postProcessEntity(Fci_Model_AbstractEntity $entity)
    {
        if (!$entity instanceof Fci_Model_Product) {
            return;
        }

        $this->_moveGroupPriceToTierPrice($entity);
        $this->_cleanupTierPrice($entity);
    }

    /**
     * Moves group price data to tier price data as group price was removed in Magento 2.
     * Group price is now just a tier price with a qty of 1.
     *
     * @param \Fci_Model_Product $product
     */
    protected function _moveGroupPriceToTierPrice(Fci_Model_Product $product)
    {
        if (!$product->hasData('group_price')) {
            return;
        }

        $tierPrices = [];
        if ($product->hasData('tier_price')) {
            $tierPrices = $product->getData('tier_price');
        }

        /** @var array $groupPrices */
        $groupPrices = $product->getData('group_price');
        foreach ($groupPrices as $groupPrice) {
            $groupPrice['qty'] = 1;
            $tierPrices[] = $groupPrice;
        }

        $product->unsData('group_price');
        if ($tierPrices) {
            $product->setData('tier_price', $tierPrices);
        }
    }

    /**
     * If only one website is present the website id MUST be 0.
     *
     * @param \Fci_Model_Product $product
     */
    protected function _cleanupTierPrice(Fci_Model_Product $product)
    {
        if (!$product->hasData('tier_price')) {
            return;
        }

        $cache = Fci_Objects_Cache::getInstance();
        $websites = $cache->getData('websites');

        // admin + 1 FE website
        if (count($websites) > 2) {
            return;
        }

        $tierPrices = $product->getData('tier_price');
        foreach ($tierPrices as $index => $tierPrice) {
            $tierPrices[$index]['website'] = 0;
        }

        $product->setData('tier_price', $tierPrices);
    }

    /**
     * @inheritDoc
     */
    public function getIndexState()
    {
        return 'invalid';
    }

    /**
     * @inheritDoc
     */
    public function getMediaDirectory()
    {
        return realpath(ROOT_BASEDIR . '../pub/media');
    }
}
