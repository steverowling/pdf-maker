<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker\controllers;

use Craft;
use craft\web\Controller;
use springworks\pdfmaker\PdfMaker;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;

/**
 * Pdf Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 */
class PdfController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionPdfFromHtml(): Response
    {
        $request = Craft::$app->getRequest();
        $htmlString = $request->getParam('html');
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'pdf');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->pdfFromHtml($htmlString, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionPdfFromTemplate(): Response
    {
        $request = Craft::$app->getRequest();
        $template = $request->getValidatedBodyParam('template') ?? '';
        $variables = $request->getBodyParam('variables') ?? [];
        $validatedVariables = [];
        $security = Craft::$app->getSecurity();
        foreach ($variables as $key => $value) {
            $validatedVariables[$key] = $security->validateData($value);
        }
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'pdf');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->pdfFromTemplate($template, $validatedVariables, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     */
    public function actionPdfFromUrl(): Response
    {
        $request = Craft::$app->getRequest();
        $url = $request->getParam('url');
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'pdf');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->pdfFromUrl($url, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     */
    public function actionMerge(): Response
    {
        $request = Craft::$app->getRequest();
        $urls = $request->getParam('urls');
        if (!$urls) {
            $urls = [];
        }
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'pdf');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->merge($urls, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     */
    public function actionImageFromHtml(): Response
    {
        $request = Craft::$app->getRequest();
        $htmlString = $request->getParam('html');
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'image');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->imageFromHtml($htmlString, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionImageFromTemplate(): Response
    {
        $request = Craft::$app->getRequest();
        $template = $request->getValidatedBodyParam('template') ?? '';
        $variables = $request->getBodyParam('variables') ?? [];
        $validatedVariables = [];
        $security = Craft::$app->getSecurity();
        foreach ($variables as $key => $value) {
            $validatedVariables[$key] = $security->validateData($value);
        }
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'image');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->imageFromTemplate($template, $validatedVariables, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @return Response
     */
    public function actionImageFromUrl(): Response
    {
        $request = Craft::$app->getRequest();
        $url = $request->getParam('url');
        $inline = $this->_getInline($request);
        $filename = $this->_getFilename($request);
        $options = $this->_getOptions($request, 'image');
        $redirect = $this->_getRedirect($request);
        $result = PdfMaker::$plugin->pdf->imageFromUrl($url, $inline, $filename, $options);

        return $this->_formatResponse($result, $redirect, $inline, $filename);
    }

    /**
     * @param $request
     * @return bool
     */
    private function _getInline($request): bool
    {
        return $request->getParam('inline') ?? false;
    }

    /**
     * @param $request
     * @return string
     */
    private function _getFilename($request): string
    {
        $filename = $request->getParam('filename') ?? '';
        if (strpos(strtolower($filename),'.pdf',-4) === false) {
            $filename .= '.pdf';
        }
        return $filename;
    }

    /**
     * @param $request
     * @param $type
     * @return array
     */
    private function _getOptions($request, $type): array
    {
        $defaultOptions = PdfMaker::$plugin->getSettings()->options[$type];
        $options = $request->getParam('options');
        if (!$options) {
            $options = [];
        }
        if (isset($options['landscape'])) {
            $options['landscape'] = (bool) $options['landscape'];
        }
        if (isset($options['fullPage'])) {
            $options['fullPage'] = (bool) $options['fullPage'];
        }
        return array_merge($defaultOptions, $options);
    }

    /**
     * @param $request
     * @return bool
     */
    private function _getRedirect($request): bool
    {
        return (bool)$request->getParam('redirect');
    }

    /**
     * @param $response
     * @param $redirect
     * @param $inline
     * @param null $filename
     * @return Response
     */
    private function _formatResponse($response, $redirect = null, $inline = null, $filename = null): Response
    {
        if (is_array($response)) {
            return $this->asJson($response);
        }

        if ($redirect) {
            Craft::$app->getResponse()->redirect($response->getFile());
        }

        if ($inline) {
            try {
                return Craft::$app->getResponse()->sendContentAsFile($response->getFileContents(), $filename);
            } catch (RangeNotSatisfiableHttpException $e) {
            } catch (HttpException $e) {
            }
        }

        return $this->asJson([
            "success" => true,
            "pdf" => $response->getFile(),
            "seconds" => $response->getSeconds(),
            "mbOut" => $response->getMbOut(),
            "cost" => $response->getCost(),
            "responseId" => $response->getResponseId()
        ]);
    }
}
