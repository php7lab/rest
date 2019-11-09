<?php

namespace PhpLab\Rest\Lib;

use php7extension\core\exceptions\NotFoundException;
use php7extension\core\web\enums\HttpHeaderEnum;
use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Domain\Data\ArraySerializer;
use PhpLab\Domain\Data\ArraySerializerHandlers\ArrayHandler;
use PhpLab\Domain\Data\ArraySerializerHandlers\ObjectHandler;
use PhpLab\Domain\Data\Collection;
use PhpLab\Domain\Data\DataProviderEntity;
use PhpLab\Rest\Entity\ExceptionEntity;
use PhpLab\Rest\Lib\ArraySerializerHandlers\TimeHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JsonRestSerializer
{

    private $exceptionStatusCodeMap = [
        NotFoundHttpException::class => 404,
        NotFoundException::class => 404,
        MethodNotAllowedHttpException::class => 405,
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
        $this->response->setData($data);
    }

    public function encodeData($data)
    {
        /*$serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $data = $serializer->serialize($data, 'json');
        $this->response->setContent($data);*/

        $arraySerializer = new ArraySerializer($this->serializerHandlers);
        $closure = function ($value) use ($arraySerializer) {
            return $arraySerializer->toArray($value);
        };
        if ($data instanceof Collection) {
            $data = $data->map($closure)->all();
        } elseif (is_array($data) || is_object($data)) {
            $data = $closure($data);
        }
        return $data;
    }

}