# InitPHP Curl

Curl library to help you make advanced HTTP requests with PHP.

[![Latest Stable Version](http://poser.pugx.org/initphp/curl/v)](https://packagist.org/packages/initphp/curl) [![Total Downloads](http://poser.pugx.org/initphp/curl/downloads)](https://packagist.org/packages/initphp/curl) [![Latest Unstable Version](http://poser.pugx.org/initphp/curl/v/unstable)](https://packagist.org/packages/initphp/curl) [![License](http://poser.pugx.org/initphp/curl/license)](https://packagist.org/packages/initphp/curl) [![PHP Version Require](http://poser.pugx.org/initphp/curl/require/php)](https://packagist.org/packages/initphp/curl)

## Requirements

- PHP 7.4 or higher
- PHP Curl Extension

## Installation

```
composer require initphp/curl
```

## Usage

```php
require_once "vendor/autoload.php";
use \InitPHP\Curl\Curl;

$curl = new Curl();
$curl->setUrl("https://example.com");
$curl->handler();
    
$res = $this->getResponse();

if(!empty($curl->getError())){
    die($curl->getError());
}
echo $res['body'];
```

This library can be used as HTTP client for your API service. Example:

```php
require_once "vendor/autoload.php";
use \InitPHP\Curl\Curl;

$curl = Curl::client('PUT', 'http://api.service.example.com/update/1')

$curl->setVersion("1.1") // HTTP Version
    ->setHeader("Content-Type", "application/json")
    ->setBody(json_encode([
        'username'  => 'admin',
        'password'  => '12345',
    ]))
    ->handler();

if(!empty($curl->getError())){
    die($curl->getError());
}

switch ($curl->getResponse('code')) {
    case 200 :
    case 201 :
        // Success
        break;
    case 404 :
        // Not Found
        break;
    case 400:
        // Badrequest
        break;
    // ...
}
```

## Methods

### `client()`

Creates a new client object.

```php
public static function client(string $method, string $url): \InitPHP\Curl\Curl;
```

### `getResponse()`

Returns the result of the curl operation.

```php
public function getResponse(null|string $key = null): null|mixed;
```

The values that can be used for the `$key` parameter and its explanation are explained below.

- `NULL` : Returns an associative array containing all response information.
- `"status"` : Returns the status header information line of the HTTP response.
- `"code"` : Returns the HTTP response code.
- `"version"` : Returns HTTP response version information.
- `"headers"` : Returns the headers of the HTTP response as an array.
- `"body"` : Returns the body of the HTTP response.

### `setUrl()`

Defines URL information for cURL.

```php
public function setUrl(string $url): self
```

Throws `\InvalidArgumentException` if it is not a valid URL.

### `setHeader()`

Adds a header for the request.

```php
public function setHeader(string $name, string $value): self
```

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setHeader('Content-type', 'application/json; charset=utf8');
```

### `setHeaders()`

For the request; Adds one or more headers via an associative array.

```php
public function setHeaders(array $headers): self
```

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setHeaders([
    'Content-type' => 'application/json; charset=utf8'
]);
```

### `setMethod()`

Defines the HTTP Request method.

```php
public function setMethod(string $method = 'GET'): self
```

The `$method` parameter can take the following values;

- GET, POST, PUT, HEAD, DELETE, PATCH, OPTIONS

### `setBody()`

HTTP Request is used to manually define the content information. 

```php
public function setBody(string $body): self
```

### `setVersion()`

Defines the HTTP version of the HTTP request.

```php
public function setVersion(string $httpVersion = '1.1'): self
```

The `$httpVersion` parameter can take the following values;

- `1.0`
- `1.1`
- `2.0` (libcurl v7.33 or higher version is required)

### `setUserAgent()`

Defines the User Agent information to be reported to the server in the cURL process.

```php
public function setUserAgent(null|string $userAgent = null): self
```

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setUserAgent("Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0");
```

### `setReferer()`

Defines HTTP referer information to be reported to the server.

```php
public function setReferer(null|string $referer = null): self
```

### `setAllowRedirect()`

Defines whether requests follow redirects. If this method is not used, the redirects are not followed.

```php
public function setAllowRedirect(int $maxRedirect = 3): self
```

### `setTimeout()`

Defines the wait limit for the request in seconds or milliseconds.

```php
public function setTimeout(int $timeout = 0, bool $isMicrosecond = false): self
```

If `$isMicrosecond` is FALSE, the value is used as seconds.

### `setField()`

Defines a data to be sent as POST.

```php
public function setField(string $key, string $value): self
```

### `setFields()`

It defines the data to be sent by POST with an associative array.

```php
public function setFields(array $fields) : self
```

### `setSSL()`

Defines the SSL configurations of the cURL request.

```php
public function setSSL(int $host = 2, int $verify = 1, null|int $version = null): self
```

### `setProxy()`

Defines the proxy to be used by cURL.

```php
public function setProxy($proxy): self
```

### `setUpload()`

It sends a file to be uploaded to the server.

```php
public function setUpload(string $name, \CURLFile $file): self
```

**Example (Single):**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setUpload("upload_file", new \CURLFile(__DIR__ . 'image.jpg')); // $_FILES["upload_file"]
```

**Example (Multi):**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setUpload("upload_file[0]", new \CURLFile(__DIR__ . 'image-1.jpg'));
$curl->setUpload("upload_file[1]", new \CURLFile(__DIR__ . 'image-2.jpg'));
```

### `getInfo()`

It is the `curl_getinfo()` function in this library.

```php
public function getInfo(null|string $key = null): null|mixed
```

### `getError()`

It is the `curl_error()` function in this library.

```php
public function getError(): null|string
```

### `setOpt()`

It is the `curl_setopt()` function in this library.

```php
public function setOpt(int $key, mixed $value): self
```

### `setOptions()`

It is the `curl_setopt_array()` function in this library.

```php
public function setOptions(array $options): self
```

### `handler()`

cURL starts and executes.

```php
public function handler(): bool
```

### `save()`

After cURL is handled, it writes the content to the specified file.

```php
public function save(string $filePath): false|int
```

Returns the number of bytes written on success.

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl = new \InitPHP\Curl\Curl();
$curl->setUrl("http://example.com")
        ->handler();
        
if($curl->save(__DIR__ . '/example.html') === FALSE){
    echo "The file could not be written.";
}
```

### `setCookie()`

Defines a cookie to be sent with cURL.

```php
public function setCookie(string $name, string|int|float $value): self
```

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setCookie('username', 'admin') // $_COOKIE['username']
    ->setCookie('mail', 'admin@example.com'); // $_COOKIE['mail']
```

### `setCookies()`

Defines cookies as multiple with an associative array.

```php
public function setCookies(array $cookies): self
```

**Example :**

```php
/** @var \InitPHP\Curl\Curl $curl */
$curl->setCookies([
    'username'  => 'admin', // $_COOKIE['username']
    'mail'      => 'admin@example.com' // $_COOKIE['mail']
]);
```

### `setCookieJarFile()`

It tells the file path where the cookies values to be sent to the server will be kept or kept.

```php
public function setCookieJarFile(string $filePath): self
```

If the specified file exists, cookies are read from the file and sent to the server. `CURLOPT_COOKIEFILE`

If the specified file does not exist, cookies from the server are written to the file. `CURLOPT_COOKIEJAR`

**Example :**

```php
$login = new \InitPHP\Curl\Curl();
$login->setUrl("http://example.com/user/login")
    ->setField('username', 'admin')
    ->setField('password', '123456')
    ->setMethod('POST')
    ->setCookieJarFile(__DIR__ . '/session.txt')
    ->handler();
    
$dashboard = new \InitPHP\Curl\Curl();
$dashboard->setUrl("http://example.com/user/dashboard")
    ->setCookieJarFile(__DIR__ . '/session.txt')
    ->handler();
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
