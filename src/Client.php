<?php

namespace mpyw\Coutte;

use mpyw\Co\CoInterface;
use mpyw\Co\CURLException;
use mpyw\Coutte\Internal\AsyncClient as AsyncBaseClient;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class Client extends AsyncBaseClient implements BasicInterface, RequesterInterface, AsyncRequesterInterface
{
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array     $curlOptions  Options for curl_setopt_array().
     * @param array     $server       The server parameters (equivalent of $_SERVER)
     * @param History   $history      A History instance to store the browser history
     * @param CookieJar $cookieJar    A CookieJar instance to store the cookies
     */
    public function __construct(
        array $curlOptions = [],
        array $server = [],
        History $history = null,
        CookieJar $cookieJar = null
    ) {
        $this->setCurlOptions($curlOptions);
        parent::__construct($server, $history, $cookieJar);
    }

    public function setCurlOptions(array $options)
    {
        $this->options = $options + $this->options;
    }

    public function getCurlOptions()
    {
        return $this->options;
    }

    /**
     * Makes a request.
     *
     * @param object $request An origin request instance
     *
     * @return Response An origin response instance
     */
    protected function doRequest($request)
    {
        $ch = $this->createCurl($request);
        $content = curl_exec($ch);
        if ($content === false) {
            throw new CURLException(curl_error($ch), curl_errno($ch), $ch);
        }
        return $this->processResult($content, $ch);
    }

    /**
     * Makes an asynchronous request.
     *
     * @param object $request An origin request instance
     *
     * @return \Generator object An origin response instance
     */
    protected function doRequestAsync($request)
    {
        $ch = $this->createCurl($request);
        $content = (yield $ch);
        yield CoInterface::RETURN_WITH => $this->processResult($content, $ch);
    }

    protected function createCurl($request)
    {
        $method = strtoupper($request->getMethod());
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $request->getUri(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $this->createHeaders($request),
            CURLOPT_COOKIE => $this->createCookieHeader($request),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_NOBODY => $method === 'HEAD',
        ] + $this->options + [
            CURLOPT_ENCODING => 'gzip',
        ]);
        if ($method === 'HEAD' || $method === 'GET') {
            return $ch;
        }
        if (null !== $content = $request->getContent()) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            return $ch;
        }
        $params = $request->getParameters();
        $files = $request->getFiles();
        if (!$files) {
            $content = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            return $ch;
        }
        $multipart = [];
        $this->addMultipartFields($params, $multipart);
        $this->addMultipartFiles($files, $multipart);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
        return $ch;
    }

    /**
     * @param resource $ch
     */
    protected function processResult($content, $ch)
    {
        list($head, $data) = explode("\r\n\r\n", $content, 2) + [1 => ''];
        preg_match_all('/^([^:]+?):(.+)$/m', $head, $matches, PREG_SET_ORDER);
        $headers = [];
        foreach ($matches as $match) {
            $headers[trim($match[1])][] = trim($match[2]);
        }
        return new Response($data, curl_getinfo($ch, CURLINFO_HTTP_CODE), $headers);
    }

    protected function createHeaders($request)
    {
        static $contentHeaders = [
            'content-length' => true,
            'content-md5' => true,
            'content-type' => true,
        ];
        $headers = [];
        foreach ($request->getServer() as $key => $val) {
            $key = strtolower(strtr($key, '_', '-'));
            if (!strncmp($key, 'http-', 5)) {
                $headers[] = substr($key, 5) . ': ' . $val;
                continue;
            }
            if (isset($contentHeaders[$key])) {
                $headers[] = "$key: $val";
                continue;
            }
        }
        return $headers;
    }

    protected function createCookieHeader($request)
    {
        $cookies = $this->getCookieJar()->allValues($request->getUri());
        $pairs = [];
        foreach ($cookies as $name => $value) {
            $pairs[] = "$name=$value";
        }
        return implode('; ', $pairs);
    }

    protected function addMultipartFiles(array $files, array &$multipart, $arrayName = '')
    {
        foreach ($files as $name => $info) {
            if ($arrayName !== '') {
                $name = "{$arrayName}[{$name}]";
            }
            if ($info instanceof \CURLFile) {
                $multipart[$name] = $info;
                continue;
            }
            if (!is_array($info)) {
                $multipart[$name] = new \CURLFile($info);
                continue;
            }
            if (!isset($info['tmp_name'])) {
                $this->addMultipartFiles($info, $multipart, $name);
                continue;
            }
            if ('' === $info['tmp_name']) {
                continue;
            }
            $multipart[$name] = new \CURLFile(
                $info['tmp_name'],
                isset($info['type']) ? $info['type'] : '',
                isset($info['name']) ? $info['name'] : ''
            );
        }
    }

    protected function addMultipartFields(array $params, array &$multipart, $arrayName = '')
    {
        foreach ($params as $name => $value) {
            if ($arrayName !== '') {
                $name = "{$arrayName}[{$name}]";
            }
            if (!is_array($value)) {
                $multipart[$name] = $value;
                continue;
            }
            $this->addMultipartFields($value, $multipart, $name);
        }
    }
}
