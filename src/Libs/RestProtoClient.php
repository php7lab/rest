<?php

namespace PhpLab\Rest\Libs;

use GuzzleHttp\Psr7\Response;
use PhpLab\Bundle\Crypt\Libs\Encoders\EncoderInterface;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use Psr\Http\Message\ResponseInterface;

class RestProtoClient
{

    private $transport;
    private $encoder;

    public function __construct($transport, EncoderInterface $encoder)
    {
        $this->transport = $transport;
        $this->encoder = $encoder;
    }

    public function request(string $method, string $uri, array $query = [], array $body = []): ResponseInterface
    {
        $dataForEncode = [
            'method' => $method,
            'uri' => $uri,
            'headers' => [
                HttpHeaderEnum::CONTENT_TYPE => 'application/x-base64',
            ],
            'query' => $query,
        ];
        $encodedRequest = $this->encoder->encode($dataForEncode);
        $encodedContent = $this->transport->request($encodedRequest);
        $payload = $this->encoder->decode($encodedContent);
        return new Response($payload['statusCode'], $payload['headers'], $payload['content']);
    }

}