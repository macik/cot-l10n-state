L10N-State
==========

Plugin for Cotonti CMF. 

Description
-----------

Check original page text and it's localized version (by i18n plugin) and 
determines conformness of translation.


Features
--------

* just add widget to localized pages
* can be extended to fine tune for certain language

Requirements
------------

Cotonti `Siena.0.9.18` and `i18n` plugin

### Compatibility

Primarily its designed to test Russian localization of Cotonti Doc pages, so 
other languages uses unified algorithm and should be extended for better results.
See `How extension works` for details.

### Comments

Plugin works out from the box. For better results 
You can setup `Cache TTL` parameter

### How extension works

In spite of extension can not really check translation quality of page localization 
we can test it against some metrics, like text size and inner structure.

Common conform algorithm checks word counts in origin text and its translated version,
counts code samples (PRE tags) in page and checks headings structure (H1,H2,H3 tags). 

Certain comparison method can be extended for any language by adding `inc/l10m-state.##.php`
file, where `##` is language code. See example of `l10m-state.ru.php` file.

Install
-------

* Unpack, copy files to plugin specific folder of your site.
* Install via Admin → Extensions menu (`Administration panel → Extensions`)
* Checks setting in config (`Administration panel → Extensions → l10n-state → Configuration`).

### Comments

To see this Extension in action - add this code sample somewhere to your `page.tpl`:
```HTML
<!-- IF {PHP.l10n_state} -->
	{PHP.pag|l10n_state_widget($this)}
<!-- ENDIF -->
```

References
----------

* [Cotonti.com](http://Cotonti.com/) -- Home of Cotonti CMF


