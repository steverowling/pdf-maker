<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker\assetbundles\pdfmaker;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * PdfMakerAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 */
class PdfMakerAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@springworks/pdfmaker/assetbundles/pdfmaker/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/PdfMaker.js',
        ];

        $this->css = [
            'css/PdfMaker.css',
        ];

        parent::init();
    }
}
