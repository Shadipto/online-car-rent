<?php

class OrderApiController extends BaseController
{
    private Car $cars;
    private Order $orders;

    public function __construct()
    {
        $this->cars = new Car();
        $this->orders = new Order();
    }

    public function calcCost(): void
    {
        $this->requireRole('member', true);
        $this->verifyCsrf(true);

        $carId = (string) ($_POST['car_id'] ?? '');
        $startDate = trim((string) ($_POST['start_date'] ?? ''));
        $endDate = trim((string) ($_POST['end_date'] ?? ''));

        if (!ctype_digit($carId)) {
            $this->json(['success' => false, 'message' => 'Invalid car.'], 422);
        }

        $car = $this->cars->getById((int) $carId);

        if ($car === null || (int) $car['availability_status'] !== 1) {
            $this->json(['success' => false, 'message' => 'Car is unavailable.'], 404);
        }

        $dateErrors = $this->validateDates($startDate, $endDate);

        if ($dateErrors !== []) {
            $this->json(['success' => false, 'message' => reset($dateErrors)], 422);
        }

        $days = $this->orders->rentalDays($startDate, $endDate);
        $totalCost = $this->orders->calcCost((float) $car['price_per_day'], $startDate, $endDate);

        $this->json([
            'success' => true,
            'days' => $days,
            'total_cost' => $totalCost,
            'formatted_total' => number_format($totalCost, 2),
        ]);
    }

    public function cancel(string $id): void
    {
        $this->requireRole('member', true);
        $this->verifyCsrf(true);

        if (!ctype_digit($id)) {
            $this->json(['success' => false, 'message' => 'Invalid order.'], 422);
        }

        $order = $this->orders->getForUser((int) $id, (int) Session::get('user_id'));

        if ($order === null) {
            $this->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ($order['status'] !== 'pending') {
            $this->json(['success' => false, 'message' => 'Only pending orders can be cancelled.'], 422);
        }

        $this->orders->updateStatus((int) $order['id'], (int) Session::get('user_id'), 'cancelled');

        $this->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'redirect' => base_url('/profile/history'),
        ]);
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
