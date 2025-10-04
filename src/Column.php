<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $collation = null,
        public bool $nullable = false,
        public ?string $default = null,
        public ?string $extra = null,
        public ?string $comment = null,
    ) {
    }
}
