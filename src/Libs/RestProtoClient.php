<?php

namespace PhpLab\Rest\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PhpLab\Bundle\Crypt\Libs\Encoders\EncoderInterface;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Rest\Entities\RequestEntity;
use Psr\Http\Message\ResponseInterface;

class RestProtoClient
{

    private $endpoint;
    private $encoder;

    public function __construct(string $endpoint, EncoderInterface $encoder)
    {
        $this->endpoint = $endpoint;
        $this->encoder = $encoder;
    }

    public function request(string $method, string $uri, array $query = [], array $body = []): ResponseInterface
    {
        $requestProtoEntity = new RequestEntity;
        $requestProtoEntity->setMethod($method);
        $requestProtoEntity->setUri($uri);
        $requestProtoEntity->withHeader(HttpHeaderEnum::CONTENT_TYPE, 'application/x-base64');
        $requestProtoEntity->setQuery($query);
        $encoder = $this->encoder;
        $restProto = new RestProto($encoder, $_SERVER);
        $encodedRequest = $encoder->encode($requestProtoEntity);
        $client = new Client;
        $options = $this->getRequestOptions($encodedRequest);
        $response = $client->request(HttpMethodEnum::POST, $this->endpoint, $options);
        $encodedContent = $response->getBody()->getContents();
        $payload = $encoder->decode($encodedContent);
        $response = new Response($payload['statusCode'], $payload['headers'], $payload['content']);
        return $response;
    }

    private function getRequestOptions($encodedRequest) {
        return [
            RequestOptions::HEADERS => [
                RestProto::CRYPT_HEADER_NAME => 1,
            ],
            RequestOptions::FORM_PARAMS => [
                'data' => $encodedRequest,
            ],
        ];
    }

}