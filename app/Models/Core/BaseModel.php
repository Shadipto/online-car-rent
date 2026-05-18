<?php

abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $record = Database::query($sql, ['id' => $id])->fetch();

        return $record ?: null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC";

        return Database::query($sql)->fetchAll();
    }

    public function insert(array $data): string
    {
        $data = $this->onlyFillable($data);

        if ($data === []) {
            throw new InvalidArgumentException('Insert data cannot be empty.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn (string $column): string => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::query($sql, $data);

        return Database::lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->onlyFillable($data);

        if ($data === []) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }

        $sets = array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($data));
        $data['id'] = $id;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :id',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        return Database::exec($sql, $data) > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";

        return Database::exec($sql, ['id' => $id]) > 0;
    }

    protected function onlyFillable(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}
