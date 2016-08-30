<?php

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CoInterface;
use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientPayloadTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function getClient()
    {
        return new Client([
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function testPayloadHttp()
    {
        $client = $this->getClient();
        $client->request('GET', 'http://localhost:8080/hello.php');
        $this->assertEquals('Hello', $client->getResponse()->getContent());
    }

    public function testPayloadHttps()
    {
        $client = $this->getClient();
        $client->request('GET', 'https://localhost:8081/hello.php');
        $this->assertEquals('Hello', $client->getResponse()->getContent());
    }

    public function testAsyncPayloadHttp()
    {
        Co::wait(function () {
            $client = $this->getClient();
            yield $client->requestAsync('GET', 'http://localhost:8080/hello.php');
            $this->assertEquals('Hello', $client->getResponse()->getContent());
        });
    }

    public function testAsyncPayloadHttps()
    {
        Co::wait(function () {
            $client = $this->getClient();
            yield $client->requestAsync('GET', 'https://localhost:8081/hello.php');
            $this->assertEquals('Hello', $client->getResponse()->getContent());
        });
    }

}
