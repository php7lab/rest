<?php

namespace PhpLab\Rest\Base;

use PhpLab\Core\Domain\Data\Query;
use PhpLab\Core\Domain\Interfaces\Service\CrudServiceInterface;
use PhpLab\Core\Domain\Libs\GetParams;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseAction
 * @package PhpLab\Rest\Actions
 *
 * @property CrudServiceInterface $service
 */
abstract class BaseAction
{

    /** @var $service */
    public $service;

    /** @var Request */
    public $request;

    /** @var Query */
    public $query;

    public function __construct(object $service, Request $request)
    {
        $this->service = $service;
        $this->request = $request;
        $this->query = $this->forgeQueryFromRequest($request);
    }

    abstract public function run(): JsonResponse;

    private function forgeQueryFromRequest(Request $request)
    {
        $getParams = new GetParams;
        return $getParams->getAllParams($request->query->all());
    }

}