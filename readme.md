# Gravity Forms Fieldsets
**Contributors:** Mike Kormendy, basvandenwijngaard, HarroH.<br>
**Tags:** gravity forms, forms, fieldset, wrapper, gravity forms styling, groups<br>
**Requires at least:** 4.2<br>
**Tested up to:** 4.7<br>
**Stable tag:** 0.3.5<br>
**License:** GPLv2<br>
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html<br>

Gravity Forms Fieldsets Add-On for grouping fields inside an HTML5 fieldset. Adapted from <a href="https://wordpress.org/plugins/gravity-forms-fieldsets/" target="_blank">Gravity Fieldset for Gravity Forms</a> by Bas van den Wijngaard & Harro Heijboer.


## Description

This plugin is an add-on for <a href="http://www.gravityforms.com" target="_blank">Gravity Forms</a>.

Features of this plugin:

* Adding real fieldsets around your form fields
* Includes option for fieldset legend
* Autocomplete of fieldsets to properly close all HTML tags
* Auto-delete of fieldsets to properly close all HTML tags

Available Languages:

* English
* Dutch
* German

*Gravity Forms Fieldsets is inspired by the post 'Tips on Making Your Gravity Forms as Accessible as Possible' by Cynthia Ng. You can read the whole post <a href="https://cynng.wordpress.com/2014/02/26/tips-on-making-your-gravity-forms-as-accessible-as-possible/" target="_blank">here</a>. Thanks to ovann86 and his <a href="https://nl.wordpress.org/plugins/gravity-forms-infobox-field/" target="_blank">infobox plugin</a> for gravity forms for borrowing the base plugin framework.*

*Version 0.2 and onwards improved the HTML output to proper fieldsets instead of the closing list item and closing list hack proposed by Cynthia Ng.*


## Installation

1. This plugin requires that the Gravity Forms plugin be installed and activated
2. Upload folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in the WordPress administration
4. Your fieldsets are now available in your form editor


## Screenshots

1. Two new form field types will be added to your form.
2. The plugin will automatically close or open the fieldset.
3. Fields can be added within the fieldset.


## Changelog

### 0.3.5
* Code refactoring to unbrand the plugin from "raak" references (since no longer maintained)
* Added support for Gravity Forms 2.5 new markup structure
* Added icons for fieldset begin/end in admin editor
* Fix: Longstanding Javascript error when deleting fieldset end, now deleting is quicker too

### 0.3.1
* Fix: enable fieldset when loading form through AJAX

### 0.3
* Forked

### 0.2.2
* Fix: Delete closing fieldset (attempt failed, see v0.3.5)

### 0.2.1
* Fix: Text Domain

### 0.2
* Added support for custom classes in fieldset end
* Major improvement in HTML output
* Fix: Compatability issue with Gravity PDF plugin
* Fix: compatability issue with WCAG 2.0 form fields for Gravity Forms plugin
* Fix: Compatability issue with Gravity Forms Repeater Add-On plugin

**PLEASE NOTE:**

 * class `.gform_fieldset_end` has been removed
 * class `.gform_fieldset_begin` will be deprecated in the next release

### 0.1

* Cleanup
* Added comments
* Improved English and Dutch Language
* Added auto close or open when adding fieldset begin or end
* Added auto delete of fieldset end when deleting fieldset begin

### 0.0.1

* First release