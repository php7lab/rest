<?php

namespace PhpLab\Rest\Libs;

use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Domain\Data\ArraySerializerHandlers\ArrayHandler;
use PhpLab\Domain\Data\ArraySerializerHandlers\ObjectHandler;
use PhpLab\Domain\Data\DataProviderEntity;
use PhpLab\Sandbox\User\Domain\Exceptions\UnauthorizedException;
use PhpLab\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Rest\Entities\ExceptionEntity;
use PhpLab\Rest\Libs\ArraySerializerHandlers\TimeHandler;
use PhpLab\Sandbox\Common\Exceptions\NotFoundException;
use PhpLab\Sandbox\Web\Enums\HttpHeaderEnum;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonRestSerializer
{

    private $exceptionStatusCodeMap = [
        NotFoundHttpException::class => 404,
        NotFoundException::class => 404,
        MethodNotAllowedHttpException::class => 405,
        UnprocessibleEntityException::class => 422,
        UnauthorizedException::class => 401
    ];

    private $serializerHandlers = [
        ArrayHandler::class,
        TimeHandler::class,
        ObjectHandler::class,
    ];

    /** @var Response | JsonResponse */
    private $response;

    public function __construct(Response $response = null)
    {
        $this->response = $response;
        //$this->response->headers->set(HttpHeaderEnum::CONTENT_TYPE, 'application/json');
    }

    public function serializeException(FlattenException $exception)
    {
        $statusCode = ArrayHelper::getValue($this->exceptionStatusCodeMap, $exception->getClass(), 500);
        $this->response->setStatusCode($statusCode);

        $exceptionEntity = new ExceptionEntity;
        $exceptionEntity->message = $exception->getMessage();
        $exceptionEntity->code = $exception->getCode();
        $exceptionEntity->status = $this->response->getStatusCode();
        $exceptionEntity->type = $exception->getClass();

        if ($_SERVER['APP_ENV'] === 'dev') {
            $exceptionEntity->file = $exception->getFile();
            $exceptionEntity->line = $exception->getLine();
            $exceptionEntity->trace = $exception->getTrace();
            $exceptionEntity->previous = $exception->getPrevious();
        }

        $this->serialize($exceptionEntity);
        return $this;
    }

    public function serializeDataProviderEntity(DataProviderEntity $entity)
    {
        $this->serialize($entity->collection);
        $this->response->headers->set(HttpHeaderEnum::PER_PAGE, $entity->pageSize);
        $this->response->headers->set(HttpHeaderEnum::PAGE_COUNT, $entity->pageCount);
        $this->response->headers->set(HttpHeaderEnum::TOTAL_COUNT, $entity->totalCount);
        $this->response->headers->set(HttpHeaderEnum::CURRENT_PAGE, $entity->page);
        return $this;
    }

    public function serialize($data)
    {
        $data = $this->encodeData($data);
        $this->response->setContent($data);
    }

    public function encodeData($data)
    {
        $context = [
            //AbstractNormalizer::IGNORED_ATTRIBUTES => ['createdAt']
        ];
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter)];

        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($data, 'json', $context);
        return $jsonContent;
    }

}