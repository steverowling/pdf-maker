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

    /**
     * @param string $url
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     */
    public function pdfFromUrl(string $url = '', bool $inline = false, string $filename = '', array $options = [])
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
    public function pdfFromHtml(string $html = '', bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        if (!$html) {
            return [ "success" => false, "error" => "No HTML provided." ];
        }

        try {
            $response = $apiClient->chromeHtmlToPdf($html, $inline, $filename, $options);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }

    /**
     * @param string $template
     * @param array $variables
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function pdfFromTemplate(string $template = '', array $variables = [], bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        $html = $this->_getHtmlFromTemplate($template, $variables);

        if ($html && is_array($html)) {
            return $html;
        }

        try {
            $response = $apiClient->chromeHtmlToPdf($html, $inline, $filename, $options);
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

    /**
     * @param string $url
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     */
    public function imageFromUrl(string $url = '', bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        if (!$url) {
            return [ "success" => false, "error" => "No URL provided." ];
        }

        if ($this->_getIsLocalUrl($url)) {
            return $this->_getLocalUrlResponse($url);
        }

        try {
            $response = $apiClient->chromeUrlToImage($url, $inline, $filename, $options);
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
    public function imageFromHtml(string $html = '', bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        if (!$html) {
            return [ "success" => false, "error" => "No HTML provided." ];
        }

        try {
            $response = $apiClient->chromeHtmlToImage($html, $inline, $filename, $options);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }

    /**
     * @param string $template
     * @param array $variables
     * @param bool $inline
     * @param string $filename
     * @param array $options
     * @return Api2PdfResult|array
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function imageFromTemplate(string $template = '', array $variables = [], bool $inline = false, string $filename = '', array $options = [])
    {
        $apiClient = $this->_getClient();

        $html = $this->_getHtmlFromTemplate($template, $variables);

        if ($html && is_array($html)) {
            return $html;
        }

        try {
            $response = $apiClient->chromeHtmlToImage($html, $inline, $filename, $options);
        } catch (Api2PdfException $e) {
            $response = [ "success" => false, "error" => $e->getMessage() ];
        }
        return $response;
    }

    /**
     * @param string $template
     * @param array $variables
     * @return array|string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function _getHtmlFromTemplate(string $template = '', array $variables = [])
    {
        if (!$template) {
            return [ "success" => false, "error" => "No template provided." ];
        }

        $view = Craft::$app->getView();

        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        if (!$view->doesTemplateExist($template)) {
            $view->setTemplateMode($oldMode);
            return [ "success" => false, "error" => "No template found." ];
        }

        $html = $view->renderTemplate($template, $variables);

        $view->setTemplateMode($oldMode);

        if (!$html) {
            return [ "success" => false, "error" => "Template could not be rendered." ];
        }

        return $html;
    }
}
