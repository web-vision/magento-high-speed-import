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
 * @package     Fci_Helper_Factory
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Factory_ProductFactory extends Fci_Helper_Factory_AbstractFactory
{
    /**
     * Creates a product with the given data.
     *
     * @param array $data
     * @param int   $entityId
     *
     * @return Fci_Model_AbstractEntity
     */
    public function createProduct($data, $entityId)
    {
        $product = new Fci_Model_Product([$entityId]);

        $data = $this->_beforeNormalization($product, $data);

        $attrHelper = $this->_getAttributeHelper($product);
        foreach ($data as $attributeCode => $value) {
            $attribute = $this->_cache->getProductAttribute($attributeCode);
            if (!$attribute->isEmpty()) {
                $attrHelper->setAttribute($attribute);
                $value = $attrHelper->normalizeValue($value);

                // don't add value that couldn't be normalized
                if ($value === null) {
                    continue;
                }

                // 'NULL' is to delete value so only add it if we update the entity
                if ($value === 'NULL' && $product->isNew()) {
                    continue;
                }

                if ($attribute->getBackendType() === 'static') {
                    $product->setData($attributeCode, $value);
                } else {
                    $attribute->setValue($value);
                    $product->setData($attributeCode, $attribute);
                }
            }
        }

        $this->_afterNormalization($product, $data);

        // attributes that are not in the eav_attribute table or get processed differently
        $product->setData(
            'type_id',
            $this->_getDataFromArray('type_id', $data, $this->_config->getProductDefault('type_id'))
        );
        if ($product->getData('type_id') !== 'simple') {
            $product->setData('has_options', 1);
        } else {
            $product->setData('has_options', 0);
        }
        $product->setData('stores', $this->_config->getStores($data));
        $product->setData('websites', $this->_config->getWebsites($data));

        if ($product->getData('type_id') === 'configurable') {
            $this->_getProductOptions($product, $data);
        }

        if ($product->getData('type_id') === 'simple'
            && ($configurableSku = $this->_getDataFromArray('configurable_product', $data))) {
            /** @var \Cemes\Pdo\Mysql $mysql */
            $mysql = Cemes_Registry::get('db');
            $configurableId = $mysql->select($product->getResource()->getEntityTable(), ['entity_id'])
                ->where('sku', $configurableSku, 'like')
                ->fetchEntry('entity_id');

            if ($configurableId) {
                $product->setData('configurable_product_id', $configurableId);
            }
        }

        $this->_createStockItem($product, $data);
        $this->_getRootCategories($product);
        $this->_getProductLinks($product, $data);
        $this->_getTierPrice($product, $data);
        $this->_getGroupPrice($product, $data);
        if ($options = Fci_Helper_Option::getInstance()->processData($data)) {
            $product->setData('options', $options);
        }
        $catHelper = Fci_Helper_Category::getInstance();
        $categories = $this->_getDataFromArray('categories', $data, '');
        if ($categories || $product->isNew()) {
            $product->setData(
                'category_ids',
                $catHelper->cleanCategories($categories, $product->getData('root_categories'))
            );
        }
        $this->_createGallery($product, $data);

        return $product;
    }

    /**
     * Processes data before it gets normalized.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     *
     * @return array
     */
    protected function _beforeNormalization(Fci_Model_Product $product, $data)
    {
        if ($product->isNew()) {
            // mandatory fields for insert that can be empty
            $data['weight'] = $this->_getDataFromArray('weight', $data, 0.);
            $data['description'] = $this->_getDataFromArray('description', $data, '&nbsp;');
            $data['short_description'] = $this->_getDataFromArray('short_description', $data, '&nbsp;');
            $data['options_container'] = $this->_getDataFromArray(
                'options_container',
                $data,
                $this->_config->getProductDefault('options_container')
            );
        }

        if (array_key_exists('attribute_set', $data)) {
            $product->setData('attribute_set_id', $this->_cache->validateAttributeSet($data['attribute_set']));
        }
        if ($product->getData('attribute_set_id') === null
            && ($product->isNew() || array_key_exists('attribute_set', $data))) {
            $product->setData('attribute_set_id', $this->_config->getProductDefault('attribute_set'));
        }

        return $data;
    }

    /**
     * Processes data after it got normalized.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _afterNormalization(Fci_Model_Product $product, $data)
    {
        // build url_key if not given and name is present
        if ($product->hasData('name') && !$product->hasData('url_key')) {
            $urlKeyAttribute = $this->_cache->getProductAttribute('url_key');

            $urlKey = Fci_Helper_Entity::getUrlPath($product->getData('name')->getValue());
            $urlKeyAttribute->setValue($urlKey);
            $product->setData('url_key', $urlKeyAttribute);
        }

        // remove special price that is 0 or empty string
        if ($product->hasData('special_price') && !(float)$product->getData('special_price')->getValue()) {
            $product->unsData('special_price');
        }

        // remove special_to_date if it is empty in csv
        if (array_key_exists('special_to_date', $data) && $data['special_to_date'] === '') {
            $product->unsData('special_to_date');
        }

        if ($product->hasData('description') && !$product->getData('description')->getValue()) {
            if ($product->isNew()) {
                $product->getData('description')->setValue('&nbsp;');
            } else {
                $product->unsData('description');
            }
        }

        if ($product->hasData('short_description') && !$product->getData('short_description')->getValue()) {
            if ($product->isNew()) {
                $product->getData('short_description')->setValue('&nbsp;');
            } else {
                $product->unsData('short_description');
            }
        }
    }

    /**
     * Prepares the product options for a configurable product.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _getProductOptions(Fci_Model_Product $product, $data)
    {
        $productOptions = [];

        $attributes = explode(',', $this->_getDataFromArray('product_options', $data, ''));
        foreach ($attributes as $attributeCode) {
            $attribute = $this->_cache->getProductAttribute(trim($attributeCode));
            if (!$attribute->isEmpty()) {
                $productOptions[$attribute->getLabel()] = $attribute->getAttributeId();
            }
        }

        if ($productOptions) {
            $product->setData('product_options', $productOptions);
        }
    }

    /**
     * Tries to create a stock item. If it was successful it will be added to the product.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _createStockItem(Fci_Model_Product $product, $data)
    {
        $attributes = [
            'manage_stock',
            'qty',
            'min_sale_qty',
            'max_sale_qty',
            'is_qty_decimal',
            'backorders',
            'qty_increments',
            'is_in_stock',
        ];

        $stockItem = [];

        foreach ($attributes as $attribute) {
            $value = $this->_getDataFromArray($attribute, $data);

            if ($value !== NULL) {
                $stockItem[$attribute] = $value;
            }
        }

        $stockItem = new Fci_Model_Product_StockItem($stockItem);

        $product->setData('stock_item', $stockItem);
    }

    /**
     * Retrieves all root categories of the websites stored in the product.
     *
     * @param Fci_Model_Product $product
     */
    protected function _getRootCategories(Fci_Model_Product $product)
    {
        $rootCategoryIds = [];

        foreach ($product->getData('websites') as $websiteId) {
            $id = $this->_cache->getData('websites/' . $websiteId . '/root_category');
            if ($id !== 0) {
                $rootCategoryIds[] = $id;
            }
        }

        array_unique($rootCategoryIds);

        if (count($rootCategoryIds)) {
            $product->setData('root_categories', $rootCategoryIds);
        }
    }

    /**
     * Prepares the data for product links and adds them to the product if data is available.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _getProductLinks(Fci_Model_Product $product, $data)
    {
        $linkTypes = [
            'related',
            'up_sell',
            'cross_sell',
        ];

        foreach ($linkTypes as $linkType) {
            if (!array_key_exists($linkType, $data)) {
                continue;
            }

            $linkedProducts = [];
            $productsData = explode(',', $data[$linkType]);
            $productsData = array_filter($productsData);

            foreach ($productsData as $productData) {
                $productData = explode(':', $productData);
                $sku = trim($productData[0]);

                if (!$sku) {
                    continue;
                }

                $position = 0;
                if (count($productData) === 2) {
                    $position = (int)trim($productData[1]);
                }

                $linkedProducts[$sku] = $position;
            }

            if ($linkedProducts) {
                $product->setData($linkType, $linkedProducts);
            }
        }
    }

    /**
     * Prepares the tier price data and adds it to the product if available.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _getTierPrice(Fci_Model_Product $product, $data)
    {
        $tierPrices = [];

        // format: website:customer_group#qty:price,customer_group#qty:price
        $tierPriceData = explode(',', $this->_getDataFromArray('tier_price', $data, ''));
        foreach ($tierPriceData as $tierPrice) {
            $count = preg_match('/([\w ]*:){0,1}([\w ]*)#([\d.]*):([\d.]*)/u', $tierPrice, $matches);
            if ($count !== 1) {
                continue;
            }
            list(, $website, $customerGroup, $qty, $price) = $matches;
            $tierPrice = [];
            if ($website !== '') {
                $tierPrice['website'] = $this->_cache->validateWebsite(trim($website, ':'));
            } else {
                $tierPrice['website'] = 0;
            }
            $tierPrice['customer_group'] = $this->_cache->validateCustomerGroup($customerGroup);
            $tierPrice['price'] = ((float)$price === 0.) ? '0.01' : number_format($price, 4);
            $tierPrice['qty'] = $qty;

            if ($tierPrice['website'] === false || $tierPrice['customer_group'] === false) {
                continue;
            }

            $tierPrices[] = $tierPrice;
        }

        if ($tierPrices) {
            $product->setData('tier_price', $tierPrices);
        } else {
            $product->unsData('tier_price');
        }
    }

    /**
     * Prepares the group price data and adds it to the product if available.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _getGroupPrice(Fci_Model_Product $product, $data)
    {
        $groupPrices = [];

        // format: website:customer_group#price,customer_group#price
        $groupPriceData = explode(',', $this->_getDataFromArray('group_price', $data, ''));
        foreach ($groupPriceData as $groupPrice) {
            $count = preg_match('/([\w ]*:){0,1}([\w ]*)#([\d.]*)/u', $groupPrice, $matches);
            if ($count !== 1) {
                continue;
            }
            list(, $website, $customerGroup, $price) = $matches;
            $groupPrice = [];
            if ($website !== '') {
                $groupPrice['website'] = $this->_cache->validateWebsite(trim($website, ':'));
            } else {
                $groupPrice['website'] = 0;
            }
            $groupPrice['customer_group'] = $this->_cache->validateCustomerGroup($customerGroup);
            $groupPrice['price'] = ((float)$price === 0.) ? '0.01' : number_format($price, 4);

            if ($groupPrice['website'] === false || $groupPrice['customer_group'] === false) {
                continue;
            }

            $groupPrices[] = $groupPrice;
        }

        if ($groupPrices) {
            $product->setData('group_price', $groupPrices);
        } else {
            $product->unsData('group_price');
        }
    }

    /**
     * Prepares the gallery data and adds the gallery to the product.
     *
     * @param Fci_Model_Product $product
     * @param array             $data
     */
    protected function _createGallery(Fci_Model_Product $product, $data)
    {
        if (!$this->_config->getImportGallery()) {
            if ($product->hasData('image')) {
                $product->unsData('image');
            }
            if ($product->hasData('small_image')) {
                $product->unsData('small_image');
            }
            if ($product->hasData('thumbnail')) {
                $product->unsData('thumbnail');
            }

            return;
        }

        $gallery = new Fci_Helper_Gallery($this->_config->getImageSeparator());

        // image name by sku as fallback
        if ($this->_config->getImagePrefix() !== '') {
            $imageName = $this->_config->getImagePrefix() . '-' . $product->getSku();
        } else {
            $imageName = $product->getSku();
        }

        // image
        if ($product->hasData('image')) {
            if ($product->hasData('image_label')) {
                $labels = $product->getData('image_label')->getValue();
                $product->unsData('image_label');
            } else {
                $labels = '';
            }
            $gallery->setImages($product->getData('image')->getValue(), $labels);
            $product->unsData('image');
        }
        if ($gallery->getImage() === 'no_selection' && $this->_config->getUseSkuImageFallback()) {
            $gallery->setImages($imageName);
        }

        // small image
        if ($product->hasData('small_image')) {
            if ($product->hasData('small_image_label')) {
                $labels = $product->getData('small_image_label')->getValue();
                $product->unsData('small_image_label');
            } else {
                $labels = '';
            }
            $gallery->setSmallImages($product->getData('small_image')->getValue(), $labels);
            $product->unsData('small_image');
        }
        if ($gallery->getSmallImage() === 'no_selection' && $this->_config->getUseSkuImageFallback()) {
            $gallery->setSmallImages($imageName);
        }

        // thumbnail
        if ($product->hasData('thumbnail')) {
            if ($product->hasData('thumbnail_label')) {
                $labels = $product->getData('thumbnail_label')->getValue();
                $product->unsData('thumbnail_label');
            } else {
                $labels = '';
            }
            $gallery->setThumbnails($product->getData('thumbnail')->getValue(), $labels);
            $product->unsData('thumbnail');
        }
        if ($gallery->getThumbnail() === 'no_selection' && $this->_config->getUseSkuImageFallback()) {
            $gallery->setThumbnails($imageName);
        }

        // excluded images
        if (array_key_exists('image_excluded', $data)) {
            $gallery->setExcludedImages($data['image_excluded']);
        }

        $product->setData('gallery', $gallery);
    }

    /**
     * Gets data with the given needle from the haystack. If the needle is not present in the haystack or the value is
     * empty the default will be returned.
     *
     * @param string $needle
     * @param array  $haystack
     * @param mixed  $default
     *
     * @return null
     */
    protected function _getDataFromArray($needle, $haystack, $default = null)
    {
        if (array_key_exists($needle, $haystack) && $haystack[$needle] !== '') {
            return $haystack[$needle];
        }

        return $default;
    }
}
