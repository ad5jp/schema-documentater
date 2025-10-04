<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

interface Reader
{
    /**
     * @return Table[]
     */
    public function read(string $host, string $schema, string $user, string $password): array;
}
