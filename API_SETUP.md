# API Server Setup Guide — api.nerace.in

## Requirements

| Requirement     | Version       |
|-----------------|---------------|
| PHP             | 7.4 – 8.2     |
| PostgreSQL      | 12+           |
| Web Server      | Apache / Nginx |
| PHP Extensions  | pgsql, pdo_pgsql, curl, mbstring, json |

---

## Step 1 — Clone / Upload Project

Upload the project to your server root. Example paths:

- **Apache (cPanel):** `/home/<user>/public_html/api.nerace.in/`
- **Linux VPS:** `/var/www/html/api.nerace.in/`
- **Windows IIS:** `C:\inetpub\wwwroot\api.nerace.in\`

---

## Step 2 — Database Configuration

**File:** `application/config/database.php`

```php
$db_hostname = '<YOUR_DB_HOST>';       
$db_username = '<YOUR_DB_USER>';       
$db_password = '<YOUR_DB_PASSWORD>';  
$db_database = 'nerace';              

### Appname → Database Mapping

The app reads the `appname` HTTP header and maps it to a database name.
Update this block to match your environment:

```php
if (isset($_SERVER['HTTP_APPNAME']) && !empty($_SERVER['HTTP_APPNAME'])) {
    $db_database = $_SERVER['HTTP_APPNAME'];
    if ($db_database === 'nerace') {
        $db_database = 'nerace';
    }
}
```

Add more mappings as needed:
```php
if ($db_database === 'your_appname') {
    $db_database = 'your_actual_db_name';
}
```

### Secondary DB Connections

Also update credentials in the `master_db` and `master` connection groups at the bottom of the same file:

```php
$db['master_db'] = array(
    'hostname' => '<YOUR_DB_HOST>',
    'username' => '<YOUR_DB_USER>',
    'password' => '<YOUR_DB_PASSWORD>',
    'database' => 'master',
    ...
);
$db['master'] = array(
    'hostname' => '<YOUR_DB_HOST>',
    ...
);
```

---

## Step 3 — Application Configuration

**File:** `application/config/config.php`

| Config Key | Description | Current Value |
|---|---|---|
| `$config['PORTAL_LINK']` | Frontend portal URL |
| `$config['SITE_LINK']` | Site URL | 
| `$config['curl_api_key']` | Internal API key (base64) | 
| `$config['crm_email']` | CRM notification email |
| `$config['gprivatekey']` | Google reCaptcha private key | 
| `$config['gpublickey']` | Google reCaptcha public key |
| `$config['sms_login_id']` | SMS gateway login | 
| `$config['sms_password']` | SMS gateway password | 
| `$config['sms_senderid']` | SMS sender ID |
| `$config['sms_ip']` | SMS gateway IP | 
| `$config['sms_url']` | SMS gateway URL |
| `$config['encryption_key']` | Session encryption key | 

Update the `switch (ENVIRONMENT)` block for your environment:

```php
switch (ENVIRONMENT) {
    case 'production':
        $config['PORTAL_LINK'] = 'https://<YOUR_PORTAL_DOMAIN>/';
        $config['SITE_LINK']   = 'https://<YOUR_PORTAL_DOMAIN>/';
        break;
    default:
        $config['PORTAL_LINK'] = 'https://<YOUR_PORTAL_DOMAIN>/';
        $config['SITE_LINK']   = 'https://<YOUR_PORTAL_DOMAIN>/';
        break;
}
```

---

## Step 4 — Constants Configuration

**File:** `application/config/constants.php`

| Constant | Description | Current Value |
|---|---|---|
| `API_BASE_PATH` | This API's public base URL | 
| `BASE_PATH_PORTAL` | Frontend portal base URL |
| `PARTNER_URL` | Partner login URL |
| `UPLOAD_ROOT_FOLDER` | Server upload root folder | 
| `CODE` | App identifier code | 

Update these to match your new server:

```php
define('API_BASE_PATH',      'https://<YOUR_API_DOMAIN>/api/v16/');
define('BASE_PATH_PORTAL',   'https://<YOUR_PORTAL_DOMAIN>/');
define('PARTNER_URL',        'https://<YOUR_PORTAL_DOMAIN>/partner/login/index');
define('UPLOAD_ROOT_FOLDER', '<SERVER_UPLOAD_ROOT_FOLDER>');
```

### Payment Gateway Keys

