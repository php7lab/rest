<?php

namespace PhpLab\Rest\Entities;

use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ProtoEntity /*implements RequestInterface*/
{

    //private $statusCode;
    //private $protocolVersion = '1.1';
    //private $requestTarget = '/';
    private $headers = [];
    private $method = HttpMethodEnum::GET;
    //private $content;
    private $uri = '/';
    private $query = [];
    private $body = [];
    private $server;

    /*public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }*/

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers): void
    {
        $this->headers = $headers;
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /*public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }*/

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri): void
    {
        $this->uri = $uri;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query): void
    {
        $this->query = $query;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setServer($server): void
    {
        $this->server = $server;
    }

    /*public function getHeader($name) {
        $name = strtolower($name);
        return $this->headers[$name] ?? null;
    }*/

    /*public function getData() {
        $contentType = $this->getHeader(HttpHeaderEnum::CONTENT_TYPE);
        $data = $this->content;
        if($contentType == 'application/json') {
            $data = json_decode($data, true);
        }
        return $data;
    }*/

    /*public function withHeader($name, $value) {
        $name = strtolower($name);
        $this->headers[$name] = $value;
    }

    public function withAddedHeader($name, $value)
    {
        $name = strtolower($name);
        $this->headers[$name][] = $value;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }

    public function hasHeader($name)
    {
        $name = strtolower($name);
        return isset($this->headers[$name]);
    }

    public function getHeaderLine($name)
    {
        $name = strtolower($name);
        $value = $this->headers[$name];
        return is_array($value) ? $value[0] : $value;
    }

    public function withoutHeader($name)
    {
        $name = strtolower($name);
        unset($this->headers[$name]);
    }

    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    public function withRequestTarget($requestTarget)
    {
        $this->requestTarget = $requestTarget;
    }

    public function withMethod($method)
    {
        $this->method = $method;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;
    }*/
}