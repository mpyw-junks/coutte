<?php

namespace mpyw\Coutte;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;

interface BasicInterface
{
    /**
     * Constructor.
     *
     * @param array     $server    The server parameters (equivalent of $_SERVER)
     * @param History   $history   A History instance to store the browser history
     * @param CookieJar $cookieJar A CookieJar instance to store the cookies
     */
    public function __construct(array $server = [], History $history = null, CookieJar $cookieJar = null);

    /**
     * Sets whether to automatically follow redirects or not.
     *
     * @param bool $followRedirect Whether to follow redirects
     */
    public function followRedirects($followRedirect = true);

    /**
     * Returns whether client automatically follows redirects or not.
     *
     * @return bool
     */
    public function isFollowingRedirects();

    /**
     * Sets the maximum number of requests that crawler can follow.
     *
     * @param int $maxRedirects
     */
    public function setMaxRedirects($maxRedirects);

    /**
     * Returns the maximum number of requests that crawler can follow.
     *
     * @return int
     */
    public function getMaxRedirects();

    /**
     * Sets server parameters.
     *
     * @param array $server An array of server parameters
     */
    public function setServerParameters(array $server);

    /**
     * Sets single server parameter.
     *
     * @param string $key   A key of the parameter
     * @param string $value A value of the parameter
     */
    public function setServerParameter($key, $value);

    /**
     * Gets single server parameter for specified key.
     *
     * @param string $key     A key of the parameter to get
     * @param string $default A default value when key is undefined
     *
     * @return string A value of the parameter
     */
    public function getServerParameter($key, $default = '');

    /**
     * Returns the History instance.
     *
     * @return History A History instance
     */
    public function getHistory();

    /**
     * Returns the CookieJar instance.
     *
     * @return CookieJar A CookieJar instance
     */
    public function getCookieJar();

    /**
     * Returns the current Crawler instance.
     *
     * @return Crawler|null A Crawler instance
     */
    public function getCrawler();

    /**
     * Returns the current origin response instance.
     *
     * The origin response is the response instance that is returned
     * by the code that handles requests.
     *
     * @return object|null A response instance
     */
    public function getResponse();

    /**
     * Returns the current origin Request instance.
     *
     * The origin request is the request instance that is sent
     * to the code that handles requests.
     *
     * @return object|null A Request instance
     */
    public function getRequest();

    /**
     * Restarts the client.
     *
     * It flushes history and all cookies.
     */
    public function restart();

}
