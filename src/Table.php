<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

class Table
{
    public function __construct(
        public string $name,
        public ?string $comment = null,
        /** @var Column[] $columns */
        public array $columns = [],
        /** @var Index[] $indexes */
        public array $indexes = [],
    ) {
    }
}
