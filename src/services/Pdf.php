<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker\services;

use Api2Pdf\Api2Pdf;
use Api2Pdf\Api2PdfException;
use Api2Pdf\Api2PdfResult;

use Craft;
use craft\base\Component;
use craft\web\View;

use springworks\pdfmaker\PdfMaker;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use yii\base\Exception;

/**
 * Pdf Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 */
class Pdf extends Component
{
    /**
     * @param string|null $apiKey
     * @return Api2Pdf
     */
    private function _getClient(string $apiKey = null): Api2Pdf
    {
        if ($apiKey === null) {
            $apiKey = PdfMaker::$plugin->getSettings()->getApiKey();
        }

        return new Api2Pdf($apiKey);
    }

    /**
     * @param string $url
     * @return bool
     */
    private function _getIsLocalUrl(string $url): bool
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);

        if (!$url) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return strpos($host, 'localhost') !== false || strpos($host, 'ddev', -4) !== false || strpos($host, 'test', -4) !== false || strpos($host, 'nitro', -5) !== false;
    }

    /**
     * @param string $url
     * @return array
     */
    private function _getLocalUrlResponse(string $url): array
    {
        $msg = "Invalid URL: local URL provided, which Api2Pdf won’t be able to access: " . $url;
        return [
            "success" => false,
            "error" => $msg
        ];
    }

//    /**
//     * @return string
//     * @throws LoaderError
//     * @throws RuntimeError
//     * @throws SyntaxError
//     * @throws Exception
//     */
//    public function renderPdfTemplateHtml(): string
//    {
//        // https://docs.craftcms.com/v3/extend/updating-plugins.html#rendering-templates
//
//        /*
//        // For using templates within the plugin
//        $oldMode = \Craft::$app->view->getTemplateMode();
//        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
//        // Strangely, it seems necessary to leave out `templates/`
//        $templatePath = 'api2pdf/pdf.twig';
//        $pdfHtml = \Craft::$app->view->renderTemplate($templatePath, [
//          'body' => 'Example body from plugin'
//        ]);
//        \Craft::$app->view->setTemplateMode($oldMode);
//        */
//
//        // Using templates from the site
//        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
//
//        // TODO The specific template and the variables need
//        // to be passed along as arguments
//        $pdfHtml = Craft::$app->getView()->renderTemplate(
//            "/pdf/proof-letter.twig",
//            [
//                'body' => 'Testing'
//            ]
//        );
//
//        return $pdfHtml;
//    }

    /**
     * @param string $url
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     */
    public function generateFromUrl(string $url = '', bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        if (!$url) {
            return [ "success" => false, "error" => "No URL provided." ];
        }

        if ($this->_getIsLocalUrl($url)) {
            return $this->_getLocalUrlResponse($url);
        }

        try {
            $response = $apiClient->chromeUrlToPdf($url, $inline, $filename, $options);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }

    /**
     * @param string $html
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     */
    public function generateFromHtml(string $html = '', bool $inline = false, string $filename = '', array $options = [])
    {
        // if ($html !== '') {
        //   $pdfHtml = $this->renderPdfTemplateHtml();
        // } else {
        // TODO Remove support for the $html argument from Twig, once it’s
        // possible to supply your own templates, ex. we get them from your
        // templates/api2pdf directory or whatever
        $pdfHtml = $html;
        // }

        $apiClient = $this->_getClient();

        if (!$pdfHtml) {
            return [ "success" => false, "error" => "No HTML provided." ];
        }

        try {
            $response = $apiClient->chromeHtmlToPdf($pdfHtml, $inline, $filename, $options);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }

    /**
     * @param array $urls
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     */
    public function merge(array $urls = [], bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        if (!$urls) {
            return [ "success" => false, "error" => "No URLs provided." ];
        }

        foreach ($urls as $url) {
            // Check each URL to make sure it isn’t local
            if ($this->_getIsLocalUrl($url)) {
                return $this->_getLocalUrlResponse($url);
            }
        }

        try {
            $response = $apiClient->pdfsharpMerge($urls, $inline, $filename);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }
}
