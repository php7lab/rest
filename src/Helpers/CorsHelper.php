<?php

namespace PhpLab\Rest\Helpers;

use php7extension\yii\filters\Cors;
use php7rails\app\helpers\EnvService;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Enums\Web\HttpHeaderEnum;
use PhpLab\Core\Enums\Web\HttpMethodEnum;
use PhpLab\Core\Enums\Web\HttpServerEnum;
use Symfony\Component\HttpFoundation\Response;

class CorsHelper
{

    public static function autoload()
    {
        $headers = self::generateHeaders();
        $response = new Response('', 200, $headers);
        $response->sendHeaders();
        if ($_SERVER[HttpServerEnum::REQUEST_METHOD] == HttpMethodEnum::OPTIONS) {
            exit;
        }
    }

    private static function generateHeaders(): array
    {
        $headers = [
            HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN => '*',
            HttpHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS => ArrayHelper::getValue($_SERVER, HttpServerEnum::HTTP_ACCESS_CONTROL_REQUEST_HEADERS),
            HttpHeaderEnum::ACCESS_CONTROL_ALLOW_METHODS => implode(', ', HttpMethodEnum::values()),
            /*
            HttpHeaderEnum::ACCESS_CONTROL_ALLOW_ORIGIN => ArrayHelper::getValue($_SERVER, HttpServerEnum::HTTP_ORIGIN),
            HttpHeaderEnum::ACCESS_CONTROL_ALLOW_CREDENTIALS => 'true',
            HttpHeaderEnum::ACCESS_CONTROL_MAX_AGE => 3600,
            HttpHeaderEnum::ACCESS_CONTROL_EXPOSE_HEADERS => [
                HttpHeaderEnum::CONTENT_TYPE,
                HttpHeaderEnum::LINK,
                HttpHeaderEnum::ACCESS_TOKEN,
                HttpHeaderEnum::AUTHORIZATION,
                HttpHeaderEnum::TIME_ZONE,
                HttpHeaderEnum::TOTAL_COUNT,
                HttpHeaderEnum::PAGE_COUNT,
                HttpHeaderEnum::CURRENT_PAGE,
                HttpHeaderEnum::PER_PAGE,
                HttpHeaderEnum::X_ENTITY_ID,
                HttpHeaderEnum::X_AGENT_FINGERPRINT,
            ],
            */
        ];
        return $headers;
    }

}
