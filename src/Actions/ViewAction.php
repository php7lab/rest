<?php

namespace PhpLab\Rest\Actions;

use PhpLab\Rest\Libs\JsonRestSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ViewAction extends BaseEntityAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        try {
            $entity = $this->service->oneById($this->id, $this->query);
            $serializer = new JsonRestSerializer($response);
            $serializer->serialize($entity);
        } catch (\php7extension\core\exceptions\NotFoundException $e) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        return $response;
    }

}