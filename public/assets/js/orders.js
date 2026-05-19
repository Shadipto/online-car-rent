document.addEventListener("DOMContentLoaded", () => {
  const orderForm = document.querySelector(".js-order-form");

  if (orderForm) {
    const start = orderForm.elements.start_date;
    const end = orderForm.elements.end_date;
    const today = new Date().toISOString().split("T")[0];

    start.min = today;
    end.min = today;

    orderForm.addEventListener("submit", (event) => {
      if (!validateOrderDates(orderForm)) {
        event.preventDefault();
      }
    });

    [start, end].forEach((field) => {
      field.addEventListener("change", () => {
        if (start.value) {
          end.min = start.value;
        }

        updateLiveCost(orderForm);
      });
    });
  }

  document.querySelectorAll(".js-cancel-order").forEach((button) => {
    button.addEventListener("click", () => cancelOrder(button));
  });
});

function validateOrderDates(form) {
  clearFormErrors(form);
  let valid = true;
  const start = form.elements.start_date;
  const end = form.elements.end_date;
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (!start.value) {
    showFieldError(start, "Start date is required.");
    valid = false;
  }

  if (!end.value) {
    showFieldError(end, "End date is required.");
    valid = false;
  }

  if (start.value && new Date(start.value) < today) {
    showFieldError(start, "Start date cannot be in the past.");
    valid = false;
  }

  if (
    start.value &&
    end.value &&
    new Date(end.value) <= new Date(start.value)
  ) {
    showFieldError(end, "End date must be after the start date.");
    valid = false;
  }

  return valid;
}

async function updateLiveCost(form) {
  const preview = document.querySelector("[data-cost-preview]");

  if (
    !preview ||
    !form.elements.start_date.value ||
    !form.elements.end_date.value
  ) {
    return;
  }

  if (!validateOrderDates(form)) {
    preview.textContent = "Choose a valid date range.";
    return;
  }

  const data = new FormData();
  data.append(
    "_csrf",
    document.querySelector('meta[name="csrf-token"]').content,
  );
  data.append("car_id", form.elements.car_id.value);
  data.append("start_date", form.elements.start_date.value);
  data.append("end_date", form.elements.end_date.value);

  const response = await fetch(resolveUrl("api/orders/cost"), {
    method: "POST",
    headers: { Accept: "application/json" },
    body: data,
  });
  const payload = await response.json();

  preview.textContent = payload.success
    ? `${payload.days} rental day(s) - BDT ${payload.formatted_total}`
    : payload.message;
}

async function cancelOrder(button) {
  if (!window.confirm("Cancel this order?")) {
    return;
  }

  button.disabled = true;
  const message = document.querySelector("[data-cancel-message]");
  const data = new FormData();
  data.append(
    "_csrf",
    document.querySelector('meta[name="csrf-token"]').content,
  );

  const response = await fetch(
    resolveUrl(`api/orders/${button.dataset.orderId}/cancel`),
    {
      method: "POST",
      headers: { Accept: "application/json" },
      body: data,
    },
  );
  const payload = await response.json();

  if (message) {
    message.textContent = payload.message;
  }

  if (payload.success) {
    alert("Order cancelled successfully.");
    window.location.href = resolveUrl("/cars");
    return;
  }

  button.disabled = false;
}

function resolveUrl(path) {
  return `${window.location.origin}/online-car-rent/public${path}`;
}
