<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

use Exception;

class Documentater
{
    public function __construct(
        private Reader $reader,
        private Writer $writer
    ) {

    }

    public function run(string $host, string $schema, string $user, string $password): string
    {
        $tables = $this->reader->read($host, $schema, $user, $password);

        return $this->writer->write($tables, $schema);
    }

    public static function create(string $driver, string $format): self
    {
        $reader = match ($driver) {
            'mysql' => new MySQLReader(),
            default => throw new Exception('Unknown driveer ' . $driver),
        };

        $writer = match ($format) {
            'excel' => new ExcelWriter(),
            default => throw new Exception('Unknown format ' . $format),
        };

        return new self($reader, $writer);
    }
}
