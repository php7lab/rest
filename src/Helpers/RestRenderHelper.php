<?php

namespace PhpLab\Rest\Helpers;

use PhpLab\Domain\Data\Collection;
use PhpLab\Rest\Entity\ValidateErrorEntity;
use Symfony\Component\Validator\ConstraintViolationList;

class RestRenderHelper
{

    /**
     * @param   array | ConstraintViolationList[] $violations
     * @return  array | Collection | ValidateErrorEntity[]
     */
    public static function prepareUnprocessible(array $violations) : Collection {
        $collection = new Collection;
        foreach ($violations as $name => $violationList) {
            foreach ($violationList as $violation) {
                $violation->getCode();
                $entity = new ValidateErrorEntity;
                $entity->field = $name;
                $entity->message = $violation->getMessage();
                $collection->add($entity);
            }
        }
        return $collection;
    }

}