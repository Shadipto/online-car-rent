<?php

class CarController extends BaseController
{
    private Car $cars;

    public function __construct()
    {
        $this->cars = new Car();
    }

    public function index(): void
    {
        $type = trim((string) ($_GET['type'] ?? ''));

        $this->render('cars/index', [
            'title' => $type !== '' ? $type . ' Cars' : 'Cars',
            'cars' => $this->cars->getAvailable($type),
            'categories' => $this->cars->getDistinctTypes(),
            'selectedType' => $type,
        ]);
    }

    public function show(string $id): void
    {
        if (!ctype_digit($id)) {
            $this->abort(404, 'Car not found.');
        }

        $car = $this->cars->getById((int) $id);

        if ($car === null) {
            $this->abort(404, 'Car not found.');
        }

        $this->render('cars/show', [
            'title' => $car['name'],
            'car' => $car,
        ]);
    }

    public function apiIndex(): void
    {
        $type = trim((string) ($_GET['type'] ?? ''));
        $cars = array_map(function (array $car): array {
            return [
                'id' => (int) $car['id'],
                'name' => $car['name'],
                'model' => $car['model'],
                'type' => $car['type'],
                'price_per_day' => (float) $car['price_per_day'],
                'image_path' => $car['image_path'],
                'url' => base_url('/cars/' . $car['id']),
            ];
        }, $this->cars->getAvailable($type));

        $this->json([
            'success' => true,
            'cars' => $cars,
        ]);
    }
}
