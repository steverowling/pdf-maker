<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker\variables;

use springworks\pdfmaker\PdfMaker;

use Craft;

/**
 * Pdf Maker Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.pdfMaker }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 */
class PdfMakerVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Checks that Formie plugin greater than or equal to version 1.5 is installed
     *
     * @return bool
     */
    public function isFormieEnabled(): bool
    {
        $plugins = Craft::$app->getPlugins();
        $formieEnabled = $plugins->isPluginEnabled('formie');

        if ($formieEnabled) {
            $formieVersion = $plugins->getPlugin('formie')->getVersion();

            $formieEnabled = version_compare($formieVersion, '1.5.0', '>=');
        }

        return $formieEnabled;
    }

    /**
     * Checks that Commerce plugin greater than or equal to version 2.0 is installed
     *
     * @return bool
     */
    public function isCommerceEnabled(): bool
    {
        $plugins = Craft::$app->getPlugins();
        $commerceEnabled = $plugins->isPluginEnabled('commerce');

        if ($commerceEnabled) {
            $commerceVersion = $plugins->getPlugin('commerce')->getVersion();

            $commerceEnabled = version_compare($commerceVersion, '2.0.0', '>=');
        }

        return $commerceEnabled;
    }
}
