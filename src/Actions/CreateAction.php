<?php

namespace PhpLab\Rest\Actions;

use PhpLab\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Rest\Libs\JsonRestSerializer;
use PhpLab\Sandbox\Web\Enums\HttpHeaderEnum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateAction extends BaseAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $body = $this->request->request->all();
        try {
            $entity = $this->service->create($body);
            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set(HttpHeaderEnum::X_ENTITY_ID, $entity->getId());
        } catch (UnprocessibleEntityException $e) {
            $errorCollection = $e->getErrorCollection();
            $serializer = new JsonRestSerializer($response);
            $serializer->serialize($errorCollection);
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        //$location = $this->generateUrl('app_crud_view', ['id', 3], UrlGeneratorInterface::ABSOLUTE_URL);
        //$response->headers->set('Location', $location);
        return $response;
    }

}