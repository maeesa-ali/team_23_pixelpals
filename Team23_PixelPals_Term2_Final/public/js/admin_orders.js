document.addEventListener("DOMContentLoaded", function () {
  // The order cards on the left feed the detail panel on the right using data attributes.
  const cards = Array.from(document.querySelectorAll(".order-card[data-order-id]"));
  const detailOrderId = document.getElementById("detailOrderId");
  const detailCustomer = document.getElementById("detailCustomer");
  const detailUsername = document.getElementById("detailUsername");
  const detailEmail = document.getElementById("detailEmail");
  const detailStatus = document.getElementById("detailStatus");
  const detailSummary = document.getElementById("detailSummary");
  const detailEngraving = document.getElementById("detailEngraving");
  const detailEngravingText = document.getElementById("detailEngravingText");
  const detailItemList = document.getElementById("detailItemList");
  const processOrderId = document.getElementById("processOrderId");
  const processStatus = document.getElementById("processStatus");

  if (!cards.length || !detailOrderId || !detailCustomer || !detailUsername || !detailEmail || !detailStatus || !detailSummary || !detailItemList) {
    return;
  }

  // Rebuild the selected order's item list without needing another server round-trip.
  function renderItems(items) {
    if (!Array.isArray(items) || items.length === 0) {
      detailItemList.innerHTML = '<div class="detail-empty">No item rows were found for this order.</div>';
      return;
    }

    detailItemList.innerHTML = items.map(function (item) {
      const productName = item.ProductName || "";
      const quantity = Number(item.Quantity || 0);
      const unitPrice = Number(item.totalProductPrice || 0).toFixed(2);
      const subtotal = Number(item.Subtotal || 0).toFixed(2);

      return ""
        + '<div class="item-row">'
        + "<div>"
        + "<strong>" + productName + "</strong>"
        + "<span>" + quantity + " x GBP " + unitPrice + "</span>"
        + "</div>"
        + '<div class="item-price">GBP ' + subtotal + "</div>"
        + "</div>";
    }).join("");
  }

  // Move the clicked order's data into the detail panel and keep the selected card highlighted.
  function selectCard(card) {
    cards.forEach(function (item) {
      item.classList.remove("selected");
    });

    card.classList.add("selected");

    detailOrderId.textContent = "Order #" + card.dataset.orderId;
    detailCustomer.textContent = card.dataset.orderUser;
    detailUsername.textContent = card.dataset.orderUsername;
    detailEmail.textContent = card.dataset.orderEmail;
    detailStatus.textContent = card.dataset.orderStatus;
    detailSummary.textContent = card.dataset.orderItems + " items | GBP " + Number(card.dataset.orderTotal || 0).toFixed(2) + " | User ID #" + card.dataset.orderUserid;

    // Engraving details only show up when that order actually used the extra feature.
    if (detailEngraving && detailEngravingText) {
      const engravingName = String(card.dataset.orderEngravingName || "").trim();
      const engravingFee = Number(card.dataset.orderEngravingFee || 0);

      if (engravingName !== "" && engravingFee > 0) {
        detailEngraving.hidden = false;
        detailEngravingText.textContent = '"' + engravingName + '" added for GBP ' + engravingFee.toFixed(2) + ".";
      } else {
        detailEngraving.hidden = true;
        detailEngravingText.textContent = "";
      }
    }

    if (processOrderId) {
      processOrderId.value = card.dataset.orderId;
    }

    if (processStatus) {
      processStatus.value = card.dataset.orderStatus;
    }

    // The order items are already embedded as JSON in the card, so parse them client-side.
    let items = [];
    try {
      items = JSON.parse(card.dataset.orderItemsJson || "[]");
    } catch (error) {
      items = [];
    }

    renderItems(items);
  }

  // Clicking any order card updates the detail view in place.
  cards.forEach(function (card) {
    card.addEventListener("click", function () {
      selectCard(card);
    });
  });
});
