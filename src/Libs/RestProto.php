<?php

namespace PhpLab\Rest\Libs;

use PhpLab\Bundle\Crypt\Libs\Encoders\EncoderInterface;
use PhpLab\Core\Enums\Http\HttpMethodEnum;
use PhpLab\Core\Enums\Http\HttpServerEnum;
use PhpLab\Rest\Entities\ProtoEntity;
use Symfony\Component\HttpFoundation\Response;

class RestProto
{

    const CRYPT_SERVER_NAME = 'HTTP_X_CRYPT';
    const CRYPT_HEADER_NAME = 'X-Crypt';

    private $encoderInstance;
    private $originalServer;

    public function __construct(EncoderInterface $encoder, array $server)
    {
        $this->encoderInstance = $encoder;
        $this->originalServer = $server;
    }

    public function isCrypt(): bool
    {
        $isPostMethod = strtolower($this->originalServer[HttpServerEnum::REQUEST_METHOD]) == 'post';
        $isCrypt = $isPostMethod && ! empty($this->originalServer[self::CRYPT_SERVER_NAME]);
        return $isCrypt;
    }

    public function encodeResponse(Response $request): Response
    {
        if ( ! $this->isCrypt()) {
            return $request;
        }
        $headers = [];
        $encodedResponse = new Response;
        foreach ($request->headers->all() as $headerKey => $headerValue) {
            $headers[$headerKey] = \PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper::first($headerValue);
        }
        $payload = [
            'statusCode' => $request->getStatusCode(),
            'headers' => $headers,
            'content' => $request->getContent(),
        ];
        $encodedContent = $this->encoderInstance->encode($payload);
        $encodedResponse->headers->set(self::CRYPT_HEADER_NAME, 1);
        $encodedResponse->setContent($encodedContent);
        return $encodedResponse;
    }

    public function decodeRequest(string $encodedData): ProtoEntity
    {
        $server = [];
        $decodedData = $this->encoderInstance->decode($encodedData);
        if ($decodedData['headers']) {
            foreach ($decodedData['headers'] as $headerKey => $headerValue) {
                $headerKey = strtoupper($headerKey);
                $headerKey = str_replace('-', '_', $headerKey);
                $headerKey = 'HTTP_' . $headerKey;
                $server[$headerKey] = $headerValue;
            }
        }

        $server[HttpServerEnum::REQUEST_METHOD] = HttpMethodEnum::value($decodedData['method'], HttpMethodEnum::GET);
        $server[HttpServerEnum::REQUEST_URI] = $decodedData['uri'] ?? '/';

        $protoEntity = new ProtoEntity;
        $protoEntity->uri = $decodedData['uri'];
        $protoEntity->headers = $decodedData['headers'] ?? [];
        $protoEntity->query = $decodedData['query'] ?? [];
        $protoEntity->body = $decodedData['body'] ?? [];
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