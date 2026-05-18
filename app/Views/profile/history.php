<section class="page-header">
    <h1>Rental History</h1>
    <p class="muted">Confirmed and cancelled rentals from your account.</p>
</section>

<?php if ($orders === []): ?>
    <p class="empty-state">No confirmed or cancelled rentals yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Dates</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?= e($order['car_name']) ?></strong>
                            <span><?= e($order['car_model']) ?></span>
                        </td>
                        <td><?= e($order['start_date']) ?> to <?= e($order['end_date']) ?></td>
                        <td>BDT <?= e(number_format((float) $order['total_cost'], 2)) ?></td>
                        <td><span class="status <?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
                        <td><?= e($paymentMethods[$order['payment_method']] ?? ($order['payment_method'] ?: 'Not paid')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
