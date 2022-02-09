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
    <input type="hidden" name="action" value="pdf-maker/pdf/pdf-from-url">
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
    <input type="hidden" name="action" value="pdf-maker/pdf/pdf-from-html">
    <input type="hidden" name="html" value="<p>HTML content for PDF</p>" />
    
    {# Set the filename (optional) #}
    <input type="hidden" name="filename" value="test.pdf" />

    {# Redirect to the PDF URL (optional) #}
    <input type="hidden" name="redirect" value="1" />

    <input type="submit" value="Create PDF" />
</form>

```

### Generate PDF from Template

To generate a PDF from a template, set the hidden `template` input to the hashed value of the path to the template to render. The path is hashed to prevent it being tempered with in the browser. For example, if you wanted to use a template called `page`, which lived in a folder called `_pdfs` in your `templates` folder, you would set the value of the hidden `template` to `{{ '_pdfs/page'|hash }}`.

You can pass variables into the template by setting them as hidden `variables[variableName]` inputs. Each of these values must also be hashed. So, for example, if your template required an `entryId` to tell it what entry to render, you could pass that in like this:

`<input type="hidden" name="variables[entryId]" value="{{ entry.id|hash }}" />`

Full example:

```twig
<form method="post" action="" accept-charset="UTF-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="pdf-maker/pdf/pdf-from-template">
    <input type="hidden" name="template" value="{{ 'path/to/template'|hash }}" />
    <input type="hidden" name="variables[entryId]" value="{{ entry.id|hash }}" />
    <input type="hidden" name="variables[someVariable]" value="{{ 'value'|hash }}" />
    
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
