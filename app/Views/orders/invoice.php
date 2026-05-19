<section class="page-header">
    <h1>Invoice #<?= e($order['id']) ?></h1>
    <p class="muted">Review your rental before payment.</p>
</section>

<article class="invoice-panel" data-order-panel>
    <div>
        <p class="eyebrow"><?= e($order['car_type']) ?></p>
        <h2><?= e($order['car_name']) ?></h2>
        <p><?= e($order['car_model']) ?></p>
    </div>

    <dl class="summary-list">
        <div>
            <dt>Start Date</dt>
            <dd><?= e($order['start_date']) ?></dd>
        </div>
        <div>
            <dt>End Date</dt>
            <dd><?= e($order['end_date']) ?></dd>
        </div>
        <div>
            <dt>Status</dt>
            <dd><span class="status <?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></dd>
        </div>
        <div>
            <dt>Total Cost</dt>
            <dd>BDT <?= e(number_format((float) $order['total_cost'], 2)) ?></dd>
        </div>
    </dl>

    <?php if ($order['status'] === 'pending'): ?>
        <div class="action-row">
            <button type="button" class="button-danger js-cancel-order" data-order-id="<?= e($order['id']) ?>">Cancel Order</button>
            <form method="post" action="<?= e(base_url('/orders/' . $order['id'] . '/finalize')) ?>">
                <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">
                <button type="submit">Finalize</button>
            </form>
        </div>
        <p class="muted" data-cancel-message></p>
    <?php endif; ?>
</article>
