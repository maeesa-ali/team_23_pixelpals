<?php
// This form loads an existing product so admins can update the details without editing SQL by hand.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_form_page.php';
require_once '../../app/includes/product_catalogue_options.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;

if ($id > 0) {
    // Load the existing product first so the form can be prefilled with the current values.
    $stmt = $db->prepare('SELECT * FROM product WHERE ProductID = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    header('Location: ../../public/admin/products.php');
    exit();
}

$categoryOptions = get_product_category_options();
$currentImagePath = (string) ($product['ImagePath'] ?? '');

// Build the form markup first, then hand it to the shared admin form layout.
ob_start();
?>
<span class="eyebrow">Edit Product</span>
<h1><?php echo htmlspecialchars((string) $product['ProductName']); ?></h1>
<p class="intro">Update the fields below and save when the product details and stock level look right.</p>

<!-- Flash messages from the update action appear here after redirects. -->
<div class="flash-wrap">
    <?php display_flash_messages(); ?>
</div>

<form method="POST" action="admin_product_update.php" enctype="multipart/form-data">
    <!-- Hidden values keep track of which product is being saved and what image is already attached to it. -->
    <input type="hidden" name="id" value="<?php echo (int) $product['ProductID']; ?>">
    <input type="hidden" name="CurrentImagePath" value="<?php echo htmlspecialchars($currentImagePath); ?>">
    <!-- Editable product fields stay grouped together so the update form is easy to follow. -->
    <div class="form-grid">
        <div class="field">
            <label for="ProductName">Product Name</label>
            <input id="ProductName" type="text" name="ProductName" value="<?php echo htmlspecialchars((string) $product['ProductName']); ?>" required>
        </div>

        <div class="field">
            <label for="Category">Category</label>
            <select id="Category" name="Category" required>
                <?php foreach ($categoryOptions as $categoryOption): ?>
                    <option value="<?php echo htmlspecialchars($categoryOption); ?>" <?php echo (string) $product['Category'] === $categoryOption ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoryOption); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="Price">Price</label>
            <input id="Price" type="number" step="0.01" min="0" name="Price" value="<?php echo htmlspecialchars((string) $product['Price']); ?>" required>
        </div>

        <div class="field">
            <label for="Stock">Stock</label>
            <input id="Stock" type="number" min="0" name="Stock" value="<?php echo htmlspecialchars((string) $product['Stock']); ?>" required>
        </div>

        <div class="field full">
            <label for="ImageFile">Replace Product Image</label>
            <input id="ImageFile" type="file" name="ImageFile" accept="image/png,image/jpeg,image/webp,image/gif" data-product-image-file>
        </div>

        <div class="field full">
            <label for="Description">Description</label>
            <textarea id="Description" name="Description" required><?php echo htmlspecialchars((string) $product['Description']); ?></textarea>
        </div>
    </div>

    <!-- The preview starts with the current image and swaps to a new file if one is selected. -->
    <div class="media-preview" data-product-media-preview>
        <strong>Current image</strong>
        <img data-product-preview-image src="../<?php echo htmlspecialchars($currentImagePath ?: 'assets/img/logo.png'); ?>" alt="Current product image preview">
        <span data-product-preview-label><?php echo $currentImagePath !== '' ? htmlspecialchars(basename($currentImagePath)) : 'No image selected'; ?></span>
    </div>

    <div class="actions">
        <button type="submit">Save Product Changes</button>
        <a class="button-link" href="products.php">Cancel</a>
    </div>
</form>
<?php
$content = ob_get_clean();

// The shared admin form page wraps this content with the usual admin shell and actions.
render_admin_form_page([
    'title' => 'Edit Product | PixelPals Admin',
    'brand_subtitle' => 'Edit and maintain an existing catalogue product',
    'back_href' => 'products.php',
    'back_label' => 'Back to Products',
    'extra_scripts' => ['../js/admin_product_media.js?v=1'],
], $content);
