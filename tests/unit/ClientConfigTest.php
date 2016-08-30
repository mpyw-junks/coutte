<?php

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CoInterface;
use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientConfigTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function testCurlOptionsConfig()
    {
        $client = new Client($expected = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $actual = array_intersect_key($client->getCurlOptions(), $expected);
        $this->assertEquals($expected, $actual);
    }
}
