<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

interface Writer
{
    /**
     * @param Table[] $tables
     */
    public function write(array $tables, ?string $schema = null): string;
}
