<?php
// This form loads one customer record so an admin can update it without leaving the admin area.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

require_once '../../app/config/db.php';
require_once '../../app/includes/flash.php';
require_once '../../app/includes/admin_form_page.php';

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = null;

if ($userId > 0) {
    // Load the customer record once so the form opens with their current details already filled in.
    $stmt = $db->prepare('SELECT * FROM users WHERE UserID = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    $_SESSION['error'] = 'Customer account not found.';
    header('Location: ../../public/admin/customers.php');
    exit();
}

// Build the form markup first, then pass it into the shared admin form shell.
ob_start();
?>
<span class="eyebrow">Edit Customer</span>
<h1><?php echo htmlspecialchars($user['Username']); ?></h1>
<p class="intro">Update the customer profile details below. Changes are saved directly to the main users table.</p>

<!-- Flash messages from the update action appear here after redirects. -->
<div class="flash-wrap">
    <?php display_flash_messages(); ?>
</div>

<form action="admin_customer_update.php" method="POST">
    <!-- Hidden ID keeps the update pointed at the correct customer row. -->
    <input type="hidden" name="user_id" value="<?php echo (int) $user['UserID']; ?>">

    <!-- Main customer fields are grouped together in one grid for easier editing. -->
    <div class="form-grid">
        <div class="field">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
        </div>

        <div class="field">
            <label for="first_name">First Name</label>
            <input id="first_name" type="text" name="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required>
        </div>

        <div class="field">
            <label for="last_name">Last Name</label>
            <input id="last_name" type="text" name="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" required>
        </div>

        <div class="field full">
            <label for="dob">Date of Birth</label>
            <input id="dob" type="date" name="dob" value="<?php echo htmlspecialchars((string) $user['DateOfBirth']); ?>" required>
        </div>
    </div>

    <div class="actions">
        <button type="submit">Save Customer Changes</button>
        <a class="button-link" href="customers.php">Cancel</a>
    </div>
</form>

<!-- This small meta line gives admins an easy way to double-check which account they are editing. -->
<p class="meta">Customer ID: #<?php echo (int) $user['UserID']; ?></p>
<?php
$content = ob_get_clean();

// The shared admin form page wraps this content with the usual admin shell and actions.
render_admin_form_page([
    'title' => 'Edit Customer | PixelPals Admin',
    'brand_subtitle' => 'Edit customer details safely and clearly',
    'back_href' => 'customers.php',
    'back_label' => 'Back to Customers',
], $content);
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
</head>
<body>
    <h1>Edit User: <?php echo htmlspecialchars($user['Username']); ?></h1>
    <p><a href="../../public/admin/customers.php">Back to List</a></p>

    <form action="../../app/actions/admin_customer_update.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">

        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required><br><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required><br><br>
        
        <label>Date Of Birth:</label><br>
        <input type="date" name="dob" value="<?php echo $user['DateOfBirth']; ?>" required><br><br>

        <button type="submit">Update Customer</button>
    </form>
</body>
</html>
