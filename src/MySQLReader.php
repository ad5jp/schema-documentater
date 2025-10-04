<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

use PDO;

class MySQLReader implements Reader
{
    /**
     * @inheritdoc
     */
    public function read(string $host, string $schema, string $user, string $password): array
    {
        $pdo = new PDO("mysql:dbname={$schema};host={$host}", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query('show table status');
        $table_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($table_info) use ($pdo) {
            $table = new Table(
                name: $table_info['Name'],
                comment: $table_info['Comment'],
            );

            $stmt = $pdo->query('show full columns from `' . $table->name . '`');
            $column_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $table->columns = array_map(function ($column_info) {
                return new Column(
                    name: $column_info['Field'],
                    type: $column_info['Type'],
                    collation: $column_info['Collation'],
                    nullable: $column_info['Null'] === 'YES',
                    default: $column_info['Default'],
                    extra: $column_info['Extra'],
                    comment: $column_info['Comment'],
                );
            }, $column_list);

            $stmt = $pdo->query('show index from `' . $table->name . '`');
            $index_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($index_list as $index_info) {
                $exists = array_search($index_info['Key_name'], array_column($table->indexes, 'name'));

                if ($exists !== false) {
                    $table->indexes[$exists]->columns[] = $index_info['Column_name'];
                } else {
                    $table->indexes[] = new Index(
                        name: $index_info['Key_name'],
                        columns: [$index_info['Column_name']],
                        unique: $index_info['Non_unique'] === 0,
                    );
                }
            }

            return $table;
        }, $table_list);
    }
}
