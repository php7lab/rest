<?php

namespace PhpLab\Rest\Helpers;

use PhpLab\Core\Enums\Http\HttpHeaderEnum;
use Psr\Http\Message\ResponseInterface;

class RestHelper
{

    public static function getDataFromResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine(HttpHeaderEnum::CONTENT_TYPE);
        $data = $response->getBody()->getContents();
        if($contentType == 'application/json') {
            $data = json_decode($data, true);
        }
        return $data;
    }

}
