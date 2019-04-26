=== NIF (Num. de Contribuinte Português) for WooCommerce ===
Contributors: webdados
Tags: woocommerce, ecommerce, e-commerce, nif, nipc, vat, tax, portugal, webdados
Author URI: https://www.webdados.pt
Plugin URI: https://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/nif-de-contribuinte-portugues-woocommerce-wordpress/
Requires at least: 4.7
Tested up to: 5.0.3
Stable tag: 4.2

This plugin adds the Portuguese NIF/NIPC as a new field to WooCommerce checkout and order details, if the billing address / customer is from Portugal.

== Description ==

This plugin adds the Portuguese VAT identification number (NIF/NIPC) as a new field to WooCommerce checkout and order details, if the billing address is from Portugal.

= Are you already issuing automatic invoices on your WooCommerce store? =

If not, get to know our new plugin: [Invoicing with InvoiceXpress for WooCommerce](https://wordpress.org/plugins/woo-billing-with-invoicexpress/)

= Features: =

* Adds the Portuguese VAT identification number (NIF/NIPC) to the WooCommerce Checkout fields, Order admin fields, Order Emails and "Thank You" page;
* It's possible to edit the customer's NIF/NIPC field on "My Account - Billing Address" and on the User edit screen on wp-admin.
* NIF/NIPC check digit validation (if activated via filter)

== Installation ==

* Use the included automatic install feature on your WordPress admin panel and search for "NIF WooCommerce".

== Frequently Asked Questions ==

= How to make the NIF field required? =

Just add this to your theme's functions.php file (v3.0 and up):

`add_filter( 'woocommerce_nif_field_required', '__return_true' );`

= Is it possible to validate the check digit in order to ensure a valid Portuguese NIF/NIPC is entered by the customer? =

Yes, it is! Just add this to your theme's functions.php file (v3.0 and up):

`add_filter( 'woocommerce_nif_field_validate', '__return_true' );`

We only recommend validating the NIF if your shop only sells to Portugal.

= Is this plugin compliant with the new EU General Data Protection Regulation (GDPR)? =

First of all, it's the website owner responsibility to make your whole website compliant because no personal details whatsoever are transmitted to us, the plugin developers.
Anyway, we can help you by documenting how this plugin handles the collected data:
* The NIF/NIPC field is collected via the checkout process and stored as an order meta value, alongside with all the other WooCommerce order fields;
* The NIF/NIPC field can also be set on the "My Account - Billing Address" form and it's then stored as a user meta value, alongside with all the other WordPress and WooCommerce user fields;
* The NIF/NIPC field is shown and editable on the order edit and user edit screens on the backend, by the store owner;
* The NIF/NIPC field is shown on the order transactional emails;

== Changelog ==

= 4.2 =
* Add NIF to the WooCommerce REST `orders` and `customers` endpoints

= 4.1.3 =
* Fixed a fatal error when the `woocommerce_nif_field_validate` was set to true and the customer doesn't have a country associated yet
* Tested with WooCommerce 3.5.5 and WordPress 5.0.3

= 4.1.2 =
* Tested with WooCommerce 3.5.2
* Bumped `WC tested up` tag
* Bumped `Requires at least` tag

= 4.1.1 =
* Tested with WooCommerce 3.5
* Bumped `WC tested up` tag
* Bumped `Requires at least` tag

= 4.1 =
* Using `WC()->customer->get_billing_country()` instead of `WC()->customer->get_country()` on WooCommerce 3.0 and above

= 4.0 =
* Toggle the NIF field via javascript on the checkout page by default - if you need to get back to the old mechanism, you should false on the `woocommerce_nif_use_javascript` filter
* New `woocommerce_nif_show_all_countries` to show the field for all countries instead of only to portuguese customers (not recommended)
* Tested with WooCommerce 3.3
* Filters examples updated
* Improved the FAQ

= 3.3 =
* Experimental javascript toggle of the NIF field on the checkout page, so that the field is shown or hidden depending on the billing country selection (by returning true on the `woocommerce_nif_use_javascript` filter) - this will probably be default on a later version, after heavy user testing
* GDPR information
* Improved the FAQ

= 3.2 =
* Fixed a bug where if the validation is active but the field is not required, the checkout wouldn't go ahead if the client didn't fill the field
* Validation of the field on the "My Account - Billing Address" form
* Bumped `Tested up to` and `WC tested up to` tags
* Better code formatting standards
* Improved the FAQ

= 3.1 =
* Removed the translation files from the plugin `lang` folder (the translations are now managed on WordPress.org's GlotPress tool and will be automatically downloaded from there)
* Tested with WooCommerce 3.2
* Added `WC tested up to` tag on the plugin main file
* Bumped `Tested up to` tag

= 3.0 =
* It's now possible to validate the Portuguese NIF/NIPC check digit entered by the customer (by returninig true on the `woocommerce_nif_field_validate` filter)
* Tested with WooCommerce 3.0.0-rc.2
* Changed version tests from 2.7 to 3.0
* New `autocomplete` parameter set to 'on'
* New `priority` parameter set to '120'
* New `maxlength` parameter set to '9'
* New filters to manipulate the field `label`, `placeholder`, `required`, `class`, `clear`, `autocomplete` and `maxlength` parameters (check filters_examples.php)
* Bumped `Tested up to` tag

= 2.1 =
* WooCommerce 2.7 compatibility
* NIF/NIPC is also shown and editable, in admin, on the user edit screen (alongside with other Billing Address fields)

= 2.0.2 =
* Fix typos in the readme.txt file (Thanks Daniel Matos)

= 2.0.1 =
* Bumped `Tested up to` and `Requires at least` tags

= 2.0 =
* Completely rewritten
* NIF/NIPC is added to the Billing Address fields on the Checkout (as long as the customer country is Portugal)
* You can also edit the user NIF/NIPC on the "My Account - Billing Address" form
* NIF/NIPC is also shown and editable on the order screen (alongside with other Billing Address fields)
* NIF/NIPC is added to the Customer Details section on Emails and Thank You page.

= 1.3 =
* Adds the field to the My Acccount / Edit Billing Address form

= 1.2.2 =
* The value is now auto-filled with the last one used

= 1.2.1 =
* Small fix to avoid php notices

= 1.2 =
* WordPress Multisite support

= 1.1.1 =
* Forgot to update version number on the php file.

= 1.1 =
* Bug fix after WooCommerce 2.1 changes.

= 1.0 =
* Initial release.