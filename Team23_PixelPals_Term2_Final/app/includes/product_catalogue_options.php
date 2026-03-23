<?php

function get_product_category_options(): array
{
    // Keep the allowed product families in one place so the forms and actions validate against the same list.
    return [
        'Audio',
        'Controllers',
        'Keyboard and Mouse',
        'Desks and Chairs',
        'Tabletop Accessories',
    ];
}

function is_valid_product_category(string $category): bool
{
    // Category validation is reused by both product create and product edit actions.
    return in_array($category, get_product_category_options(), true);
}

function get_product_upload_directory(): string
{
    // Uploaded admin product images all live in one managed folder under the public assets path.
    return dirname(__DIR__, 2) . '/public/assets/img/products/uploads';
}

function is_managed_product_upload_path(string $imagePath): bool
{
    // Only delete files that were created by the managed upload flow, not any hand-added stock assets.
    return str_starts_with($imagePath, 'assets/img/products/uploads/');
}

function remove_uploaded_product_image(string $imagePath): void
{
    // Ignore non-managed paths so this cleanup helper cannot accidentally delete unrelated files.
    if (!is_managed_product_upload_path($imagePath)) {
        return;
    }

    $absolutePath = dirname(__DIR__, 2) . '/public/' . ltrim($imagePath, '/');
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function store_uploaded_product_image(array $file): array
{
    // Start with the obvious upload checks before trying to inspect or move the file.
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, 'Please choose an image file.', null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [false, 'The image upload failed. Please try again.', null];
    }

    // Restrict uploads to the handful of image types the storefront actually expects.
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    // Validate the temp upload and inspect its MIME type instead of trusting the filename alone.
    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return [false, 'The uploaded image could not be verified.', null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    $extension = $allowedMimeTypes[$mimeType] ?? null;

    if ($extension === null) {
        return [false, 'Please upload a JPG, PNG, WEBP or GIF image.', null];
    }

    // Create the upload folder on demand so the admin UI works on a fresh project checkout too.
    $uploadDirectory = get_product_upload_directory();
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        return [false, 'The product image folder could not be created.', null];
    }

    // Clean the original name and add a timestamp/random suffix to avoid filename collisions.
    $baseName = pathinfo((string) ($file['name'] ?? 'product-image'), PATHINFO_FILENAME);
    $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '-', $baseName) ?: 'product-image';
    $fileName = $safeBaseName . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDirectory . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return [false, 'The image could not be saved to the product folder.', null];
    }

    // Return both the success flag and the storefront-relative image path the database should store.
    return [true, null, 'assets/img/products/uploads/' . $fileName];
}
