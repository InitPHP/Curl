<?php
/**
 * Curl.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    0.1
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Curl;

use InitPHP\Curl\Exception\CurlException;

class Curl
{

    public const VERSION = '0.1';

    protected ?string $url = null;

    private string $userInfo = '';

    protected string $method = 'GET';

    protected array $headers = [];

    protected string $protocol = '1.1';

    protected array $options = [
        'allow_redirects'   => false,
        'max_redirects'     => 3,
        'timeout'           => 0,
        'ssl'               => true,
        'proxy'             => null,
    ];

    protected string $body = '';

    protected static int $bodySeek = 0;

    protected array $params = [];

    protected static array $response = [
        'status'    => 200,
        'header'    => '',
        'body'      => ''
    ];

    /** @var mixed */
    protected $info = null;

    /** @var null|false|resource */
    protected $curl = null;

    public function __construct()
    {
        if(!\extension_loaded('curl')){
            throw new CurlException('The CURL extension must be installed.');
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function init(string $url): self
    {
        if(\filter_var($url, \FILTER_VALIDATE_URL) === FALSE){
            throw new CurlException('URL address could not be verified');
        }
        $this->close();
        $this->curl = \curl_init();
        if($this->curl === FALSE){
            throw new CurlException('Failed to initialize CURL.');
        }
        $this->url = $url;
        $parse = \parse_url($url);
        $this->userInfo = $parse['user'] ?? '';
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function clear(): self
    {
        $this->close();
        $this->url = null;
        $this->userInfo = '';
        $this->method = 'GET';
        $this->headers = [];
        $this->protocol = '1.1';
        $this->options = [
            'allow_redirects'   => false,
            'max_redirects'     => 5,
            'timeout'           => 0,
            'ssl'               => true,
            'proxy'             => null,
        ];
        $this->body = '';
        static::$bodySeek = 0;
        self::$response = [
            'status'    => 200,
            'header'    => '',
            'body'      => ''
        ];
        $this->params = [];
        $this->info = null;
        return $this;
    }

    public function response(): array
    {
        return self::$response;
    }

    public function method(string $method = 'GET'): self
    {
        $method = \strtoupper($method);
        if(!\in_array($method, ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], true)){
            throw new CurlException('Request method can only be GET, POST, PUT, HEAD, DELETE, PATCH or OPTIONS.');
        }
        $this->method = $method;
        return $this;
    }

    public function protocol(string $protocol = '1.1'): self
    {
        if(!\in_array($protocol, ['1.0', '1.1', '2.0'], true)){
            throw new CurlException('The protocol can only be 1.0, 1.1 or 2.0.');
        }
        if($protocol == '2.0' && !\defined('CURL_HTTP_VERSION_2_0')){
            throw new CurlException('libcurl 7.33 needed for HTTP 2.0 support');
        }
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * Defines the value of the specified element from the options array.
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function option(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function params(array $params = []): self
    {
        if(!empty($params)){
            $this->params = \array_merge($this->params, $params);
        }
        return $this;
    }

    /**
     * Executes CURL.
     *
     * @return bool
     */
    public function exec(): bool
    {
        if(\defined('CURLOPT_PROTOCOLS')){
            $this->setOpt(\CURLOPT_PROTOCOLS, (\CURLPROTO_HTTP | \CURLPROTO_HTTPS));
            $this->setOpt(\CURLOPT_REDIR_PROTOCOLS, (\CURLPROTO_HTTP | \CURLPROTO_HTTPS));
        }
        $this->setOpt(\CURLOPT_HEADER, false);
        $this->setOpt(\CURLOPT_RETURNTRANSFER, false);
        $this->setOpt(\CURLOPT_FAILONERROR, false);

        if(($proxy = $this->options['proxy'] ?? null) !== null){
            $this->setOpt(\CURLOPT_PROXY, $proxy);
        }
        $canFollow = !\ini_get('safe_mode') && !\ini_get('open_basedir') && ($this->options['allow_redirects'] ?? false);
        $this->setOpt(\CURLOPT_FOLLOWLOCATION, $canFollow);
        $this->setOpt(\CURLOPT_MAXREDIRS, ($canFollow ? ($this->options['max_redirects'] ?? 3) : 0));
        $this->setOpt(\CURLOPT_SSL_VERIFYPEER, (($this->options['ssl'] ?? false) ? 1 : 0));
        $this->setOpt(\CURLOPT_SSL_VERIFYHOST, (($this->options['ssl'] ?? false) ? 2 : 0));
        if(($timeout = ($this->options['timeout'] && 0)) > 0){
            $this->setOpt(\CURLOPT_TIMEOUT, $timeout);
        }

        $options = [
            \CURLOPT_CUSTOMREQUEST  => $this->method,
            \CURLOPT_URL            => $this->url,
            \CURLOPT_HTTPHEADER     => $this->getHeaders(),
        ];

        switch($this->protocol){
            case '1.0':
                $options[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_1_0;
                break;
            case '1.1':
                $options[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_1_1;
                break;
            case '2.0':
                $options[\CURLOPT_HTTP_VERSION] = \CURL_HTTP_VERSION_2_0;
                break;
        }

        if(!empty($this->userInfo)){
            $options[\CURLOPT_USERPWD] = $this->userInfo;
        }

        switch ($this->method){
            case 'HEAD':
                $options[\CURLOPT_NOBODY] = true;
                break;
            case 'GET':
                $options[\CURLOPT_HTTPGET] = true;
                break;
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
            case 'OPTIONS':
                if(!empty($this->body)){
                    $options[\CURLOPT_UPLOAD] = true;
                    $size = \strlen($this->body);
                    if($size > (1024 * 1024)){
                        $options[\CURLOPT_INFILESIZE] = $size;
                        $options[\CURLOPT_READFUNCTION] = function ($ch, $fd, $lenght){
                            return $this->readBody($lenght);
                        };
                    }else{
                        $options[\CURLOPT_POSTFIELDS] = $this->body;
                    }
                }else{
                    if(!empty($this->params)){
                        $params = [];
                        foreach ($this->params as $key => $value){
                            $params[] = $key . '=' . $value;
                        }
                        $options[\CURLOPT_POST] = true;
                        $options[\CURLOPT_POSTFIELDS] = \implode('&', $params);
                    }
                }
        }

        \curl_setopt_array($this->curl, $options);

        $this->setOpt(\CURLOPT_HEADERFUNCTION, function ($ch, $data){
            $str = \trim($data);
            if($str !== ''){
                if(\strpos(\strtolower($str), 'http/') === 0){
                    self::$response['status'] = $str;
                }else{
                    self::$response['header'] = $str;
                }
            }
            return \strlen($data);
        });
        $this->setOpt(\CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            self::$response['body'] .= $data;
            return \strlen($data);
        });


        try {
            $exec = \curl_exec($this->curl);
            $this->info = \json_encode(\curl_getinfo($this->curl));

        } finally {
            $this->setOpt(\CURLOPT_HEADERFUNCTION, null)
                ->setOpt(\CURLOPT_READFUNCTION, null)
                ->setOpt(\CURLOPT_WRITEFUNCTION, null)
                ->setOpt(\CURLOPT_PROGRESSFUNCTION, null)
                ->setOpt(\CURLOPT_PROGRESSFUNCTION, null);
            \curl_reset($this->curl);
        }
        return $exec !== FALSE;
    }

    /**
     * Closes the current CURL.
     */
    public function close(): void
    {
        if($this->curl !== null){
            \curl_close($this->curl);
        }
        $this->curl = null;
    }

    /**
     * Defines an options with the `curl_setopt()` function for the current CURL.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setOpt($key, $value): self
    {
        \curl_setopt($this->curl, $key, $value);
        return $this;
    }

    protected function getHeaders(): array
    {
        $reheaders = [];
        foreach ($this->headers as $key => $values){
            if(!\is_array($values)){
                $reheaders[] = \sprintf('%s: %s', $key, $values);
            }else{
                foreach($values as $value){
                    $reheaders[] = \sprintf('%s: %s', $key, $value);
                }
            }
        }
        return $reheaders;
    }

    protected function readBody(int $lenght): string
    {
        $content = \substr($this->body, static::$bodySeek, $lenght);
        static::$bodySeek += $lenght;
        return $content;
    }
}
