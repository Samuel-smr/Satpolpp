<?php

namespace App\Support;

readonly class Employee
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $employee_number,
        public string $name,
        public ?string $position = null,
    ) {}
}
