<?php

namespace PhpLab\Rest\Action;

use PhpLab\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Rest\Lib\JsonRestSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use php7extension\core\exceptions\NotFoundException;
use PhpLab\Rest\Helpers\RestRenderHelper;

class UpdateAction extends BaseEntityAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $body = $this->request->request->all();
        try {
            $this->service->updateById($this->id, $body);
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (UnprocessibleEntityException $e) {
            $violations = $e->getErrorCollection();
            $errorCollection = RestRenderHelper::prepareUnprocessible($violations);
            $serializer = new JsonRestSerializer($response);
            $serializer->serialize($errorCollection);
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        return $response;
    }

}