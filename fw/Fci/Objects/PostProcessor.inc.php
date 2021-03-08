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
 * @package     Fci_Objects
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Objects_PostProcessor
{
    /**
     * Fci_Objects_PostProcessor constructor.
     *
     * @param Fci_Model_AbstractEntity $product
     * @param string                   $profile
     */
    public function __construct(&$product, $profile)
    {
        $files = $this->getFileList(ROOT_BASEDIR . 'bin/processors');
        foreach ($files as $file) {
            if (Cemes_StdLib::endsWith($file, '.php')) {
                include_once(ROOT_BASEDIR . 'bin/processors/' . $file);
                $class = str_replace('.php', '', $file);
                if (class_exists($class) && is_subclass_of($class, 'Fci_Objects_AbstractProcessor')) {
                    /** @var Fci_Objects_AbstractProcessor $processor */
                    $processor = new $class();
                    $processor->post($product, $profile);
                }
            }
        }
    }

    /**
     * Returns a list of files for the given folder path.
     *
     * @param string $folderPath
     *
     * @return array
     */
    protected function getFileList($folderPath)
    {
        if (!@mkdir($folderPath, 0644, true) && !is_dir($folderPath)) {
            return array();
        }
        $files = scandir($folderPath);

        foreach ($files as $index => $file) {
            if ($file === '.' || $file === '..') {
                unset($files[$index]);
            }
        }

        return $files;
    }
}
