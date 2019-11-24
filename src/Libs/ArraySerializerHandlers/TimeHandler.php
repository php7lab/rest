<?php

namespace PhpLab\Rest\Libs\ArraySerializerHandlers;

use DateTime;
use PhpLab\Domain\Data\ArraySerializer;
use PhpLab\Domain\Data\ArraySerializerHandlers\SerializerHandlerInterface;

class TimeHandler implements SerializerHandlerInterface
{

    public $properties = [];
    public $recursive = true;

    /** @var ArraySerializer */
    public $parent;

    public function encode($object)
    {
        if ($object instanceof DateTime) {
            $object = $this->objectHandle($object);
        }
        return $object;
    }

    protected function objectHandle(DateTime $object): string
    {
        return $object->format('c');
    }
}