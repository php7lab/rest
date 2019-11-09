<?php

namespace PhpLab\Rest\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteAction extends BaseEntityAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $this->service->deleteById($this->id);
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        return $response;
    }

}