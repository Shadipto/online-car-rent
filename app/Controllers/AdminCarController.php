<?php

class AdminCarController extends BaseController
{
    private Car $cars;

    public function __construct()
    {
        $this->cars = new Car();
    }

    public function index(): void
    {
        $this->requireRole('admin');

        $this->render('admin/cars/index', [
            'title' => 'Manage Cars',
            'cars' => $this->cars->findAll(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->requireRole('admin');

        $this->render('admin/cars/form', [
            'title' => 'Create Car',
            'mode' => 'create',
            'car' => [],
            'types' => Car::TYPES,
            'errors' => [],
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $data = $this->cleanInput($_POST);
        $errors = $this->validateInput($data);
        $imagePath = null;

        if ($errors === [] && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$imagePath, $uploadError] = $this->storeCarImage($_FILES['image']);

            if ($uploadError !== null) {
                $errors['image'] = $uploadError;
            }
        }

        if ($errors !== []) {
            $this->render('admin/cars/form', [
                'title' => 'Create Car',
                'mode' => 'create',
                'car' => $data,
                'types' => Car::TYPES,
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $data['image_path'] = $imagePath;
        $this->cars->createCar($data);
        Session::flash('success', 'Car created successfully.');
        $this->redirect('/admin/cars');
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $car = $this->findCar($id);

        $this->render('admin/cars/form', [
            'title' => 'Edit Car',
            'mode' => 'edit',
            'car' => $car,
            'types' => Car::TYPES,
            'errors' => [],
        ], 'admin');
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $car = $this->findCar($id);
        $data = $this->cleanInput($_POST);
        $errors = $this->validateInput($data);
        $imagePath = $car['image_path'] ?? null;

        if ($errors === [] && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$newImagePath, $uploadError] = $this->storeCarImage($_FILES['image'], $imagePath);

            if ($uploadError !== null) {
                $errors['image'] = $uploadError;
            } else {
                $imagePath = $newImagePath;
            }
        }

        if ($errors !== []) {
            $this->render('admin/cars/form', [
                'title' => 'Edit Car',
                'mode' => 'edit',
                'car' => array_merge($car, $data),
                'types' => Car::TYPES,
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $data['image_path'] = $imagePath;
        $this->cars->updateCar((int) $car['id'], $data);
        Session::flash('success', 'Car updated successfully.');
        $this->redirect('/admin/cars');
    }

    public function destroy(string $id): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $car = $this->findCar($id);

        if ($this->cars->hasOrders((int) $car['id'])) {
            Session::flash('error', 'This car has orders and cannot be deleted safely.');
            $this->redirect('/admin/cars');
        }

        $this->cars->deleteCar((int) $car['id']);
        $this->removeCarImage($car['image_path'] ?? null);
        Session::flash('success', 'Car deleted successfully.');
        $this->redirect('/admin/cars');
    }

    private function findCar(string $id): array
    {
        if (!ctype_digit($id)) {
            $this->abort(404, 'Car not found.');
        }

        $car = $this->cars->getById((int) $id);

        if ($car === null) {
            $this->abort(404, 'Car not found.');
        }

        return $car;
    }

    private function cleanInput(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'model' => trim((string) ($input['model'] ?? '')),
            'type' => trim((string) ($input['type'] ?? '')),
            'price_per_day' => trim((string) ($input['price_per_day'] ?? '')),
            'availability_status' => !empty($input['availability_status']) ? 1 : 0,
            'description' => trim((string) ($input['description'] ?? '')),
        ];
    }

    private function validateInput(array $data): array
    {
        $errors = [];

        if ($data['name'] === '' || strlen($data['name']) > 100) {
            $errors['name'] = 'Name is required and must be under 100 characters.';
        }

        if ($data['model'] === '' || strlen($data['model']) > 100) {
            $errors['model'] = 'Model is required and must be under 100 characters.';
        }

        if (!in_array($data['type'], Car::TYPES, true)) {
            $errors['type'] = 'Choose a valid car type.';
        }

        if (!is_numeric($data['price_per_day']) || (float) $data['price_per_day'] <= 0) {
            $errors['price_per_day'] = 'Price per day must be greater than zero.';
        }

        return $errors;
    }

    private function storeCarImage(array $file, ?string $oldPath = null): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Car image upload failed.'];
        }

        if ((int) $file['size'] > (int) app_config('upload_max_size', 2 * 1024 * 1024)) {
            return [null, 'Car image must be 2MB or smaller.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (!isset($extensions[$mime])) {
            return [null, 'Car image must be a JPEG or PNG image.'];
        }

        $uploadDir = root_path('public/uploads/cars');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = 'car_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [null, 'Could not save car image.'];
        }

        $this->removeCarImage($oldPath);

        return ['uploads/cars/' . $filename, null];
    }

    private function removeCarImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $uploadDir = root_path('public/uploads/cars');
        $imageFile = root_path('public/' . ltrim($path, '/'));
        $carsDir = realpath($uploadDir);
        $imageReal = realpath($imageFile);

        if ($carsDir && $imageReal && str_starts_with($imageReal, $carsDir) && is_file($imageReal)) {
            unlink($imageReal);
        }
    }
}
