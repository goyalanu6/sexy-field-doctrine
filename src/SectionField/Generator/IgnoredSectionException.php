<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Generator;

class IgnoredSectionException extends \Exception
{
    public function __construct($message = "This section is ignored", $code = 422, Throwable $previous = null) {

    }
}
