<?php

namespace PhpLab\Rest\Entities;

use PhpLab\Core\Enums\Http\HttpHeaderEnum;

class ProtoEntity
{

    public $statusCode;
    public $headers;
    public $method;
    public $content;
    public $uri;
    public $query;
    public $body;
    public $server;

    public function getHeader(string $key) {
        $key = strtolower($key);
        return $this->headers[$key] ?? null;
    }

    public function getData() {
        $contentType = $this->getHeader(HttpHeaderEnum::CONTENT_TYPE);
        $data = $this->content;
        if($contentType == 'application/json') {
            $data = json_decode($data, true);
        }
        return $data;
    }

}