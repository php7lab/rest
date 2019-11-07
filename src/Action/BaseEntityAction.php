<?php

namespace PhpLab\Rest\Action;

use PhpLab\Domain\Interfaces\CrudServiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaseEntityAction
 * @package PhpLab\Rest\Action
 *
 * @property CrudServiceInterface $service
 */
abstract class BaseEntityAction extends BaseAction
{

    public $id;

    public function __construct(object $service, Request $request, $id)
    {
        parent::__construct($service, $request);
        $this->id = $id;
    }

}