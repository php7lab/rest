<?php

namespace PhpLab\Rest\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateAction extends BaseAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $body = $this->request->request->all();
        $entity = $this->service->create($body);
        $response->setStatusCode(Response::HTTP_CREATED);
        //$response->setData($collection);
        $response->headers->set('X-Entity-Id', $entity->id);
        //$location = $this->generateUrl('app_crud_view', ['id', 3], UrlGeneratorInterface::ABSOLUTE_URL);
        //$response->headers->set('Location', $location);
        return $response;
    }

}