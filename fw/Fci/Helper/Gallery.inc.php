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
class Fci_Helper_Gallery
{
    /**
     * @var array
     */
    protected $images = [];

    /**
     * @var string
     */
    protected $image = 'no_selection';

    /**
     * @var string
     */
    protected $small_image = 'no_selection';

    /**
     * @var string
     */
    protected $thumbnail = 'no_selection';

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $excluded = [];

    /**
     * @var string
     */
    protected $_splitChar = ',';

    /**
     * @var array
     */
    protected static $_validExtensions = [
        'bmp',
        'gif',
        'jpg',
        'jpeg',
        'png',
        'tif',
        'tiff',
        'svg',
    ];

    /**
     * @var array
     */
    protected $_processedImages = [];

    /**
     * @var Fci_Log_AbstractLogger
     */
    protected $_logger;

    /**
     * Fci_Helper_Gallery constructor.
     *
     * @param string $splitChar
     */
    public function __construct($splitChar)
    {
        $this->_splitChar = $splitChar;
        $this->_logger = Cemes_Registry::get('logger');
    }

    /**
     * Sets the given images with the given labels if the image is present.
     *
     * @param string $images
     * @param string $labels
     */
    public function setImages($images, $labels = '')
    {
        // split comma separated list of images
        $images = explode($this->_splitChar, $images);
        $labels = explode($this->_splitChar, $labels);
        foreach ($images as $i => $image) {
            $file = $this->_getFilePath($image);
            if (!$file) {
                if (array_key_exists($image, $this->_processedImages) || trim($image) === '') {
                    continue;
                }
                $this->_logger->notice('Kein Bild mit dem Namen {image} gefunden.', ['image' => $image]);
            } else {
                if ($this->image === 'no_selection') {
                    $this->image = $file;
                }
                // save path for imagelist
                $this->images[] = $file;
                if (array_key_exists($i, $labels)) {
                    $this->labels[$file] = $labels[$i];
                }
            }
            $this->_processedImages[$image] = $file;
        }
        $this->_clearDouble();
    }

    /**
     * Sets the given small images with the given labels if the image is present.
     *
     * @param string $images
     * @param string $labels
     */
    public function setSmallImages($images, $labels = '')
    {
        // split comma separated list of images
        $images = explode($this->_splitChar, $images);
        $labels = explode($this->_splitChar, $labels);
        foreach ($images as $i => $image) {
            $file = $this->_getFilePath($image);
            if (!$file) {
                if (array_key_exists($image, $this->_processedImages) || trim($image) === '') {
                    continue;
                }
                $this->_logger->notice('Kein Bild mit dem Namen {image} gefunden.', ['image' => $image]);
            } else {
                if ($this->small_image === 'no_selection') {
                    $this->small_image = $file;
                }
                // save path for imagelist
                $this->images[] = $file;
                if (array_key_exists($i, $labels)) {
                    $this->labels[$file] = $labels[$i];
                }
            }
            $this->_processedImages[$image] = $file;
        }
        $this->_clearDouble();
    }

    /**
     * Sets the given thumbnails with the given labels if the image is present.
     *
     * @param string $images
     * @param string $labels
     */
    public function setThumbnails($images, $labels = '')
    {
        // split comma separated list of images
        $images = explode($this->_splitChar, $images);
        $labels = explode($this->_splitChar, $labels);
        foreach ($images as $i => $image) {
            $file = $this->_getFilePath($image);
            if (!$file) {
                if (array_key_exists($image, $this->_processedImages) || trim($image) === '') {
                    continue;
                }
                $this->_logger->notice('Kein Bild mit dem Namen {image} gefunden.', ['image' => $image]);
            } else {
                if ($this->thumbnail === 'no_selection') {
                    $this->thumbnail = $file;
                }
                // save path for imagelist
                $this->images[] = $file;
                if (array_key_exists($i, $labels)) {
                    $this->labels[$file] = $labels[$i];
                }
            }
            $this->_processedImages[$image] = $file;
        }
        $this->_clearDouble();
    }

    /**
     * Sets which images should be marked as excluded.
     *
     * @param string $images
     */
    public function setExcludedImages($images)
    {
        // split comma separated list of images
        $images = explode($this->_splitChar, $images);
        foreach ($images as $image) {
            list($a, $b, $image) = explode('/', $this->_getFileAsPath($image));

            $image = $this->_removeFileExtension($image);

            foreach ($this->images as $galleryImage) {
                // check if image with same name exists
                if (strpos($galleryImage, $a . '/' . $b . '/' . $image . '.') === 0) {
                    $this->excluded[] = $galleryImage;
                    break;
                }
            }
        }
    }

