document.addEventListener("DOMContentLoaded", function () {
  // Any element with a data-confirm attribute gets a browser confirm box before the action continues.
  const confirmTargets = Array.from(document.querySelectorAll("[data-confirm]"));

  confirmTargets.forEach(function (target) {
    const message = target.getAttribute("data-confirm");

    if (!message) {
      return;
    }

    // Forms confirm on submit, while links/buttons confirm on click.
    const eventName = target.tagName === "FORM" ? "submit" : "click";

    // Cancel the action if the user backs out of the confirmation dialog.
    target.addEventListener(eventName, function (event) {
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
