<?php

class Order extends BaseModel
{
    protected string $table = 'orders';
    protected array $fillable = [
        'user_id',
        'car_id',
        'start_date',
        'end_date',
        'total_cost',
        'status',
        'payment_method',
    ];

    public function createOrder(int $userId, int $carId, string $startDate, string $endDate, float $totalCost): string
    {
        return $this->insert([
            'user_id' => $userId,
            'car_id' => $carId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_cost' => $totalCost,
            'status' => 'pending',
        ]);
    }

    public function getById(int $id): ?array
    {
        $order = Database::query(
            'SELECT orders.*, cars.name AS car_name, cars.model AS car_model, cars.type AS car_type,
                    cars.price_per_day, cars.image_path, cars.description
             FROM orders
             INNER JOIN cars ON cars.id = orders.car_id
             WHERE orders.id = :id
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return $order ?: null;
    }

    public function getForUser(int $id, int $userId): ?array
    {
        $order = Database::query(
            'SELECT orders.*, cars.name AS car_name, cars.model AS car_model, cars.type AS car_type,
                    cars.price_per_day, cars.image_path, cars.description
             FROM orders
             INNER JOIN cars ON cars.id = orders.car_id
             WHERE orders.id = :id AND orders.user_id = :user_id
             LIMIT 1',
            ['id' => $id, 'user_id' => $userId]
        )->fetch();

        return $order ?: null;
    }

    public function updateStatus(int $id, int $userId, string $status): bool
    {
        return Database::exec(
            'UPDATE orders SET status = :status WHERE id = :id AND user_id = :user_id',
            ['status' => $status, 'id' => $id, 'user_id' => $userId]
        ) > 0;
    }

    public function confirmWithPaymentMethod(int $id, int $userId, string $paymentMethod): bool
    {
        return Database::exec(
            'UPDATE orders
             SET status = :status, payment_method = :payment_method
             WHERE id = :id AND user_id = :user_id',
            [
                'status' => 'confirmed',
                'payment_method' => $paymentMethod,
                'id' => $id,
                'user_id' => $userId,
            ]
        ) > 0;
    }

    public function getByUser(int $userId): array
    {
        return Database::query(
            'SELECT orders.*, cars.name AS car_name, cars.model AS car_model, cars.type AS car_type,
                    cars.price_per_day, cars.image_path
             FROM orders
             INNER JOIN cars ON cars.id = orders.car_id
             WHERE orders.user_id = :user_id AND orders.status IN (:confirmed, :cancelled)
             ORDER BY orders.order_date DESC, orders.id DESC',
            ['user_id' => $userId, 'confirmed' => 'confirmed', 'cancelled' => 'cancelled']
        )->fetchAll();
    }

    public function calcCost(float $pricePerDay, string $startDate, string $endDate): float
    {
        $days = $this->rentalDays($startDate, $endDate);

        return round($pricePerDay * $days, 2);
    }

    public function rentalDays(string $startDate, string $endDate): int
    {
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);

        return max(1, (int) $start->diff($end)->days);
    }

    public function getAll(): array
    {
        return Database::query(
            'SELECT orders.*, users.name AS member_name, users.email AS member_email,
                    cars.name AS car_name, cars.model AS car_model, cars.type AS car_type
             FROM orders
             INNER JOIN users ON users.id = orders.user_id
             INNER JOIN cars ON cars.id = orders.car_id
             ORDER BY orders.order_date DESC, orders.id DESC'
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int) Database::query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }
}
