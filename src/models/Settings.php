<?php
/**
 * Pdf Maker plugin for Craft CMS 3.x
 *
 * PDF creation using api2pdf v2
 *
 * @link      https://springworks.co.uk
 * @copyright Copyright (c) 2022 Steve Rowling
 */

namespace springworks\pdfmaker\models;

use springworks\pdfmaker\PdfMaker;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

/**
 * PdfMaker Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Steve Rowling
 * @package   PdfMaker
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Api2Pdf API key
     *
     * @var string
     */
    public string $apiKey = '';

    /**
     * PDF options
     *
     * @var array
     */
    public array $options = [
        'pdf' => [
            "landscape" => false,
            "width" => "8.27in",
            "height" => "11.69in",
            "marginTop" => ".4in",
            "marginBottom" => ".4in",
            "marginLeft" => ".4in",
            "marginRight" => ".4in",
        ],
        'image' => [
            "fullPage" => true,
            "viewPortOptions" => [
                "width" => 1920,
                "height" => 1080
            ]
        ]
    ];

    /**
     * Formie integration
     *
     * @var bool
     */
    public bool $formieIntegration = false;

    /**
     * Commerce integration
     *
     * @var bool
     */
    public bool $commerceIntegration = false;

    // Public Methods
    // =========================================================================

    /**
     * @return string the parsed API key
     */
    public function getApiKey(): string
    {
        return Craft::parseEnv($this->apiKey);
    }

    /*
     * https://docs.craftcms.com/v3/extend/environmental-settings.html#validation
     *
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['apiKey'],
            ],
        ];
    }

    /**
     * Returns the validation rules for attributes.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            ['apiKey', 'required'],
            ['apiKey', 'string'],
            ['apiKey', 'default', 'value' => ''],
            ['formieIntegration', 'boolean'],
            ['formieIntegration', 'default', 'value' => false],
            ['commerceIntegration', 'boolean'],
            ['commerceIntegration', 'default', 'value' => false],
        ];
    }
}