```php
// Razorpay
define('KEYID_TEST',     '<RAZORPAY_TEST_KEY_ID>');
define('KEYSECRET_TEST', '<RAZORPAY_TEST_KEY_SECRET>');
define('KEYID_LIVE',     '<RAZORPAY_LIVE_KEY_ID>');
define('KEYSECRET_LIVE', '<RAZORPAY_LIVE_KEY_SECRET>');

// Paytm
define('PAYTM_MERCHANT_KEY_TEST', '<PAYTM_MERCHANT_KEY>');
define('PAYTM_MERCHANT_MID_TEST', '<PAYTM_MERCHANT_MID>');
define('PAYTM_MERCHANT_WEBSITE_TEST', 'WEBSTAGING');  // or 'DEFAULT' for live
```

---

## Step 5 — Payment Config

**File:** `payment/config.php`

Update Razorpay / Paytm credentials specific to the payment flow.

---

## Step 6 — Web Server Configuration

### Apache

The `.htaccess` file is already present. Ensure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
```

Update allowed CORS origins in `.htaccess`:

```apache
SetEnvIf Origin "https://<YOUR_PORTAL_DOMAIN>$" CORS_ALLOWED_ORIGIN
SetEnvIf Origin "https://<YOUR_API_DOMAIN>$"    CORS_ALLOWED_ORIGIN
SetEnvIf User-Agent "Postman" CORS_ALLOWED_ORIGIN
```

Set PHP handler to your installed PHP version:

```apache
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php82 .php .php8 .phtml
</IfModule>
```

### Nginx

Add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### PHP Built-in Server (local dev only)

```bash
php -S localhost:8080 router.php
```

> `router.php` is included in the project root for local development only. Do NOT use on production.

---

## Step 7 — Environment Variable

Set the CI environment in your web server config or `.env`:

**Apache (VirtualHost):**
```apache
SetEnv CI_ENV production
```

**Nginx (fastcgi_params):**
```nginx
fastcgi_param CI_ENV production;
```

**Local dev** — leave unset (defaults to `development`).

---

## Step 8 — Folder Permissions

```bash
chmod -R 755 application/cache/
chmod -R 755 application/logs/
chmod -R 755 uploads/          # if exists
```

---

## Step 9 — PHP 8.2 Compatibility Fixes

These fixes are already applied in this codebase. If deploying to a **fresh CI3 copy**, reapply them manually:

| File | Fix |
|---|---|
| `system/core/URI.php` | Add `public $config;` property to `CI_URI` class |
| `system/core/Router.php` | Add `public $uri;` property to `CI_Router` class |
| `system/core/Controller.php` | Add `#[\AllowDynamicProperties]` above `CI_Controller` class |
| `system/core/Loader.php` | Add `#[\AllowDynamicProperties]` above `CI_Loader` class |
| `system/database/DB_driver.php` | Add `#[\AllowDynamicProperties]` above `CI_DB_driver` class |
| `application/helpers/common_helper.php` line 2657 | Change `$product_id, $user_id, $min_frequency` to `$product_id=null, $user_id=null, $min_frequency=null` |
| `application/helpers/log_helper.php` line 128 | Change `$user_type` to `$user_type=null` |
| `application/controllers/api/v16/Users.php` line 1943, 1963 | Change `!= f)` to `!= 'f')` |
| `application/controllers/api/v16/Users.php` line 11574 | Change `header()` to `getallheaders()` |
| `application/controllers/api/v16/Users.php` line 29771 | Change `$nutrition_management` to `$nutrition_management=null` |
| `application/controllers/api/v16/Buyer.php` line 309, 354 | Change `!= f)` to `!= 'f')` |
| `application/controllers/api/v16/Vendor.php` line 711 | Change `!= f)` to `!= 'f')` |

---

## Step 10 — Verify Setup

Test the API is running:

```bash
# Health check
curl http://<YOUR_API_DOMAIN>/api/v16/users

# Test endpoint
curl --location 'http://<YOUR_API_DOMAIN>/api/v16/users/is_user_regsitered' \
  --header 'appname: nerace_dev' \
  --header 'domain: nerace_dev' \
  --header 'lang: en' \
  --header 'x-api-key: CODEX@123' \
  --data-urlencode 'phone=<TEST_PHONE_NUMBER>'
```

Expected response: `{"success":1,"error":0,...}` or `{"success":1,"error":0,"is_registered":0,...}`

---

## Quick Reference — Files to Change Per Deployment

```
api.nerace.in/
├── application/
│   └── config/
│       ├── database.php       ← DB host, user, password, database name, appname mapping
│       ├── config.php         ← Portal URLs, SMS keys, reCaptcha keys, encryption key
│       └── constants.php      ← API URL, portal URL, upload folder, payment keys
├── payment/
│   └── config.php             ← Payment gateway credentials
├── .htaccess                  ← CORS origins, PHP handler version
└── router.php                 ← Local dev only (delete on production)
```
