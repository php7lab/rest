<?php

namespace PhpLab\Rest\Action;

use PhpLab\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Rest\Lib\JsonRestSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PhpLab\Rest\Helpers\RestRenderHelper;

class CreateAction extends BaseAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $body = $this->request->request->all();
        try {
            $entity = $this->service->create($body);
            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set('X-Entity-Id', $entity->id);
        } catch (UnprocessibleEntityException $e) {
            $violations = $e->getErrorCollection();
            $errorCollection = RestRenderHelper::prepareUnprocessible($violations);
            $serializer = new JsonRestSerializer($response);
            $serializer->serialize($errorCollection);
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        //$location = $this->generateUrl('app_crud_view', ['id', 3], UrlGeneratorInterface::ABSOLUTE_URL);
        //$response->headers->set('Location', $location);
        return $response;
    }

}