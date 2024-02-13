<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker;

use Api2Pdf\Api2PdfResult;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;

use springworks\pdfmaker\services\Pdf as PdfService;
use springworks\pdfmaker\variables\PdfMakerVariable;
use springworks\pdfmaker\models\Settings;

use yii\base\Event;
use yii\base\Exception;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 *
 * @property  PdfService $pdf
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class PdfMaker extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * PdfMaker::$plugin
     *
     * @var PdfMaker
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * PdfMaker::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pdfMaker', PdfMakerVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        if ($this->getSettings()->formieIntegration) {
            Event::on(
                \verbb\formie\services\PdfTemplates::class,
                \verbb\formie\services\PdfTemplates::EVENT_BEFORE_RENDER_PDF,
                function(\verbb\formie\events\PdfEvent $event) {
                    $variables = $event->variables;
                    $template = $event->template;

                    $view = Craft::$app->getView();

                    // If a custom template is supplied, use that, otherwise just use the email notification HTML
                    if ($template) {
                        $oldTemplatesPath = $view->getTemplatesPath();

                        // We need to do a little more work here to deal with a template, if picked
                        $view->setTemplatesPath(Craft::$app->getPath()->getSiteTemplatesPath());

                        if (!$template || !$view->doesTemplateExist($template)) {
                            throw new Exception('PDF template file does not exist.');
                        }

                        try {
                            $html = $view->renderTemplate($template, $variables);
                        } catch (\Exception $e) {
                            \verbb\formie\Formie::error('An error occurred while generating this PDF: ' . $e->getMessage());

                            // Set the _pdf html to the render error.
                            Craft::$app->getErrorHandler()->logException($e);
                            $html = Craft::t('formie', 'An error occurred while generating this PDF.') . ' ' . $e->getMessage();
                        }

                        // Restore the original template path
                        $view->setTemplatesPath($oldTemplatesPath);
                    } else {
                        $emailRender = \verbb\formie\Formie::$plugin->getEmails()->renderEmail($variables['notification'], $variables['submission']);
                        $message = $emailRender['email'] ?? '';

                        $html = $message->getSwiftMessage()->getBody();
                        $children = $message->getSwiftMessage()->getChildren();

                        // Getting the content from an email is a little more involved...
                        if (!$html && $children) {
                            foreach ($children as $child) {
                                if ($child->getContentType() == 'text/html') {
                                    $html = $child->getBody();
                                }
                            }
                        }
                    }

                    $pdfResponse = $this->pdf->pdfFromHtml($html, false, '', []);

                    $event->pdf = null;

                    if ($pdfResponse instanceof Api2PdfResult && $pdfResponse->getFile()) {
                        $event->pdf = $pdfResponse->getFileContents();
                    }
                }
            );
        }

        if ($this->getSettings()->commerceIntegration) {
            Event::on(
                \craft\commerce\services\Pdfs::class,
                \craft\commerce\services\Pdfs::EVENT_BEFORE_RENDER_PDF,
                function(\craft\commerce\events\PdfEvent $event) {
                    $order = $event->order;

                    $variables = $event->variables;
                    $variables['order'] = $event->order;
                    $variables['option'] = $event->option;

                    // Set Craft to the site template mode
                    $view = Craft::$app->getView();

                    $oldTemplateMode = $view->getTemplateMode();
                    $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

                    if (!$event->template || !$view->doesTemplateExist($event->template)) {
                        // Restore the original template mode
                        $view->setTemplateMode($oldTemplateMode);

                        throw new Exception('PDF template file does not exist.');
                    }

                    try {
                        $html = $view->renderTemplate($event->template, $variables);
                    } catch (\Exception $e) {
                        // Set the pdf html to the render error.
                        Craft::error('Order PDF render error. Order number: ' . $order->getShortNumber() . '. ' . $e->getMessage());
                        Craft::$app->getErrorHandler()->logException($e);
                        $html = Craft::t('commerce', 'An error occurred while generating this PDF.');
                    }

                    // Restore the original template mode
                    $view->setTemplateMode($oldTemplateMode);

                    $pdfResponse = $this->pdf->pdfFromHtml($html, false, '', []);

                    $event->pdf = null;

                    if ($pdfResponse instanceof Api2PdfResult && $pdfResponse->getFile()) {
                        $event->pdf = $pdfResponse->getFileContents();
                    }
                }
            );
        }

        /**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'pdf-maker',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'pdf-maker/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
