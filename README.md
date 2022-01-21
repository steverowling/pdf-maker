# Pdf Maker plugin for Craft CMS 3.x

PDF creation using the v2 API headless chrome services from [api2pdf.com](https://api2pdf.com). You can also use Api2Pdf for rendering PDFs in [Formie](https://plugins.craftcms.com/formie) or [Craft Commerce](https://plugins.craftcms.com/commerce).

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require springworks/pdf-maker

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Pdf Maker.

## Settings

The only required setting is a valid Api2Pdf API key. It is recommended to set this using an [environment variable](https://docs.craftcms.com/v3/config/environments.html).

The plugin will automatically detect if either Formie or Craft Commerce is installed and enabled and will offer additional settings to override the PDF generation for these plugins if detected.

Default PDF creation options are set in the config file. Rename `config.php` to `pdf-maker.php` and place in your `config` folder. Change the default options as required. For details of what is available, please see https://www.api2pdf.com/documentation/advanced-options-headless-chrome/.

## Examples

### Generate PDF from URL

```twig
<form method="post" action="" accept-charset="UTF-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="pdf-maker/pdf/generate-from-url">
    <input type="hidden" name="url" value="https://example.com" />
    
    {# Set the filename (optional) #}
    <input type="hidden" name="filename" value="test.pdf" />

    {# Redirect to the PDF URL (optional) #}
    <input type="hidden" name="redirect" value="1" />

    <input type="submit" value="Create PDF" />
</form>

```

### Generate PDF from HTML

```twig
<form method="post" action="" accept-charset="UTF-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="pdf-maker/pdf/generate-from-html">
    <input type="hidden" name="html" value="<p>HTML content for PDF</p>" />
    
    {# Set the filename (optional) #}
    <input type="hidden" name="filename" value="test.pdf" />

    {# Redirect to the PDF URL (optional) #}
    <input type="hidden" name="redirect" value="1" />

    <input type="submit" value="Create PDF" />
</form>

```

### Merge PDFs

```twig
{% set urls = [
    'https://example.com/one.pdf',
    'https://example.com/two.pdf'
] %}

<form method="post" action="" accept-charset="UTF-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="pdf-maker/pdf/merge">
    {% for url in urls %}
        <input type="hidden" name="urls[]" value="{{ url }}" />
    {% endfor %}
    
    {# Set the filename (optional) #}
    <input type="hidden" name="filename" value="test.pdf" />

    {# Redirect to the PDF URL (optional) #}
    <input type="hidden" name="redirect" value="1" />

    <input type="submit" value="Create PDF" />
</form>

```

Brought to you by [Steve Rowling](https://springworks.co.uk)
