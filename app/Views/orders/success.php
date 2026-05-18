<section class="success-panel">
    <p class="eyebrow">Confirmed</p>
    <h1>Order Successful</h1>
    <p>Your rental for <?= e($order['car_name']) ?> has been confirmed.</p>

    <dl class="summary-list">
        <div>
            <dt>Order ID</dt>
            <dd>#<?= e($order['id']) ?></dd>
        </div>
        <div>
            <dt>Rental Period</dt>
            <dd><?= e($order['start_date']) ?> to <?= e($order['end_date']) ?></dd>
        </div>
        <div>
            <dt>Total Paid</dt>
            <dd>BDT <?= e(number_format((float) $order['total_cost'], 2)) ?></dd>
        </div>
        <div>
            <dt>Payment Method</dt>
            <dd><?= e($paymentMethods[$order['payment_method']] ?? $order['payment_method']) ?></dd>
        </div>
        <?php if (!empty($payment['transaction_id'])): ?>
            <div>
                <dt>Transaction ID</dt>
                <dd><?= e($payment['transaction_id']) ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <a class="button-link" href="<?= e(base_url('/profile/history')) ?>">View Rental History</a>
</section>
