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

    public function run(): string
    {
        $this->reader->prepare();

        $this->writer->prepare();

        $tables = $this->reader->read();

        return $this->writer->write($tables);
    }

    public static function create(): self
    {
        $driver = Config::getValue('driver', 'mysql');
        $format = Config::getValue('format', 'excel_jp');

        $reader = match ($driver) {
            'mysql' => new MySQLReader(),
            default => throw new Exception('Unknown driver ' . $driver),
        };

        $writer = match ($format) {
            'excel_jp' => new ExcelJPWriter(),
            default => throw new Exception('Unknown format ' . $format),
        };

        return new self($reader, $writer);
    }
}
