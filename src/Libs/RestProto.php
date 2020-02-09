<?php

namespace PhpLab\Rest\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PhpLab\Bundle\Crypt\Libs\Encoders\EncoderInterface;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpServerEnum;
use PhpLab\Rest\Entities\ProtoEntity;
use Symfony\Component\HttpFoundation\Response;

class RestProto
{

    const CRYPT_SERVER_NAME = 'HTTP_X_CRYPT';
    const CRYPT_HEADER_NAME = 'X-Crypt';
    const CRYPT_CONTENT_TYPE = 'application/x-base64';

    private $encoderInstance;
    private $originalServer;

    public function __construct(EncoderInterface $encoder, array $server)
    {
        $this->encoderInstance = $encoder;
        $this->originalServer = $server;
    }

    public function sendRequest(string $endpoint, ProtoEntity $protoEntity) {
        $encodedRequest = $this->encoderInstance->encode($protoEntity);
        $client = new Client;
        $response = $client->request(HttpMethodEnum::POST, $endpoint, [
            RequestOptions::HEADERS => [
                RestProto::CRYPT_HEADER_NAME => 1,
                HttpHeaderEnum::CONTENT_TYPE => self::CRYPT_CONTENT_TYPE,
            ],
            RequestOptions::FORM_PARAMS => [
                'data' => $encodedRequest,
            ],
        ]);
        return $response;
    }

    public function isCrypt(): bool
    {
        $isPostMethod = strtolower($this->originalServer[HttpServerEnum::REQUEST_METHOD]) == 'post';
        $isCrypt = $isPostMethod && ! empty($this->originalServer[self::CRYPT_SERVER_NAME]);
        return $isCrypt;
    }

    public function encodeResponse(Response $response): Response
    {
        if ( ! $this->isCrypt()) {
            return $response;
        }
        $headers = [];
        $encodedResponse = new Response;
        foreach ($response->headers->all() as $headerKey => $headerValue) {
            $headers[$headerKey] = \PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper::first($headerValue);
        }
        $payload = [
            'statusCode' => $response->getStatusCode(),
            'headers' => $headers,
            'content' => $response->getContent(),
        ];
        $encodedContent = $this->encoderInstance->encode($payload);
        $encodedResponse->headers->set(self::CRYPT_HEADER_NAME, 1);
        $encodedResponse->setContent($encodedContent);
        return $encodedResponse;
    }

    public function decodeRequest(string $encodedData): ProtoEntity
    {
        $server = [];

        $payload = $this->encoderInstance->decode($encodedData);

        $protoEntity = new ProtoEntity;
        EntityHelper::setAttributes($protoEntity, $payload);

        $protoEntity->headers = $protoEntity->headers ?? [];
        $protoEntity->query = $protoEntity->query ?? [];
        $protoEntity->body = $protoEntity->body ?? [];

        if ($protoEntity->headers) {
            foreach ($protoEntity->headers as $headerKey => $headerValue) {
                $headerKey = strtoupper($headerKey);
                $headerKey = str_replace('-', '_', $headerKey);
                $headerKey = 'HTTP_' . $headerKey;
                $server[$headerKey] = $headerValue;
            }
        }

        $server[HttpServerEnum::REQUEST_METHOD] = HttpMethodEnum::value($protoEntity->method, HttpMethodEnum::GET);
        $server[HttpServerEnum::REQUEST_URI] = $protoEntity->uri ?? '/';


        $protoEntity->server = $server;
        return $protoEntity;
    }

    public function applyToEnv(ProtoEntity $protoEntity)
    {
        global $_SERVER, $_GET, $_POST, $_FILES;
        $_SERVER = array_merge($_SERVER, $protoEntity->server);
        $_GET = $protoEntity->query;
        $_POST = $protoEntity->body;
    }

    public function prepareRequest()
    {
        global $_SERVER, $_GET, $_POST;
        if ( ! $this->isCrypt()) {
            return;
        }
        $protoEntity = $this->decodeRequest($_POST['data']);
        $this->applyToEnv($protoEntity);
    }

}