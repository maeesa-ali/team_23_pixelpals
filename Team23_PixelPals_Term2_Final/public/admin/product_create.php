<?php
// This form is where admins add a brand new product, including the chosen image and category.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_form_page.php';
require_once '../../app/includes/product_catalogue_options.php';

// The shared category helper keeps the admin options aligned with the storefront categories.
$categoryOptions = get_product_category_options();
$defaultCategory = $categoryOptions[0] ?? '';

// Build the form markup first, then hand it to the shared admin form layout.
ob_start();
?>
<span class="eyebrow">Add Product</span>
<h1>Create a new catalogue entry.</h1>
<p class="intro">Add the core product details below and the item will appear in both the admin catalogue and the storefront product listing.</p>

<!-- Flash messages from the create action appear here after redirects. -->
<div class="flash-wrap">
    <?php display_flash_messages(); ?>
</div>

<form method="POST" action="admin_product_create.php" enctype="multipart/form-data">
    <!-- Core product fields live in one grid so the form stays short and easy to scan. -->
    <div class="form-grid">
        <div class="field">
            <label for="ProductName">Product Name</label>
            <input id="ProductName" type="text" name="ProductName" required>
        </div>

        <div class="field">
            <label for="Category">Category</label>
            <select id="Category" name="Category" required>
                <?php foreach ($categoryOptions as $categoryOption): ?>
                    <option value="<?php echo htmlspecialchars($categoryOption); ?>" <?php echo $categoryOption === $defaultCategory ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoryOption); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="Price">Price</label>
            <input id="Price" type="number" step="0.01" min="0" name="Price" required>
        </div>

        <div class="field">
            <label for="Stock">Stock</label>
            <input id="Stock" type="number" min="0" name="Stock" required>
        </div>

        <div class="field full">
            <label for="ImageFile">Product Image</label>
            <input id="ImageFile" type="file" name="ImageFile" accept="image/png,image/jpeg,image/webp,image/gif" required data-product-image-file>
        </div>

        <div class="field full">
            <label for="Description">Description</label>
            <textarea id="Description" name="Description" required></textarea>
        </div>
    </div>

    <!-- This preview updates in the browser when the admin chooses an image file. -->
    <div class="media-preview" data-product-media-preview>
        <strong>Selected image</strong>
        <img data-product-preview-image src="../assets/img/logo.png" alt="Selected product image preview">
        <span data-product-preview-label>Choose an image from your files</span>
    </div>

    <div class="actions">
        <button type="submit">Create Product</button>
        <a class="button-link" href="products.php">Cancel</a>
    </div>
</form>
<?php
$content = ob_get_clean();

// The shared admin form page wraps this content with the usual admin shell and actions.
render_admin_form_page([
    'title' => 'Add Product | PixelPals Admin',
    'brand_subtitle' => 'Create a new product for the live catalogue',
    'back_href' => 'products.php',
    'back_label' => 'Back to Products',
    'extra_scripts' => ['../js/admin_product_media.js?v=1'],
], $content);
