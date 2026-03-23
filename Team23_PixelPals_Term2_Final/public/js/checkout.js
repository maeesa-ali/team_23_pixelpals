document.addEventListener("DOMContentLoaded", function () {
  // The engraving checkbox drives a small optional section and the running total on checkout.
  const box = document.querySelector("[data-engraving-box]");
  const toggle = document.querySelector("[data-engraving-toggle]");
  const field = document.querySelector("[data-engraving-field]");
  const input = document.querySelector("[data-engraving-input]");
  const line = document.querySelector("[data-engraving-line]");
  const total = document.querySelector("[data-checkout-total]");

  if (!box || !toggle || !field || !input || !line || !total) {
    return;
  }

  const engravingFee = Number(box.dataset.engravingFee || 0);
  const baseTotal = Number(String(total.textContent || "").replace(/[^\d.]/g, "")) || 0;

  // Show or hide the engraving field and recalculate the total whenever the toggle changes.
  function render() {
    const enabled = toggle.checked;
    field.classList.toggle("hidden", !enabled);
    line.classList.toggle("is-hidden", !enabled);
    input.required = enabled;

    if (!enabled) {
      input.value = "";
    }

    const nextTotal = enabled ? baseTotal + engravingFee : baseTotal;
    total.textContent = "\u00A3" + nextTotal.toFixed(2);
  }

  // Keep the summary in sync with the checkbox state from the moment the page loads.
  toggle.addEventListener("change", render);
  render();
});
