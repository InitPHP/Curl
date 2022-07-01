<?php
/**
 * Curl.php
 *
 * This file is part of Curl.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Curl;

use InitPHP\Curl\Exception\CurlException;

class Curl
{

    public const SUPPORTED_HTTP_METHODS = [
        'GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'
    ];

    protected ?string $url = null;

    protected ?string $userInfo = null;

    protected ?string $userAgent = null;

    protected ?string $referer = null;

    protected string $method = 'GET';

    protected array $headers = [];

    protected string $version = '1.1';

    protected array $options = [];

    protected ?string $body = null;

    protected array $uploads = [];

    protected array $fields = [];

    protected bool $canFollow = false;

    protected bool $allowRedirects = false;

    protected int $maxRedirects = 3;

    protected int $timeout = 0;
    protected int $timeoutMS = 0;

    protected array $response = [
        'status'    => null,
        'version'   => null,
        'code'      => null,
        'body'      => '',
        'headers'   => []
    ];

    protected array $getInfo = [];

    protected ?string $error = null;

    /** @var null|resource|false */
    protected $curl = null;

    /** @var null|bool|string */
    protected $exec = null;

    public function __construct()
    {
        if(!\extension_loaded('curl')){
            throw new CurlException('The CURL extension must be installed.');
        }
        $this->canFollow = (!\ini_get('safe_mode') && !\ini_get('open_basedir'));
    }

    public function __debugInfo()
    {
        return [
            'url'   => $this->url
        ];
    }

    public function setUrl(string $url): self
    {
        if(\filter_var($url, \FILTER_VALIDATE_URL) === FALSE){
            throw new \InvalidArgumentException('URL address could not be verified');
        }
        $this->url = $url;
        $parse = \parse_url($url);
        $this->userInfo = $parse['user'] ?? null;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = \array_merge($this->headers, $headers);
        return $this;
    }

    public function setMethod(string $method = 'GET'): self
    {
        $method = \strtoupper($method);
        if(\in_array($method, self::SUPPORTED_HTTP_METHODS, true) === FALSE){
            throw new \InvalidArgumentException(('Request method can only be ' . \implode(', ', self::SUPPORTED_HTTP_METHODS)));
        }
        $this->method = $method;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setVersion(string $httpVersion = '1.1'): self
    {
        $supported_version = ['1.0', '1.1'];
        if(\defined('CURL_HTTP_VERSION_2_0')){
            $supported_version[] = '2.0'; //libcurl v7.33 or higher
        }
        if(\in_array($httpVersion, $supported_version, true) === FALSE){
            throw new \InvalidArgumentException(('The protocol can only be ' . \implode(', ', $supported_version)));
        }
        $this->version = $httpVersion;
        return $this;
    }

    public function setUserAgent(?string $userAgent = null): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function setReferer(?string $referer = null): self
    {
        $this->referer = $referer;
        return $this;
    }

    public function setAllowRedirect(int $maxRedirect = 3): self
    {
        if($maxRedirect < 0){
            $maxRedirect = 0;
        }
        if($this->canFollow === FALSE){
            throw new CurlException('"safe_mode" and "open_basedir" must be disabled in the server configuration to follow cURL redirects.');
        }
        $this->allowRedirects = true;
        $this->maxRedirects = $maxRedirect;
        return $this;
    }

    public function setTimeout(int $timeout = 0, bool $isMicrosecond = false): self
    {
        if($timeout < 0){
            $timeout = 0;
        }
        if($isMicrosecond){
            $this->timeoutMS = $timeout;
        }else{
            $this->timeout = $timeout;
        }
        return $this;
    }

    public function setField(string $key, string $value): self
    {
        $this->fields[$key] = $value;
        return $this;
    }

    public function setFields(array $fields) : self
    {
        $this->fields = \array_merge($this->fields, $fields);
        return $this;
    }

    public function setSSL(int $host = 2, int $verify = 1, ?int $version = null): self
    {
        $this->addCurlOption(\CURLOPT_SSL_VERIFYPEER, $verify)
            ->addCurlOption(\CURLOPT_SSL_VERIFYHOST, $host);
        if($version !== null){
            $this->addCurlOption(\CURLOPT_SSLVERSION, $version);
        }
        return $this;
    }

    public function setProxy($proxy): self
    {
        $this->addCurlOption(\CURLOPT_PROXY, $proxy);
        return $this;
    }

    public function setUpload(string $name, \CURLFile $file): self
    {
        $this->uploads[$name] = $file;
        return $this;
    }

    /**
     * @param null|string $key
     * @return null|mixed
     */
    public function getResponse(?string $key = null)
    {
        if(empty($this->response)){
            return null;
        }
        return ($key === null) ? $this->response : ($this->response[$key] ?? null);
    }

    /**
     * @param string|null $key
     * @return null|mixed
     */
    public function getInfo(?string $key = null)
    {
        if(empty($this->getInfo)){
            return null;
        }
        if($key === null){
            return $this->getInfo;
        }
        return $this->getInfo[$key] ?? null;
    }

    public function getError(): ?string
    {
        return empty($this->error) ? null : $this->error;
    }

    /**
     * @param string $filePath
     * @return false|int
     */
    public function save(string $filePath)
    {
        $content = $this->getResponse('body');
        if (empty($content)) {
            return false;
        }
        return @\file_put_contents($filePath, $content);
    }

    /**
     * @param int|string $key
     * @param mixed $value
     * @return $this
     */
    public function setOpt($key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = \array_merge($this->options, $options);
        return $this;
    }

    public function prepare(): self
    {
        $this->curlOptionsPrepare();
        return $this;
    }

    public function handler(): bool
    {
        $this->curl = \curl_init();
        try {
            if(!empty($this->options)){
                \curl_setopt_array($this->curl, $this->options);
            }
            $this->exec = \curl_exec($this->curl);
            $this->getInfo = \curl_getinfo($this->curl);
            $this->error = \curl_error($this->curl);
        }catch (\Exception $e) {
            throw new CurlException($e->getMessage());
        } finally {
            \curl_setopt_array($this->curl, [
                \CURLOPT_HEADERFUNCTION => null,
                \CURLOPT_READFUNCTION => null,
                \CURLOPT_WRITEFUNCTION => null,
                \CURLOPT_PROGRESSFUNCTION => null
            ]);
            \curl_reset($this->curl);
            \curl_close($this->curl);
            $this->curl = null;
        }
        return !empty($this->exec);
    }

    private function curlOptionsPrepare(): void
    {
        if(\defined('CURLOPT_PROTOCOLS')){
            $this->addCurlOption(\CURLOPT_PROTOCOLS, (\CURLPROTO_HTTP | \CURLPROTO_HTTPS))
                ->addCurlOption(\CURLOPT_REDIR_PROTOCOLS, (\CURLPROTO_HTTP | \CURLPROTO_HTTPS));
        }
        $this->addCurlOption(\CURLOPT_HEADER, false)
            ->addCurlOption(\CURLOPT_FAILONERROR, false);
        $this->addCurlOption(\CURLOPT_RETURNTRANSFER, false);

        $canFollow = $this->canFollow && $this->allowRedirects;
        $this->addCurlOption(\CURLOPT_FOLLOWLOCATION, $canFollow)
            ->addCurlOption(\CURLOPT_MAXREDIRS, $this->maxRedirects);

        if($this->timeout > 0){
            $this->addCurlOption(\CURLOPT_TIMEOUT, $this->timeout);
        }elseif($this->timeoutMS > 0){
            $this->addCurlOption(\CURLOPT_TIMEOUT_MS, $this->timeoutMS);
        }

        $this->addCurlOption(\CURLOPT_CUSTOMREQUEST, $this->method)
            ->addCurlOption(\CURLOPT_URL, $this->url)
            ->addCurlOption(\CURLOPT_HTTPHEADER, $this->getRequestHeaders());
        if(!empty($this->userAgent)){
            $this->addCurlOption(\CURLOPT_USERAGENT, $this->userAgent);
        }
        if(!empty($this->referer)){
            $this->addCurlOption(\CURLOPT_REFERER, $this->referer);
        }

        switch ($this->version) {
            case '1.0':
                $version = \CURL_HTTP_VERSION_1_0;
                break;
            case '1.1':
                $version = \CURL_HTTP_VERSION_1_1;
                break;
            case '2.0':
                $version = \CURL_HTTP_VERSION_2_0;
                break;
            default:
                $version = \CURL_HTTP_VERSION_1_1;
        }
        $this->addCurlOption(\CURLOPT_HTTP_VERSION, $version);

        if(!empty($this->userInfo)){
            $this->addCurlOption(\CURLOPT_USERPWD, $this->userInfo);
        }

        switch ($this->method) {
            case 'HEAD':
                $this->addCurlOption(\CURLOPT_NOBODY, true);
                break;
            case 'GET':
                $this->addCurlOption(\CURLOPT_HTTPGET, true);
                break;
        }
        if(!empty($this->uploads)){
            $this->fields = \array_merge($this->fields, $this->uploads);
        }
        if(!empty($this->fields)){
            $this->addCurlOption(\CURLOPT_POST, true)
                ->addCurlOption(\CURLOPT_POSTFIELDS, $this->fields);
        }

        if(!empty($this->body)){
            $this->addCurlOption(\CURLOPT_POSTFIELDS, $this->body);
        }

        $this->addCurlOption(\CURLOPT_HEADERFUNCTION, function ($ch, $data) {
            $str = \trim($data);
            if(!empty($str)){
                $lowercase = \strtolower($str);
                if(\strpos($lowercase, 'http/') === 0){
                    $this->response['status'] = $str;
                    if(\preg_match("/http\/([\.0-2]+) ([\d]+).?/i", $lowercase, $matches)){
                        $this->response['version'] = $matches[1];
                        $this->response['code'] = (int)$matches[2];
                    }
                }else{
                    $this->response['headers'][] = $str;
                }
            }
            return \strlen($data);
        });

        $this->addCurlOption(\CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            $this->response['body'] .= $data;
            return \strlen($data);
        });

    }

    private function addCurlOption($key, $value): self
    {
        if(!isset($this->options[$key])){
            $this->options[$key] = $value;
        }
        return $this;
    }

    private function getRequestHeaders(): array
    {
        if(empty($this->headers)){
            return [];
        }
        $headers = [];
        foreach ($this->headers as $key => $values){
            if(!\is_array($values)){
                $headers[] = \sprintf('%s: %s', $key, $values);
            }else{
                foreach($values as $value){
                    $headers[] = \sprintf('%s: %s', $key, $value);
                }
            }
        }
        return $headers;
    }


}
