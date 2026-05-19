<?php

class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected array $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'transaction_id',
    ];

    public const METHODS = [
        'credit_card' => 'Credit Card',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'bank_transfer' => 'Bank Transfer',
        'cash_on_delivery' => 'Cash on Delivery',
    ];

    public function createPayment(int $orderId, float $amount, string $paymentMethod, ?string $transactionId = null): string
    {
        return $this->insert([
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ]);
    }

    public function getByOrder(int $orderId): ?array
    {
        $payment = Database::query(
            'SELECT * FROM payments WHERE order_id = :order_id ORDER BY payment_date DESC, id DESC LIMIT 1',
            ['order_id' => $orderId]
        )->fetch();

        return $payment ?: null;
    }
}
