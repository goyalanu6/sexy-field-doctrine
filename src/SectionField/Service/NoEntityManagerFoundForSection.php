<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Throwable;

class NoEntityManagerFoundForSection extends \Exception
{
    public function __construct($message = "No entity manager found", Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
