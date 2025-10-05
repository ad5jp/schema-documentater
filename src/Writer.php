<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

interface Writer
{
    public function prepare(): void;

    /**
     * @param Table[] $tables
     */
    public function write(array $tables): string;
}
