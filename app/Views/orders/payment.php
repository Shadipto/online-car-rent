<section class="page-header">
    <h1>Payment</h1>
    <p class="muted">Choose a payment method for invoice #<?= e($order['id']) ?>.</p>
</section>

<?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="form-card payment-form" method="post" action="<?= e(base_url('/orders/' . $order['id'] . '/payment')) ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= e(Session::csrfToken()) ?>">

    <div class="invoice-total">
        <span>Total</span>
        <strong>BDT <?= e(number_format((float) $order['total_cost'], 2)) ?></strong>
    </div>

<fieldset class="payment-methods">
    <legend>Payment Method</legend>

    <div class="payment-grid">
        <?php foreach ($paymentMethods as $value => $label): ?>
            <div class="payment-option">
                <input
                    type="radio"
                    id="payment-<?= e($value) ?>"
                    name="payment_method"
                    value="<?= e($value) ?>"
                    <?= ($old['payment_method'] ?? '') === $value ? 'checked' : '' ?>
                    required
                >

                <label for="payment-<?= e($value) ?>">
                    <?= e($label) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>

    <label>
        Transaction ID
        <input type="text" name="transaction_id" value="<?= e($old['transaction_id'] ?? '') ?>" maxlength="100" placeholder="Optional for cash on delivery">
    </label>

    <button type="submit">Confirm Payment</button>
</form>
