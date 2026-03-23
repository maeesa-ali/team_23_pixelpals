document.addEventListener("DOMContentLoaded", function () {
  // The rows act like a selectable inbox list, while the panel on the side shows the chosen message.
  const rows = Array.from(document.querySelectorAll("tr[data-message-id]"));
  const detailId = document.getElementById("detailMessageId");
  const detailCreated = document.getElementById("detailCreated");
  const detailName = document.getElementById("detailName");
  const detailEmail = document.getElementById("detailEmail");
  const detailSubject = document.getElementById("detailSubject");
  const detailBody = document.getElementById("detailBody");

  if (!rows.length || !detailId || !detailCreated || !detailName || !detailEmail || !detailSubject || !detailBody) {
    return;
  }

  // Swap the selected row styling and copy that row's data attributes into the detail panel.
  function selectMessage(row) {
    rows.forEach(function (item) {
      item.classList.remove("selected-row");
      const button = item.querySelector(".view-link");
      if (button) {
        button.classList.remove("active");
      }
    });

    row.classList.add("selected-row");
    const activeButton = row.querySelector(".view-link");
    if (activeButton) {
      activeButton.classList.add("active");
    }

    detailId.textContent = "Message #" + row.dataset.messageId;
    detailCreated.textContent = row.dataset.messageCreated;
    detailName.textContent = row.dataset.messageName;
    detailEmail.textContent = row.dataset.messageEmail;
    detailSubject.textContent = row.dataset.messageSubject;
    detailBody.textContent = row.dataset.messageBody;
  }

  // Let the admin click either the row or the explicit view button to change the detail panel.
  rows.forEach(function (row) {
    row.addEventListener("click", function (event) {
      const clickedButton = event.target.closest(".view-link");
      const clickedCell = event.target.closest("td");

      if (!clickedButton && !clickedCell) {
        return;
      }

      selectMessage(row);
    });
  });
});
