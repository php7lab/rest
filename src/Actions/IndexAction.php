<?php

namespace PhpLab\Rest\Actions;

use PhpLab\Core\Domain\Libs\DataProvider;
use PhpLab\Rest\Base\BaseAction;
use PhpLab\Rest\Libs\Serializer\JsonRestSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexAction extends BaseAction
{

    public function run(): JsonResponse
    {
        $response = new JsonResponse;
        $dp = new DataProvider([
            'service' => $this->service,
            'query' => $this->query,
            'page' => $this->request->get("page", 1),
            'pageSize' => $this->request->get("per-page", 10),
        ]);
        $serializer = new JsonRestSerializer($response);
        $serializer->serializeDataProviderEntity($dp->getAll());
        return $response;
    }

}