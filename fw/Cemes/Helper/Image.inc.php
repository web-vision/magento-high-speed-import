<?php
if (!$GLOBALS['CEMES']['ACTIVE'])
    die('Framework ist nicht aktiv');

/**
 * Klasse zum resizen von Bildern
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 */
class Cemes_Helper_Image {
    const RESIZE_SCALE_EXACT = 0;
    const RESIZE_SCALE_PORTRAIT = 1;
    const RESIZE_SCALE_LANDSCAPE = 2;
    const RESIZE_SCALE_AUTO = 3;
    const RESIZE_SCALE_CROP = 4;

    const RESIZE_BY_WIDTH = 0;
    const RESIZE_BY_HEIGHT = 1;

    protected $original;
    protected $resized;
    protected $width;
    protected $height;

    public function setImage($file) {
        $this->original = $this->_openImage($file);

        $this->width = imagesx($this->original);
        $this->height = imagesy($this->original);
    }

    protected function _openImage($file) {
        // extension ermitteln; alles ab dem letzten Punkt
        $extension = strtolower(strrchr($file, '.'));

        switch($extension) {
            case '.jpg':
            case '.jpeg':
                $img = @imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = @imagecreatefromgif($file);
                break;
            case '.png':
                $img = @imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }

    public function resizeImage($newWidth, $newHeight, $option=self::RESIZE_SCALE_AUTO) {
        // ermittle optimale höhe und breite
        $options = $this->_getDimensions($newWidth, $newHeight, strtolower($option));

        // erstelle ein canvas mit optimaler höhe und breite
        $this->imageResized = imagecreatetruecolor($option->width, $options->height);
        // kopiere vom original Bild auf das canvas
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $options->width, $options->height, $this->width, $this->height);

        // wenn gecropt werden soll
        if ($options->crop) {
            $this->_crop($options->width, $options->height, $newWidth, $newHeight);
        }
    }

    private function _getDimensions($newWidth, $newHeight, $scaleType) {
        switch ($scaleType) {
            case self::RESIZE_SCALE_EXACT:
                $options = array(
                    'width' => $newWidth,
                    'height' => $newHeight,
                    'crop' => false
                );
                break;
            case self::RESIZE_SCALE_PORTRAIT:
                $options = array(
                    'width' => $this->_getFixedSize($newHeight, self::RESIZE_BY_HEIGHT),
                    'height' => $newHeight,
                    'crop' => false
                );
                break;
            case self::RESIZE_SCALE_LANDSCAPE:
                $options = array(
                    'width' => $newWidth,
                    'height' => $this->_getFixedSize($newWidth, self::RESIZE_BY_WIDTH),
                    'crop' => false
                );
                break;
            case self::RESIZE_SCALE_AUTO:
                $options = $this->_getSizeByAuto($newWidth, $newHeight);
                break;
            case self::RESIZE_SCALE_CROP:
                $options = $this->_getOptimalCrop($newWidth, $newHeight);
                break;
        }
        return (object) $options;
    }

    private function _getFixedSize($fixed, $mode = self::RESIZE_BY_HEIGHT) {
        $ratio = ($mode == self::RESIZE_BY_HEIGHT) ? $this->width / $this->height : $this->height / $this->width;
        return $fixed * $ratio;
    }

    private function _getSizeByAuto($newWidth, $newHeight) {
        if ($this->height < $this->width) { // original Bild ist breiter (landscape)
            $options = array(
                'width' => $newWidth,
                'height' => $this->_getFixedSize($newWidth, self::RESIZE_BY_WIDTH),
            );
        } else if ($this->height > $this->width) { // original Bild ist höher (portrait)
            $options = array(
                'width' => $this->_getFixedSize($newHeight, self::RESIZE_BY_HEIGHT),
                'height' => $newHeight,
            );
        } else { // *** Bild ist quadratisch
            if ($newHeight < $newWidth) {
                $options = array(
                    'width' => $newWidth,
                    'height' => $this->_getFixedSize($newWidth, self::RESIZE_BY_WIDTH),
                );
            } else if ($newHeight > $newWidth) {
                $options = array(
                    'width' => $this->_getFixedSize($newHeight, self::RESIZE_BY_HEIGHT),
                    'height' => $newHeight,
                );
            } else { //
                $options = array(
                    'width' => $newHeight,
                    'height' => $newHeight,
                );
            }
        }

        return (object) $options['crop'] = false;
    }

    private function _getOptimalCrop($newWidth, $newHeight) {
        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        $optimalRatio = ($heightRatio < $widthRatio) ? $heightRatio : $widthRatio;

        return (object) array('width' => $this->height / $optimalRatio, 'height' => $this->width  / $optimalRatio, 'crop' => true);
    }

    private function _crop($optimalWidth, $optimalHeight, $newWidth, $newHeight) {
        // finde die Mitte
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->imageResized;
        imagedestroy($this->imageResized);

        // von der Mitte aus croppen
        $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
        imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
    }

    public function saveImage($savePath, $imageQuality="100") {
        // extension ermitteln
        $extension = strtolower(strrchr($savePath, '.'));

        switch($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG)
                    imagejpeg($this->imageResized, $savePath, $imageQuality);
                break;
            case '.gif':
                if (imagetypes() & IMG_GIF)
                    imagegif($this->imageResized, $savePath);
                break;
            case '.png':
                // qualität umwandeln von 0-100 nach 0-9
                $scaleQuality = round(($imageQuality/100) * 9);
                // invertieren da 0 das beste ist und nicht 9
                $invertScaleQuality = 9 - $scaleQuality;
                if (imagetypes() & IMG_PNG)
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
                break;
            default:
                // Keine Endung gefunden also wird nicht gespeichert
                break;
        }

        imagedestroy($this->imageResized);
    }
}