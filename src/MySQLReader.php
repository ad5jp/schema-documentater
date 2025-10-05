<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

use PDO;

class MySQLReader implements Reader
{
    public ?string $schema;
    public ?string $host;
    public ?string $user;
    public ?string $password;

    public function __construct()
    {
        $this->schema = Config::getValue('schema');
        $this->host = Config::getValue('host');
        $this->user = Config::getValue('user');
        $this->password = Config::getValue('password');
    }

    public function prepare(): void
    {
        while (empty($this->host)) {
            echo "Host:";
            $this->host = trim(fgets(STDIN));
        }

        while (empty($this->schema)) {
            echo "Schema:";
            $this->schema = trim(fgets(STDIN));
        }

        while (empty($this->user)) {
            echo "User:";
            $this->user = trim(fgets(STDIN));
        }

        while (empty($this->password)) {
            echo "Password:";
            system('stty -echo');
            $this->password = trim(fgets(STDIN));
            system('stty echo');
        }
    }

    /**
     * @inheritdoc
     */
    public function read(): array
    {
        $pdo = new PDO("mysql:dbname={$this->schema};host={$this->host}", $this->user, $this->password);
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
