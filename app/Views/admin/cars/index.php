<section class="page-header admin-heading">
    <div>
        <h1>Cars</h1>
        <p class="muted">Create, edit, and delete car listings.</p>
    </div>
    <a class="button-link" href="<?= e(base_url('/admin/cars/create')) ?>">Add Car</a>
</section>

<?php if ($cars === []): ?>
    <p class="empty-state">No cars have been added yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="history-table admin-table">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td>
                            <div class="table-media">
                                <?php if (!empty($car['image_path'])): ?>
                                    <img src="<?= e(base_url('/' . $car['image_path'])) ?>" alt="<?= e($car['name']) ?>">
                                <?php else: ?>
                                    <div class="table-image-placeholder">No image</div>
                                <?php endif; ?>
                                <div>
                                    <strong><?= e($car['name']) ?></strong>
                                    <span><?= e($car['model']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= e($car['type']) ?></td>
                        <td>BDT <?= e(number_format((float) $car['price_per_day'], 2)) ?></td>
                        <td><?= (int) $car['availability_status'] === 1 ? 'Available' : 'Unavailable' ?></td>
                        <td><?= e($car['created_at']) ?></td>
                        <td>
                            <div class="action-row table-actions">
                                <a class="button-link button-secondary" href="<?= e(base_url('/admin/cars/' . $car['id'] . '/edit')) ?>">Edit</a>
                                <form method="post" action="<?= e(base_url('/admin/cars/' . $car['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this car?');">
                                    <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">
                                    <button class="button-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
