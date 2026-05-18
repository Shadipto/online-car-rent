<?php

class Car extends BaseModel
{
    public const TYPES = ['Private car', 'Microbus', 'Pick-up', 'SUV', 'Sedan', 'Other'];

    protected string $table = 'cars';
    protected array $fillable = [
        'name',
        'model',
        'type',
        'price_per_day',
        'availability_status',
        'image_path',
        'description',
    ];

    public function getFeatured(int $limit = 6): array
    {
        $limit = max(1, min($limit, 12));

        return Database::query(
            "SELECT * FROM cars WHERE availability_status = 1 ORDER BY created_at DESC, id DESC LIMIT {$limit}"
        )->fetchAll();
    }

    public function getByType(string $type): array
    {
        return Database::query(
            'SELECT * FROM cars WHERE type = :type ORDER BY created_at DESC, id DESC',
            ['type' => $type]
        )->fetchAll();
    }

    public function getAvailable(?string $type = null): array
    {
        if ($type !== null && $type !== '') {
            return $this->getByType($type);
        }

        return Database::query(
            'SELECT * FROM cars ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function getDistinctTypes(): array
    {
        return Database::query(
            'SELECT DISTINCT type FROM cars ORDER BY type ASC'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createCar(array $data): string
    {
        return $this->insert($data);
    }

    public function updateCar(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function deleteCar(int $id): bool
    {
        return $this->delete($id);
    }

    public function hasOrders(int $id): bool
    {
        return (bool) Database::query(
            'SELECT id FROM orders WHERE car_id = :car_id LIMIT 1',
            ['car_id' => $id]
        )->fetch();
    }

    public function countAll(): int
    {
        return (int) Database::query('SELECT COUNT(*) FROM cars')->fetchColumn();
    }
}
