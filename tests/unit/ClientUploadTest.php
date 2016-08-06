<?php

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CoInterface;
use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class ClientUploadTest extends \Codeception\TestCase\Test {

    use \Codeception\Specify;

    public function getClient()
    {
        return new Client([
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function testUpload01()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', 'http://localhost:8080/upload_form01.php');
        $form = $crawler->filter('form')->form([
            'file[x][y]' => __FILE__,
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals('SUCCESS', $crawler->filter('#success')->text());
    }

    public function testUpload02()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', 'http://localhost:8080/upload_form02.php');
        $form = $crawler->filter('form')->form([
            'file[tmp_name][y]' => __FILE__,
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals('SUCCESS', $crawler->filter('#success')->text());
    }

    public function testUpload03()
    {
        $this->setExpectedException(
            PHPUnit_Framework_Exception::class,
            'is_readable() expects parameter 1 to be a valid path, object given'
        );
        $client = $this->getClient();
        $crawler = $client->request('GET', 'http://localhost:8080/upload_form01.php');
        $form = $crawler->filter('form')->form([
            'file[x][y]' => new CURLFile(__FILE__),
        ]);
    }

    public function testUpload04()
    {
        $client = $this->getClient();
        $crawler = $client->request(
            'POST',
            'http://localhost:8080/upload_form01.php',
            [],
            [
                'file[x]' => [
                    'y' => new CURLFile(__FILE__)
                ]
            ]
        );
        $this->assertEquals('SUCCESS', $crawler->filter('#success')->text());
    }

    public function testUpload05()
    {
        $client = $this->getClient();
        $crawler = $client->request(
            'POST',
            'http://localhost:8080/upload_form01.php',
            [],
            [
                'file[x]' => [
                    'y' => __FILE__,
                ]
            ]
        );
        $this->assertEquals('SUCCESS', $crawler->filter('#success')->text());
    }

    public function testUpload06()
    {
        $client = $this->getClient();
        $crawler = $client->request(
            'POST',
            'http://localhost:8080/upload_form01.php',
            [
                [],
                'file[x]' => [
                    'y' => new CURLFile(__FILE__)
                ]
            ]
        );
        $this->assertEquals('SUCCESS', $crawler->filter('#success')->text());
    }
}
