<section class="page-header">
    <h1>Rent <?= e($car['name']) ?></h1>
    <p class="muted"><?= e($car['model']) ?> - BDT <?= e(number_format((float) $car['price_per_day'], 2)) ?> per day</p>
</section>

<?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="order-layout">
    <article class="car-card order-summary-card">
        <?php if (!empty($car['image_path'])): ?>
            <img src="<?= e(base_url('/' . $car['image_path'])) ?>" alt="<?= e($car['name']) ?>">
        <?php else: ?>
            <div class="image-placeholder">No image</div>
        <?php endif; ?>
        <div class="car-card-body">
            <p class="eyebrow"><?= e($car['type']) ?></p>
            <h3><?= e($car['name']) ?></h3>
            <p><?= e($car['description'] ?: 'No description has been added for this car yet.') ?></p>
        </div>
    </article>

    <form class="form-card js-order-form" method="post" action="<?= e(base_url('/orders/create/' . $car['id'])) ?>" data-price="<?= e($car['price_per_day']) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">
        <input type="hidden" name="car_id" value="<?= e($car['id']) ?>">

        <label>
            Start Date
            <input type="date" name="start_date" value="<?= e($old['start_date'] ?? '') ?>" required>
        </label>

        <label>
            End Date
            <input type="date" name="end_date" value="<?= e($old['end_date'] ?? '') ?>" required>
        </label>

        <div class="cost-preview" data-cost-preview>
            Select dates to calculate total cost.
        </div>

        <button type="submit">Create Invoice</button>
    </form>
</div>
