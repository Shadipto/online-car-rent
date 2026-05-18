<?php

class OrderController extends BaseController
{
    private Car $cars;
    private Order $orders;
    private Payment $payments;

    public function __construct()
    {
        $this->cars = new Car();
        $this->orders = new Order();
        $this->payments = new Payment();
    }

    public function create(string $carId): void
    {
        $this->requireRole('member');
        $car = $this->findAvailableCar($carId);

        $this->render('orders/create', [
            'title' => 'Place Order',
            'car' => $car,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(string $carId): void
    {
        $this->requireRole('member');
        $this->verifyCsrf();
        $car = $this->findAvailableCar($carId);
        $data = $this->cleanDates($_POST);
        $errors = $this->validateDates($data['start_date'], $data['end_date']);

        if ($errors !== []) {
            $this->render('orders/create', [
                'title' => 'Place Order',
                'car' => $car,
                'errors' => $errors,
                'old' => $data,
            ]);
            return;
        }

        $totalCost = $this->orders->calcCost((float) $car['price_per_day'], $data['start_date'], $data['end_date']);
        $orderId = $this->orders->createOrder(
            (int) Session::get('user_id'),
            (int) $car['id'],
            $data['start_date'],
            $data['end_date'],
            $totalCost
        );

        $this->redirect('/orders/' . $orderId . '/invoice');
    }

    public function invoice(string $id): void
    {
        $this->requireRole('member');
        $order = $this->findMemberOrder($id);

        $this->render('orders/invoice', [
            'title' => 'Invoice',
            'order' => $order,
        ]);
    }

    public function finalize(string $id): void
    {
        $this->requireRole('member');
        $this->verifyCsrf();
        $order = $this->findMemberOrder($id);

        if ($order['status'] !== 'pending') {
            Session::flash('error', 'Only pending orders can be finalized.');
            $this->redirect('/orders/' . $order['id'] . '/invoice');
        }

        $this->redirect('/orders/' . $order['id'] . '/payment');
    }

    public function payment(string $id): void
    {
        $this->requireRole('member');
        $order = $this->findMemberOrder($id);

        if ($order['status'] !== 'pending') {
            Session::flash('error', 'Payment is only available for pending orders.');
            $this->redirect('/orders/' . $order['id'] . '/invoice');
        }

        $this->render('orders/payment', [
            'title' => 'Payment',
            'order' => $order,
            'paymentMethods' => Payment::METHODS,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function processPayment(string $id): void
    {
        $this->requireRole('member');
        $this->verifyCsrf();
        $order = $this->findMemberOrder($id);
        $method = (string) ($_POST['payment_method'] ?? '');
        $transactionId = trim((string) ($_POST['transaction_id'] ?? ''));
        $errors = [];

        if ($order['status'] !== 'pending') {
            $errors['order'] = 'Only pending orders can be paid.';
        }

        if (!array_key_exists($method, Payment::METHODS)) {
            $errors['payment_method'] = 'Choose a valid payment method.';
        }

        if (strlen($transactionId) > 100) {
            $errors['transaction_id'] = 'Transaction ID must be under 100 characters.';
        }

        if ($errors !== []) {
            $this->render('orders/payment', [
                'title' => 'Payment',
                'order' => $order,
                'paymentMethods' => Payment::METHODS,
                'errors' => $errors,
                'old' => ['payment_method' => $method, 'transaction_id' => $transactionId],
            ]);
            return;
        }

        $pdo = Database::connect();
        $pdo->beginTransaction();

        try {
            $this->payments->createPayment((int) $order['id'], (float) $order['total_cost'], $method, $transactionId ?: null);
            $this->orders->confirmWithPaymentMethod((int) $order['id'], (int) Session::get('user_id'), $method);
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }

        Session::flash('success', 'Payment completed successfully.');
        $this->redirect('/orders/' . $order['id'] . '/success');
    }

    public function success(string $id): void
    {
        $this->requireRole('member');
        $order = $this->findMemberOrder($id);
        $payment = $this->payments->getByOrder((int) $order['id']);

        if ($order['status'] !== 'confirmed') {
            Session::flash('error', 'Order is not confirmed yet.');
            $this->redirect('/orders/' . $order['id'] . '/invoice');
        }

        $this->render('orders/success', [
            'title' => 'Order Success',
            'order' => $order,
            'payment' => $payment,
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    private function findAvailableCar(string $carId): array
    {
        if (!ctype_digit($carId)) {
            $this->abort(404, 'Car not found.');
        }

        $car = $this->cars->getById((int) $carId);

        if ($car === null || (int) $car['availability_status'] !== 1) {
            $this->abort(404, 'Car not found or unavailable.');
        }

        return $car;
    }

    private function findMemberOrder(string $id): array
    {
        if (!ctype_digit($id)) {
            $this->abort(404, 'Order not found.');
        }

        $order = $this->orders->getForUser((int) $id, (int) Session::get('user_id'));

        if ($order === null) {
            $this->abort(404, 'Order not found.');
        }

        return $order;
    }

    private function cleanDates(array $input): array
    {
        return [
            'start_date' => trim((string) ($input['start_date'] ?? '')),
            'end_date' => trim((string) ($input['end_date'] ?? '')),
        ];
    }

    private function validateDates(string $startDate, string $endDate): array
    {
        $errors = [];
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);
        $today = new DateTimeImmutable('today');

        if (!$start || $start->format('Y-m-d') !== $startDate) {
            $errors['start_date'] = 'Choose a valid start date.';
        }

        if (!$end || $end->format('Y-m-d') !== $endDate) {
            $errors['end_date'] = 'Choose a valid end date.';
        }

        if ($errors === []) {
            if ($start < $today) {
                $errors['start_date'] = 'Start date cannot be in the past.';
            }

            if ($end <= $start) {
                $errors['end_date'] = 'End date must be after the start date.';
            }
        }

        return $errors;
    }
}
