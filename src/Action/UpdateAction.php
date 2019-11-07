<?php

namespace PhpLab\Rest\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateAction extends BaseEntityAction
{

    public function run() : JsonResponse {
        $response = new JsonResponse;
        $body = $this->request->request->all();
        $this->service->updateById($this->id, $body);
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        return $response;
    }

}