<section class="page-header">
    <h1>All Rent Order History</h1>
    <p class="muted">Every rental order with member and car details.</p>
</section>

<?php if ($orders === []): ?>
    <p class="empty-state">No orders found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="history-table admin-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Car</th>
                    <th>Dates</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Ordered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?= e($order['member_name']) ?></strong>
                            <span><?= e($order['member_email']) ?></span>
                        </td>
                        <td>
                            <strong><?= e($order['car_name']) ?></strong>
                            <span><?= e($order['car_model']) ?> - <?= e($order['car_type']) ?></span>
                        </td>
                        <td><?= e($order['start_date']) ?> to <?= e($order['end_date']) ?></td>
                        <td>BDT <?= e(number_format((float) $order['total_cost'], 2)) ?></td>
                        <td><span class="status <?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
                        <td><?= e($paymentMethods[$order['payment_method']] ?? ($order['payment_method'] ?: 'Not paid')) ?></td>
                        <td><?= e($order['order_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
