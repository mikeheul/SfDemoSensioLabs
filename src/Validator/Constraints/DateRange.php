<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DateRange extends Constraint
{
    public $message = 'The start date cannot be later than the end date.';
}
