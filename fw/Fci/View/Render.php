<?php

namespace Fci\View;

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
 * @package     Fci\View\Controller
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Mark Houben <mark.houben@wmdb.de>
 */
class Render
{
    /**
     * @var \Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * Render constructor.
     *
     * @param array $environment
     */
    public function __construct(array $environment)
    {
        $this->_setTemplateFolder($environment['defaultTemplatePath']);
        $this->_setTwigEnvironment($environment['environment']);
        $this->_addTwigExtension();
    }

    /**
     * Sets the template file which should be rendered.
     *
     * @param $templatePath
     *
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * Renders the given template with the given data.
     *
     * @param $template
     * @param $data
     *
     * @return string
     */
    public function render($template, $data)
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Adds a path to the Twig Loader.
     *
     * @param string $path
     * @param string $namespace
     *
     * @throws \Twig_Error_Loader
     */
    public function addPath($path, $namespace = null)
    {
        if (!$namespace) {
            $this->loader->addPath($path);
        } else {
            $this->loader->addPath($path, $namespace);
        }
    }

    /**
     * Prepends a path to the Twig Loader.
     *
     * @param string $path
     * @param string $namespace
     *
     * @throws \Twig_Error_Loader
     */
    public function prependPath($path, $namespace = null)
    {
        if (!$namespace) {
            $this->loader->prependPath($path);
        } else {
            $this->loader->prependPath($path, $namespace);
        }
    }

    /**
     * Adds global data that will be available in all templates.
     *
     * @param string $globalName
     * @param mixed $globalData
     */
    public function addGlobalData($globalName, $globalData)
    {
        $this->twig->addGlobal($globalName, $globalData);
    }

    /**
     * Sets the default template path and initializes the Twig Loader with this path.
     *
     * @param $defaultTemplatePath
     */
    protected function _setTemplateFolder($defaultTemplatePath)
    {
        $path = PATH_VIEW . DIRECTORY_SEPARATOR . $this->templatePath;
        if (empty($this->templatePath)) {
            $path = PATH_VIEW . DIRECTORY_SEPARATOR . $defaultTemplatePath;
        }
        $this->loader = new \Twig_Loader_Filesystem($path);
    }

    /**
     * Initializes the Twig Environment with the given options.
     *
     * @param array $environment
     */
    protected function _setTwigEnvironment($environment)
    {
        $twigEnvironment = [];
        foreach ($environment as $optionName => $optionValue) {
            switch (ucwords($optionName)) {
                case 'Cache':
                    if ($optionValue) {
                        $pathToTemplate = PATH_VIEW . DIRECTORY_SEPARATOR . 'Cache';
                        $twigEnvironment[$optionName] = new \Twig_Cache_Filesystem(
                            $pathToTemplate, \Twig_Cache_Filesystem::FORCE_BYTECODE_INVALIDATION
                        );
                    } else {
                        $twigEnvironment[$optionName] = $optionValue;
                    }
                    break;
                default:
                    $twigEnvironment[$optionName] = $optionValue;
            }
        }
        $this->twig = new \Twig_Environment($this->loader, $twigEnvironment);
    }

    /**
     * Adds the debugging Twig extension.
     */
    protected function _addTwigExtension()
    {
        $this->twig->addExtension(new \Twig_Extension_Debug());
    }
}
