<?php

namespace PhpLab\Rest\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpLab\Bundle\Crypt\Libs\Encoders\EncoderInterface;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Rest\Entities\ProtoEntity;

class RestProtoClient
{

    private $endpoint;
    private $encoder;

    public function __construct(string $endpoint, EncoderInterface $encoder)
    {
        $this->endpoint = $endpoint;
        $this->encoder = $encoder;
    }

    public function request(string $method, string $uri, array $query = [], array $body = []): ProtoEntity
    {
        $requestProtoEntity = new ProtoEntity;
        $requestProtoEntity->method = $method;
        $requestProtoEntity->uri = $uri;
        $requestProtoEntity->headers = [HttpHeaderEnum::CONTENT_TYPE => 'application/x-base64'];
        $requestProtoEntity->query = $query;
        $encoder = $this->encoder;
        $restProto = new RestProto($encoder, $_SERVER);
        $encodedRequest = $encoder->encode($requestProtoEntity);
        $client = new Client;
        $response = $client->request(HttpMethodEnum::POST, $this->endpoint, [
            RequestOptions::HEADERS => [
                RestProto::CRYPT_HEADER_NAME => 1,
            ],
            RequestOptions::FORM_PARAMS => [
                'data' => $encodedRequest,
            ],
        ]);
        $encodedContent = $response->getBody()->getContents();
        $payload = $encoder->decode($encodedContent);
        $protoEntity = new ProtoEntity;
        EntityHelper::setAttributes($protoEntity, $payload);
        return $protoEntity;
    }

}