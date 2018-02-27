# License Key Addon (for Wordpress MVC)
--------------------------------

Transforms your Wordpress MVC project into a licensed product in seconds using WooCommerce License Keys API.

[Add-on](http://www.wordpress-mvc.com/v1/add-ons/) for [Wordpress MVC](http://www.wordpress-mvc.com/).

## Install

```bash
composer require 10quality/wpmvc-addon-license-key;
```

## Config

Then on your project's `app\config\app.php` add the following on the addons array:
```php
    'WPMVC\Addons\LicenseKey\Addon',
```

If your are using a plugin, add the following to the paths array:

---php
    'paths' => [

        'base'          => __DIR__ . '/../',
        'controllers'   => __DIR__ . '/../Controllers/',
        'views'         => __DIR__ . '/../../assets/views/',
        'log'           => get_wp_home_path() . './wpmvc/log',
        'base_file'     => 'your-plugin-folder-name/plugin.php',

    ],
---

Add an extra set of settings prior to closing the configuration array:

---php
    'license_api' => [
        'url'           => 'http://your-store.com/wp-admin/admin-ajax.php',
        'store_code'    => 'YOUR STORE CODE',
        'sku'           => 'PRODUCT SKU',
        'frequency'     => null,
        'option_name'   => 'WORDPRESS OPTION NAME OF CHOICE',
        'ck'            => 'AN ENCRYPTION KEY'
    ],
---

## Usage

### Fully licensed product

A fully licensed product will allow to restricted your entire project under a license key.

Change the following at your project's main class:

---php
use WPMVC\Bridge;
---

For:

---php
use WPMVC\Addons\LicenseKey\Core\FullyLicensedBridge as Bridge;
---

### Partially licensed product

A partially licensed product will allow you to determine which parts of your project will be restricted by a license key.

Change the following at your project's main class:

---php
use WPMVC\Bridge;
---

For:

---php
use WPMVC\Addons\LicenseKey\Core\LicensedBridge as Bridge;
---

Then call main class property `is_valid` to add restrictions in your code. An example used within a controller:
```php
class SampleController extends Controller
{
    public function example()
    {
        if ( $this->main->is_valid ) {
            // Access restricted code
        }
    }
}
```