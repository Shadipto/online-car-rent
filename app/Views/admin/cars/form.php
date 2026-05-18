<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? base_url('/admin/cars/' . $car['id'] . '/edit') : base_url('/admin/cars/create');
?>

<section class="page-header">
    <h1><?= $isEdit ? 'Edit Car' : 'Add Car' ?></h1>
    <p class="muted"><?= $isEdit ? 'Update this car listing.' : 'Create a new car listing.' ?></p>
</section>

<?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="form-card js-car-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

    <label>
        Name
        <input type="text" name="name" value="<?= e($car['name'] ?? '') ?>" maxlength="100" required>
    </label>

    <label>
        Model
        <input type="text" name="model" value="<?= e($car['model'] ?? '') ?>" maxlength="100" required>
    </label>

    <label>
        Type
        <select name="type" required>
            <option value="">Choose type</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= e($type) ?>" <?= ($car['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Price Per Day
        <input type="number" name="price_per_day" value="<?= e($car['price_per_day'] ?? '') ?>" min="0.01" step="0.01" required>
    </label>

    <label class="inline-check">
        <input type="checkbox" name="availability_status" value="1" <?= (int) ($car['availability_status'] ?? 1) === 1 ? 'checked' : '' ?>>
        Available
    </label>

    <label>
        Description
        <textarea name="description" rows="4"><?= e($car['description'] ?? '') ?></textarea>
    </label>

    <?php if ($isEdit && !empty($car['image_path'])): ?>
        <div class="current-image">
            <span>Current image</span>
            <img src="<?= e(base_url('/' . $car['image_path'])) ?>" alt="<?= e($car['name']) ?>">
        </div>
    <?php endif; ?>

    <label>
        Car Image
        <input type="file" name="image" accept="image/jpeg,image/png">
    </label>

    <button type="submit"><?= $isEdit ? 'Save Changes' : 'Create Car' ?></button>
</form>
