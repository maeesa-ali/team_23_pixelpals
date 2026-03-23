(function () {
  // These summary fields are updated live while the customer changes quantities in the basket.
  const subtotalEl = document.getElementById("basketSubtotal");
  const deliveryEl = document.getElementById("basketDelivery");
  const totalEl = document.getElementById("basketTotal");
  const statusEl = document.getElementById("basketStatus");
  const itemCards = Array.from(document.querySelectorAll(".item-card"));

  if (!subtotalEl || !deliveryEl || !totalEl || itemCards.length === 0) {
    return;
  }

  // Keep currency formatting in one helper so every total uses the same display style.
  function formatMoney(value) {
    return "£" + value.toFixed(2);
  }

  // The inline status area gives lightweight feedback while background updates are running.
  function setStatus(message, type) {
    if (!statusEl) {
      return;
    }

    if (!message) {
      statusEl.textContent = "";
      statusEl.className = "inline-status";
      return;
    }

    statusEl.textContent = message;
    statusEl.className = "inline-status " + type;
  }

  // Recalculate line totals and basket totals on the page before or after the server confirms them.
  function recalcTotals() {
    let subtotal = 0;

    itemCards.forEach((card) => {
      const price = Number(card.dataset.price || 0);
      const qtyInput = card.querySelector(".qty-input");
      const lineTotalEl = card.querySelector(".line-total-value");
      const quantity = Number(qtyInput ? qtyInput.value : 0);
      const lineTotal = price * quantity;

      subtotal += lineTotal;

      if (lineTotalEl) {
        lineTotalEl.textContent = lineTotal.toFixed(2);
      }
    });

    const delivery = subtotal >= 100 || subtotal === 0 ? 0 : 4.99;
    subtotalEl.textContent = formatMoney(subtotal);
    deliveryEl.textContent = formatMoney(delivery);
    totalEl.textContent = formatMoney(subtotal + delivery);
  }

  // Send the quantity change in the background so the page does not need a full refresh.
  async function submitQtyForm(form, qtyInput) {
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        body: formData,
      });

      const data = await response.json();

      if (!response.ok || !data.ok) {
        throw new Error(data.message || "Could not update basket.");
      }

      // The server may clamp the quantity to the live stock level, so reflect that back into the input.
      if (typeof data.quantity !== "undefined") {
        qtyInput.value = data.quantity;
      }

      if (typeof data.subtotal !== "undefined") {
        subtotalEl.textContent = formatMoney(Number(data.subtotal));
      }

      if (typeof data.delivery !== "undefined") {
        deliveryEl.textContent = formatMoney(Number(data.delivery));
      }

      if (typeof data.total !== "undefined") {
        totalEl.textContent = formatMoney(Number(data.total));
      }

      recalcTotals();
      setStatus(data.message || "Basket updated.", "success");
    } catch (error) {
      setStatus(error.message || "Could not update basket.", "error");
    }
  }

  // Each quantity box updates the totals immediately, then posts the change shortly afterwards.
  itemCards.forEach((card) => {
    const form = card.querySelector(".qty-form");
    const qtyInput = card.querySelector(".qty-input");
    let timeoutId = null;

    if (!form || !qtyInput) {
      return;
    }

    qtyInput.addEventListener("input", () => {
      recalcTotals();
      setStatus("Updating basket...", "success");
      window.clearTimeout(timeoutId);
      timeoutId = window.setTimeout(() => {
        submitQtyForm(form, qtyInput);
      }, 500);
    });

    qtyInput.addEventListener("change", () => {
      recalcTotals();
      window.clearTimeout(timeoutId);
      setStatus("Updating basket...", "success");
      submitQtyForm(form, qtyInput);
    });
  });
})();
