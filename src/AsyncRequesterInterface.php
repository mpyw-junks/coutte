<?php

namespace mpyw\Coutte;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\DomCrawler\Form;

interface AsyncRequesterInterface
{
    /**
     * Calls a URI asynchronously.
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array  $parameters    The Request parameters
     * @param array  $files         The files
     * @param array  $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param string $content       The raw body data
     * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return \Generator Crawler
     */
    public function requestAsync($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true);

    /**
     * Clicks on a given link asynchronously.
     *
     * @param Link $link A Link instance
     *
     * @return \Generator Crawler
     */
    public function clickAsync(Link $link);

    /**
     * Submits a form asynchronously.
     *
     * @param Form  $form   A Form instance
     * @param array $values An array of form field values
     *
     * @return \Generator Crawler
     */
    public function submitAsync(Form $form, array $values = []);

    /**
     * Goes back in the browser history asynchronously.
     *
     * @return \Generator Crawler
     */
    public function backAsync();

    /**
     * Goes forward in the browser history asynchronously.
     *
     * @return \Generator Crawler
     */
    public function forwardAsync();

    /**
     * Reloads the current browser asynchronously.
     *
     * @return \Generator Crawler
     */
    public function reloadAsync();

    /**
     * Follow redirects asynchronously?
     *
     * @return \Generator Crawler
     *
     * @throws \LogicException If request was not a redirect
     */
    public function followRedirectAsync();

}
