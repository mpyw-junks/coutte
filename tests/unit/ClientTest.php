<?php

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CoInterface;
use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;
    private static $pid;

    public function getClient()
    {
        return new Client([
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
    }

    public function testPayloadHttp()
    {
        $c = $this->getClient();
        $c->request('GET', 'http://localhost:8080/hello.php');
        $this->assertEquals('Hello', $c->getResponse()->getContent());
    }

    public function testPayloadHttps()
    {
        $c = $this->getClient();
        $c->request('GET', 'https://localhost:8081/hello.php');
        $this->assertEquals('Hello', $c->getResponse()->getContent());
    }

}
