<?php

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CoInterface;
use mpyw\Co\CURLException;
use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientGetAndPostTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;

    public function getClient()
    {
        return new Client([
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function testInvalid()
    {
        $this->setExpectedException(CURLException::class);
        $client = $this->getClient();
        $client->request('GET', 'invalid');
    }

    public function testNormalPost()
    {
        $client = $this->getClient();
        $expected = ['a' => 'b'];
        $crawler = $client->request('POST', 'http://localhost:8080/json.php', $expected);
        $json = json_decode($crawler->filter('.json')->text(), true);
        $this->assertEquals($expected, $json['_POST']);
    }

    public function testRawPost()
    {
        $client = $this->getClient();
        $expected = 'abcde';
        $crawler = $client->request('POST', 'http://localhost:8080/json.php', [], [], [], $expected);
        $json = json_decode($crawler->filter('.json')->text(), true);
        $this->assertEquals([], $json['_POST']);
        $this->assertEquals($expected, $json['rawpost']);
    }

}
