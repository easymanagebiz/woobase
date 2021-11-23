=== Easymanage ===
Contributors: easymanagebiz
Donate link: https://easymanage.biz/index.php/donate/
Tags: Google Sheet, woocommerce, import products, email, export products, manage products, export customers, Google App Sript, woocommerce to google sheet
Requires at least: 5.2.0
Tested up to: 5.7.2
Requires PHP: 7.0
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

Everybody knows Google Drive power and how to work with documents there, eh? One of the most great thing – its that it let you keep your documents, in one place, access from anywhere and share with anybody.
Try for free our ecommerce solution for Woocommerce. Its build with awesome tool – Google App Script. All your changes done in Google Spreadsheet table, and from there you can update web store products.

Basically it needs two extensions installed. One is for Google Sheet (App script addon), another for Woocommerce. App script addon update spreadsheet functionality and send-fetch data from-to web store, directly through Woocommerce REST API. Woocommerce extension its some kind of optimization for search, update products, create products, get email content etc.

**Features**

- Quickly update price, special price, status, qty and status of your products.
- Export customers, update them when newly registered, collect their emails which can be used in marketing emails.
- Mail merge - create personalized emails per each customer, send them from your Gmail account, so they arrive in your customers Inbox for sure.
- Manage unsubscribed users.
- Update any product.
- Import products
- Create different types of product
- Update products images
- Update products custom options

= Minimum Requirements =

* WordPress 5.2
* WooCommerce 3.6.0 or greater
* PHP version 7.0 or greater. PHP 7.2 or greater is recommended
* MySQL version 5.0 or greater. MySQL 5.6 or greater is recommended

Visit the [WooCommerce server requirements documentation](https://docs.woocommerce.com/document/server-requirements/) for a detailed list of server requirements.

= Automatic installation =

Automatic installation is the easiest option, as WordPress handles the file transfers and you don't need to leave your web browser. To perform an automatic install:
1. Log in to your WordPress dashboard.
2. Go to: Plugins > Add New.
3. Enter “Easymanage” in the Search field, and view details about its point release, the rating and description.
4. Select “Install Now” when you’re ready

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your web server via your favorite FTP application. The WordPress Codex contains instructions at [Manual Plugin Installation](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Getting Started ==

Visit the [Easymanage installation](https://easymanage.biz/index.php/woocommerce-woo/) for a detailed steps how to use it.
Documentation [Google docs](https://docs.google.com/document/d/1XYKxhMBHxng4VYdRcTmyT86cE5nG61XPq7OLr_vZ434/edit?usp=sharing).
Our [Support forum](https://easymanage.biz/index.php/forum/forum/woocommerce-app/)
And [GitHub repository](https://github.com/easymanagebiz/woobase)

== Frequently Asked Questions ==

= Is plugin free to try? =
Yes, it's free to try ;-) It's also free for use on any numbers of websites, and during any period.

== Changelog ==

= 1.0.2 =
* Sanitize consumer_key and consumer_secret
* Validate products data type before update
* Clean email template HTML with unexpected tags or attributes
* Sanitize email template subject
* Prepare products data output with type

== 1.0.3 ==
* Add trigger table setup
* Trigger REST endpoint URL
* Create triggers insert row
* UI component type HTML, link, select, calendar

== 1.0.3.1 ==
* Add Polylang Woocommerce module support
* Change get sku from csv row method
* Logo update

== 1.0.4 ==
* Polylang compatible get Product ID by SKU

== 1.0.5 ==
* Paginate while fetching products(500 per one query)
* About extension - admin page
* Fix meta: values export
* Fix line-break chars in description
* Add update / create mode from spreadsheet


== Screenshots ==

1. Export products from Woocommerce to Google Sheat
2. Import products from Google Sheat to Woocommerce
3. Send emails from Google Gmail to Woocustomers
