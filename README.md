# InitPHP Curl

Curl library to help you make advanced HTTP requests with PHP.

[![Latest Stable Version](http://poser.pugx.org/initphp/curl/v)](https://packagist.org/packages/initphp/curl) [![Total Downloads](http://poser.pugx.org/initphp/curl/downloads)](https://packagist.org/packages/initphp/curl) [![Latest Unstable Version](http://poser.pugx.org/initphp/curl/v/unstable)](https://packagist.org/packages/initphp/curl) [![License](http://poser.pugx.org/initphp/curl/license)](https://packagist.org/packages/initphp/curl) [![PHP Version Require](http://poser.pugx.org/initphp/curl/require/php)](https://packagist.org/packages/initphp/curl)

## Requirements

- PHP 7.4 or higher
- PHP Curl Extension
- PHP JSON Extension

## Installation

```
composer require initphp/curl
```

## Usage

```php
require_once "vendor/autoload.php";
use \InitPHP\Curl\Curl;

$curl = new Curl();

$curl->init('https:://www.muhammetsafak.com.tr');
$curl->exec();
$curl->close();

$content = ($curl->getResponse('body'));
$curl->clear();
echo $content;
```

This library can be used as HTTP client for your API service. Example:

```php
require_once "vendor/autoload.php";
use \InitPHP\Curl\Curl;

$curl = new Curl();

$curl->setMethod('PUT')
    ->setHeader('Content-Type', 'application/json')
    ->setBody(json_encode([
        'username'      => 'admin',
        'mail'          => 'admin@example.com',
        'password'      => '123456',
    ]))
    ->init('http://api.service.example.com/update/1');
$curl->exec();

$res = $curl->getResponse();
$curl->clear();

/**
 * HTTP Response Version and Status Code
 * @var string $status
 */
$status = $res['status'];

/**
 * Response HTTP Status Code
 * @var int $code
 */
$code = $res['code'];

/**
 * Response HTTP Version Status
 * @var string $version
 */
$version = $res['version'];

/**
 * HTTP Response Headers
 * @var array $headers
 */
$headers = $res['headers'];

/**
 * HTTP Response Body
 * @var string $body
 */
$body = $res['body'];
```

## Methods

### `init()`

Initializes CURL for a URL.

```php
public function init(string $url): self
```

### `setHeader()`

Defines a header for the HTTP request.

```php
public function setHeader(string $name, string $value): self
```

### `setBody()`

Defines the body of the request.

```php
public function setBody(string $body): self
```

### `setMethod()`

Defines the request method.

```php
public function setMethod(string $method = 'GET'): self
```

`GET`, `POST`, `PUT`, `HEAD`, `PATCH`, `DELETE` or `OPTIONS`

### `setProtocol()`

Defines the http protocol to use.

```php
public function setProtocol(string $protocol = '1.1'): self
```

`1.0`, `1.1` or `2.0`

### `setFile()`

If request contains a file, it reports the file body.

```php
public function setFile(?string $fileBody): self
```

### `setOption()`

Defines the value of the specified element from the options array.

```php
public function setOption(string $key, null|string|int|bool $value): self
```

The elements of the array of options are described below.

- `allow_redirects` : Boolean. If the destination URL is redirecting; Defines whether the redirect is to be followed. Its default value is `false`.
- `max_redirects` : Integer. If URL redirects are to be followed, it defines the maximum number of redirects to follow. Its default value is `3`.
- `timeout` : Integer. Maximum seconds to wait for URL to respond. A number of 0 or less means there is no limit. Its default value is `0`.
- `ssl` : Boolean. Defines whether the request will be made over SSL. Its default value is `true`.
- `proxy` : Defines the proxy to use. Its default value is `null`

### `setParams()`

Adds parameters.

```php
public function setParams(array $params = []): self
```

### `getResponse()`

Returns an array containing the response information.

```php
public function getResponse(null|string $case = null): null|array|string|int
```

The array to return is as follows. The following array keys can be used for the `$case` parameter.

```php
array(
    'status'    => 'HTTP/1.1 200 OK',
    'code'      => 200,
    'version'   => '1.1',
    'headers'    => [
        'Content-Type: application/json',
        // ...
    ],
    'body'      => '...'
)
```

### `exec()`

Executes CURL.

```php
public function exec(): bool
```

### `clear()`

CURL closes and loads the class properties to their default value.

```php
public function clear(): self
```

### `close()`

Closes the current CURL.

```php
public function close(): void
```

### `setOpt()`

Defines an options with the `curl_setopt()` function for the current CURL.

```php
public function setOpt($key, $value): self
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
