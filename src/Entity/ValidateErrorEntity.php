<?php

namespace PhpLab\Rest\Entity;

use Symfony\Component\Validator\ConstraintViolationInterface;

class ValidateErrorEntity
{

    public $field;
    public $message;
    private $violation;

    public function setViolation(ConstraintViolationInterface $violation) {
        $this->violation = $violation;
    }

    public function getViolation() {
        return $this->violation;
    }

}