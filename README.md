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
use \InitPHP\Cur\Curl;

$curl = new Curl();

$curl->init('https:://www.muhammetsafak.com.tr');
$curl->exec();
$curl->close();

$content = $curl->response()['body'];
$curl->clear();
echo $content;
```

## Methods

### `init()`

Initializes CURL for a URL.

```php
public function init(string $url): self
```

### `body()`

Defines the body of the request.

```php
public function body(string $body): self
```

### `clear()`

CURL closes and loads the class properties to their default value.

```php
public function clear(): self
```

### `response()`

Returns an array containing the response information.

```php
public function response(): array
```

The array to return is as follows.

```
array(
    'status'    => 'HTTP/1.1 200 OK',
    'header'    => 'Transfer-Encoding: chunked',
    'body'      => '...'
)
```

### `method()`

Defines the request method.

```php
public function method(string $method = 'GET'): self
```

`GET`, `POST`, `PUT`, `HEAD`, `PATCH`, `DELETE` or `OPTIONS`

### `protocol()`

Defines the http protocol to use.

```php
public function protocol(string $protocol = '1.1'): self
```

`1.0`, `1.1` or `2.0`


### `options()`

Defines the value of the specified element from the options array.

```php
public function option(string $key, $value): self
```

The elements of the array of options are described below.

- `allow_redirects` : Boolean. If the destination URL is redirecting; Defines whether the redirect is to be followed. Its default value is `false`.
- `max_redirects` : Integer. If URL redirects are to be followed, it defines the maximum number of redirects to follow. Its default value is `3`.
- `timeout` : Integer. Maximum seconds to wait for URL to respond. A number of 0 or less means there is no limit. Its default value is `0`.
- `ssl` : Boolean. Defines whether the request will be made over SSL. Its default value is `true`.
- `proxy` : Defines the proxy to use. Its default value is `null`

### `params()`

Adds parameters.

```php
public function params(array $params = []): self
```

### `exec()`

Executes CURL.

```php
public function exec(): bool
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