    /**
     * Returns all processed images.
     *
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Returns the base image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Returns the small image.
     *
     * @return string
     */
    public function getSmallImage()
    {
        return $this->small_image;
    }

    /**
     * Returns the thumbnail.
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Returns if the given image is excluded or not as an int for database usage.
     *
     * @param string $image
     *
     * @return int
     */
    public function isExcluded($image)
    {
        return (int)in_array($image, $this->excluded, true);
    }

    /**
     * Returns the label for the given image.
     *
     * @param string $image
     *
     * @return string
     */
    public function getLabel($image)
    {
        return array_key_exists($image, $this->labels) ? $this->labels[$image] : '';
    }

    /**
     * Returns if images are present in the helper.
     *
     * @return bool
     */
    public function hasImages()
    {
        return !empty($this->images);
    }

    /**
     * Searches the given image in media/import and media/catalog/product. If the file is in the import folder it will
     * be moved to the catalog product folder. The final path to the file relative to media/catalog/product will be
     * returned or false if something went wrong.
     *
     * @param string $image
     *
     * @return string
     */
    protected function _getFilePath($image)
    {
        if (array_key_exists($image, $this->_processedImages)) {
            return $this->_processedImages[$image];
        }

        $image = trim($image);
        $image = ltrim($image, '/');
        $image = str_replace('&nbsp;', '', $image);

        if ($image === '') {
            return null;
        }

        list($a, $b, $image) = explode('/', $this->_getFileAsPath($image));

        $image = $this->_removeFileExtension($image);

        $mediaDir = Fci_Helper_Factory_MagentoFactory::getMagento()->getMediaDirectory();
        // replace extension with * to find all images with this name
        $import = glob($mediaDir . '/import/' . $image . '.*');
        $media = glob($mediaDir . '/catalog/product/' . $a . '/' . $b . '/' . $image . '.*');
        if (is_array($import) && count($import) !== 0) {
            $image = basename(array_pop($import));

            return $this->_moveFile($image, $a, $b);
        }

        if (is_array($media) && count($media) !== 0) {
            $image = basename(array_pop($media));

            return $a . '/' . $b . '/' . $image;
        }

        return null;
    }

    /**
     * Moves the given image from media/import to media/catalog/product with $a and $b as sub folders.
     * If the file could not be moved the method will return false.
     *
     * @param string $image
     * @param string $a
     * @param string $b
     *
     * @return string
     */
    protected function _moveFile($image, $a, $b)
    {
        $mediaDir = Fci_Helper_Factory_MagentoFactory::getMagento()->getMediaDirectory();
        $oldFile = trim($mediaDir . '/import/' . $image);
        $newFile = trim($mediaDir . '/catalog/product/' . $a . '/' . $b . '/' . $image);
        if (!@mkdir(dirname($newFile), 0755, true) && !is_dir(dirname($newFile))) {
            $this->_logger->error(
                'Ordner {folder} konnte nicht erstellt werden.',
                ['folder' => dirname($newFile)]
            );
        }

        // move the image to it's new position
        if (!rename($oldFile, $newFile)) {
            $context = [
                'old' => $oldFile,
                'new' => $newFile,
            ];
            $this->_logger->notice('Bild konnte nicht von {old} nach {new} verschoben werden.', $context);

            return null;
        }

        return $a . '/' . $b . '/' . $image;
    }

    /**
     * Removes double entries from list.
     */
    protected function _clearDouble()
    {
        $this->images = array_values(array_unique($this->images));
    }

    /**
     * Normalizes the file name to the magento based file path with the first and second character as sub folders.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function _getFileAsPath($filename)
    {
        // if filename is not already in proper path format
        if (preg_match('%[A-Za-z0-9]/[\w]/%', $filename) === 0) {
            // get first and second letter of the file
            $a = substr($filename, 0, 1);
            $b = substr($filename, 1, 1);
            if ($b === '.' || $b === '') {
                $b = '_';
            }

            return $a . '/' . $b . '/' . $filename;
        }

        return $filename;
    }

    /**
     * Removes the file extension from the filename.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function _removeFileExtension($filename)
    {
        $info = pathinfo($filename);
        if (array_key_exists('extension', $info)
            && in_array(strtolower($info['extension']), static::$_validExtensions, true)) {
            return basename($filename, '.' . $info['extension']);
        }

        return basename($filename);
    }
}
