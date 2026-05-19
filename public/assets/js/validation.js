function showFieldError(field, message) {
  clearFieldError(field);

  const error = document.createElement("p");
  error.className = "field-error";
  error.textContent = message;
  field.insertAdjacentElement("afterend", error);
}

function clearFieldError(field) {
  const next = field.nextElementSibling;

  if (next && next.classList.contains("field-error")) {
    next.remove();
  }
}

function requireValue(form, name, message) {
  const field = form.elements[name];

  if (!field || String(field.value).trim() !== "") {
    return true;
  }

  showFieldError(field, message);
  return false;
}

function isEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

async function fetchCarsByType(type) {
  let url = `${window.location.origin}/online-car-rent/public/api/cars`;

  if (type) {
    url += `?type=${encodeURIComponent(type)}`;
  }

  const response = await fetch(url, {
    headers: { Accept: "application/json" },
  });

  if (!response.ok) {
    return null;
  }

  return response.json();
}
