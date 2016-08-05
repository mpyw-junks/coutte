<?php

namespace mpyw\Coutte\Internal;

use mpyw\Co\CoInterface;
use Symfony\Component\BrowserKit\Client as BaseClient;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\DomCrawler\Form;

abstract class AsyncClient extends BaseClient
{
    private $maxRedirectsExtended = -1;
    private $redirectCountExtended = 0;
    private $isMainRequestExtended = true;

    /**
     * Makes an asynchronous request.
     *
     * @param Request $request An origin request instance
     *
     * @return \Generator object An origin response instance
     */
    abstract protected function doRequestAsync($request);

    /**
     * Unsupported action.
     *
     * @param bool $insulated
     *
     * @throws \BadMethodCallException
     */
    public function insulate($insulated = true)
    {
        throw new \BadMethodCallException('Unsupported action.');
    }

    /**
     * Sets the maximum number of requests that crawler can follow.
     *
     * @param int $maxRedirects
     */
    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirectsExtended = $maxRedirects < 0 ? -1 : $maxRedirects;
        $this->followRedirects = -1 != $this->maxRedirectsExtended;
    }
    /**
     * Returns the maximum number of requests that crawler can follow.
     *
     * @return int
     */
    public function getMaxRedirects()
    {
        return $this->maxRedirectsExtended;
    }

    /**
     * Makes a request from a Request object directly asynchronously.
     *
     * @param Request $request       A Request instance
     * @param bool    $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return \Generator Crawler
     */
    protected function requestFromRequestAsync(Request $request, $changeHistory = true)
    {
        yield CoInterface::RETURN_WITH => $this->requestAsync($request->getMethod(), $request->getUri(), $request->getParameters(), $request->getFiles(), $request->getServer(), $request->getContent(), $changeHistory);
    }

    /**
     * Goes back in the browser history asynchronously.
     *
     * @return \Generator Crawler
     */
    public function backAsync()
    {
        return $this->requestFromRequestAsync($this->history->back(), false);
    }

    /**
     * Goes forward in the browser history asynchronously.
     *
     * @return \Generator
     */
    public function forwardAsync()
    {
        return $this->requestFromRequestAsync($this->history->forward(), false);
    }

    /**
     * Reloads the current browser asynchronously.
     *
     * @return \Generator
     */
    public function reloadAsync()
    {
        return $this->requestFromRequestAsync($this->history->current(), false);
    }

    /**
     * Clicks on a given link asynchronously.
     *
     * @param Link $link A Link instance
     *
     * @return \Generator Crawler
     */
    public function clickAsync(Link $link)
    {
        return $link instanceof Form
                ? $this->submitAsync($link)
                : $this->requestAsync($link->getMethod(), $link->getUri());
    }

    /**
     * Submits a form asynchronously.
     *
     * @param Form  $form   A Form instance
     * @param array $values An array of form field values
     *
     * @return \Generator Crawler
     */
    public function submitAsync(Form $form, array $values = [])
    {
        $form->setValues($values);
        return $this->requestAsync($form->getMethod(), $form->getUri(), $form->getPhpValues(), $form->getPhpFiles());
    }

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
    public function request($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        $uri = $this->beforeRequest($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->response = $this->doRequest($this->request);
        $this->afterRequest($uri);
        if ($this->followRedirects && $this->redirect) {
            return $this->crawler = $this->followRedirect();
        }
        return $this->crawler = $this->createCrawlerFromContent($this->internalRequest->getUri(), $this->internalResponse->getContent(), $this->internalResponse->getHeader('Content-Type'));
    }

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
    public function requestAsync($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $uri = $this->beforeRequest($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->response = (yield $this->doRequestAsync($this->request));
        $this->afterRequest($uri);
        if ($this->followRedirects && $this->redirect) {
            yield CoInterface::RETURN_WITH => $this->crawler = (yield $this->followRedirectAsync());
        }
        yield CoInterface::RETURN_WITH => $this->crawler = $this->createCrawlerFromContent($this->internalRequest->getUri(), $this->internalResponse->getContent(), $this->internalResponse->getHeader('Content-Type'));
    }

    /**
     * Follow redirects asynchronously?
     *
     * @return \Generator
     *
     * @throws \LogicException If request was not a redirect
     */
    public function followRedirect()
    {
        $p = $this->beforeRedirect();
        $this->isMainRequestExtended = false;
        $response = $this->request($p['method'], $this->redirect, $p['parameters'], $p['files'], $p['server'], $p['content']);
        $this->isMainRequestExtended = true;
        yield CoInterface::RETURN_WITH => $response;
    }

    /**
     * Follow redirects asynchronously?
     *
     * @return \Generator Crawler
     *
     * @throws \LogicException If request was not a redirect
     */
    public function followRedirectAsync()
    {
        $p = $this->beforeRedirect();
        $this->isMainRequestExtended = false;
        $response = (yield $this->requestAsync($p['method'], $this->redirect, $p['parameters'], $p['files'], $p['server'], $p['content']));
        $this->isMainRequestExtended = true;
        yield CoInterface::RETURN_WITH => $response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|null $content
     * @param boolean $changeHistory
     */
    private function beforeRequest($method, $uri, array $parameters, array $files, array $server, $content, $changeHistory)
    {
        if ($this->isMainRequestExtended) {
            $this->redirectCountExtended = 0;
        } else {
            ++$this->redirectCountExtended;
        }
        $uri = $this->getAbsoluteUri($uri);
        $server = array_merge($this->server, $server);
        if (isset($server['HTTPS'])) {
            $uri = preg_replace('{^' . parse_url($uri, PHP_URL_SCHEME) . '}', $server['HTTPS'] ? 'https' : 'http', $uri);
        }
        if (!$this->history->isEmpty()) {
            $server['HTTP_REFERER'] = $this->history->current()->getUri();
        }
        if (empty($server['HTTP_HOST'])) {
            $server['HTTP_HOST'] = $this->extractHostExtended($uri);
        }
        $server['HTTPS'] = 'https' === parse_url($uri, PHP_URL_SCHEME);
        $this->internalRequest = new Request($uri, $method, $parameters, $files, $this->cookieJar->allValues($uri), $server, $content);
        $this->request = $this->filterRequest($this->internalRequest);
        if ($changeHistory) {
            $this->history->add($this->internalRequest);
        }
        return $uri;
    }

    /**
     * @param string $uri
     */
    private function afterRequest($uri)
    {
        $this->internalResponse = $this->filterResponse($this->response);
        $this->cookieJar->updateFromResponse($this->internalResponse, $uri);
        $status = $this->internalResponse->getStatus();
        if ($status >= 300 && $status < 400) {
            $this->redirect = $this->internalResponse->getHeader('Location');
        } else {
            $this->redirect = null;
        }
    }

    private function beforeRedirect()
    {
        if (empty($this->redirect)) {
            throw new \LogicException('The request was not redirected.');
        }
        if (-1 !== $this->maxRedirectsExtended && $this->redirectCountExtended > $this->maxRedirectsExtended) {
            throw new \LogicException(sprintf('The maximum number (%d) of redirections was reached.', $this->maxRedirectsExtended));
        }
        $request = $this->internalRequest;
        if (in_array($this->internalResponse->getStatus(), [302, 303])) {
            $method = 'GET';
            $files = array();
            $content = null;
        } else {
            $method = $request->getMethod();
            $files = $request->getFiles();
            $content = $request->getContent();
        }
        if ('GET' === strtoupper($method)) {
            // Don't forward parameters for GET request as it should reach the redirection URI
            $parameters = [];
        } else {
            $parameters = $request->getParameters();
        }
        $server = $request->getServer();
        $server = $this->updateServerFromUriExtended($server, $this->redirect);
        return compact('method', 'parameters', 'files', 'server', 'content');
    }

    private function updateServerFromUriExtended($server, $uri)
    {
        $server['HTTP_HOST'] = $this->extractHostExtended($uri);
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        $server['HTTPS'] = null === $scheme ? $server['HTTPS'] : 'https' === $scheme;
        unset($server['HTTP_IF_NONE_MATCH'], $server['HTTP_IF_MODIFIED_SINCE']);
        return $server;
    }

    private function extractHostExtended($uri)
    {
        $host = parse_url($uri, PHP_URL_HOST);
        $port = parse_url($uri, PHP_URL_PORT);
        return $host . (is_int($port) ? ":$port" : '');
    }
}
