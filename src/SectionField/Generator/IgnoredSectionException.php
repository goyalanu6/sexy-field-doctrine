<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Generator;

class IgnoredSectionException extends \Exception
{
    public function __construct($message = "This section is ignored", Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
