<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

interface Reader
{
    public function prepare(): void;

    /**
     * @return Table[]
     */
    public function read(): array;
}
