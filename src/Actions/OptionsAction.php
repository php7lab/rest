<?php

namespace PhpLab\Rest\Actions;

use Symfony\Component\HttpFoundation\JsonResponse;

class OptionsAction extends BaseAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        return $response;
    }

}