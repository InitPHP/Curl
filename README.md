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
- `timeout_ms` : Integer. Maximum microseconds to wait for URL to respond. A number of 0 or less means there is no limit. Its default value is `0`. It is not used if it is defined in seconds with "`timeout`".
- `ssl` : Boolean. Defines whether the request will be made over SSL. Its default value is `true`.
- `proxy` : Defines the proxy to use. Its default value is `null`

### `setParams()`

Adds parameters.

```php
public function setParams(array $params = []): self
```

### `setUserAgent()`

Defines the `\CURLOPT_USERAGENT` information for curl.

```php
public function setUserAgent(null|string $userAgent = null): self;
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

### `getInfo()`

It is used to get the value or values returned from the `curl_getinfo()` function.

```php
public function getInfo(null|string $key = null): null|array|string|int|float;
```

The array to return is as follows. The following array keys can be used for the `$key` parameter.

```php
Array
(
    [url] => https://example.com
    [content_type] => text/html; charset=utf-8
    [http_code] => 200
    [header_size] => 417
    [request_size] => 74
    [filetime] => -1
    [ssl_verify_result] => 0
    [redirect_count] => 0
    [total_time] => 0.572835
    [namelookup_time] => 0.0598
    [connect_time] => 0.116755
    [pretransfer_time] => 0.357189
    [size_upload] => 0
    [size_download] => 650
    [speed_download] => 1134
    [speed_upload] => 0
    [download_content_length] => -1
    [upload_content_length] => 0
    [starttransfer_time] => 0.572509
    [redirect_time] => 0
    [redirect_url] => 
    [primary_ip] => 192.0.78.24
    [certinfo] => Array
        (
        )

    [primary_port] => 443
    [local_ip] => 192.168.8.134
    [local_port] => 53807
    [http_version] => 2
    [protocol] => 2
    [ssl_verifyresult] => 0
    [scheme] => HTTPS
    [appconnect_time_us] => 357122
    [connect_time_us] => 116755
    [namelookup_time_us] => 59800
    [pretransfer_time_us] => 357189
    [redirect_time_us] => 0
    [starttransfer_time_us] => 572509
    [total_time_us] => 572835
)
```

### `getError()`

if cURL encounters an error; gives the error.

```php
public function getError(): null|string;
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
