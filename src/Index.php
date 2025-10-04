<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

class Index
{
    public function __construct(
        public string $name,
        public array $columns,
        public bool $unique = false,
    ) {
    }
}
