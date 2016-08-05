<?php

namespace mpyw\Coutte;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\DomCrawler\Form;

interface RequesterInterface
{
    /**
     * Calls a URI.
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array  $parameters    The Request parameters
     * @param array  $files         The files
     * @param array  $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param string $content       The raw body data
     * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     */
    public function request($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true);

    /**
     * Clicks on a given link.
     *
     * @param Link $link A Link instance
     *
     * @return Crawler
     */
    public function click(Link $link);

    /**
     * Submits a form.
     *
     * @param Form  $form   A Form instance
     * @param array $values An array of form field values
     *
     * @return Crawler
     */
    public function submit(Form $form, array $values = []);

    /**
     * Goes back in the browser history.
     *
     * @return Crawler
     */
    public function back();

    /**
     * Goes forward in the browser history.
     *
     * @return Crawler
     */
    public function forward();

    /**
     * Reloads the current browser.
     *
     * @return Crawler
     */
    public function reload();

    /**
     * Follow redirects?
     *
     * @return Crawler
     *
     * @throws \LogicException If request was not a redirect
     */
    public function followRedirect();

}
