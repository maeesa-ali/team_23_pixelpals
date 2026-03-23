document.addEventListener("DOMContentLoaded", function () {
    // The file input and preview block work together so admins can see the chosen image straight away.
    const fileInput = document.querySelector("[data-product-image-file]");
    const preview = document.querySelector("[data-product-media-preview]");

    if (!fileInput || !preview) {
        return;
    }

    const previewImage = preview.querySelector("[data-product-preview-image]");
    const previewLabel = preview.querySelector("[data-product-preview-label]");
    const initialImage = previewImage ? previewImage.getAttribute("src") : "";
    const initialLabel = previewLabel ? previewLabel.textContent : "";

    // Swap the preview to the newly chosen local file, or restore the original preview if cleared.
    function updatePreview() {
        const file = fileInput.files && fileInput.files[0];

        // No file selected means we keep showing the existing stored image.
        if (!file) {
            if (previewImage && initialImage) {
                previewImage.src = initialImage;
                previewImage.alt = "Current product image preview";
            }

            if (previewLabel) {
                previewLabel.textContent = initialLabel;
            }

            return;
        }

        // Use a temporary object URL so the admin can preview the file before saving the form.
        const objectUrl = URL.createObjectURL(file);

        if (previewImage) {
            previewImage.src = objectUrl;
            previewImage.alt = file.name + " preview";
        }

        if (previewLabel) {
            previewLabel.textContent = file.name;
        }
    }

    // Refresh the preview whenever the admin chooses a new file.
    fileInput.addEventListener("change", updatePreview);
});
