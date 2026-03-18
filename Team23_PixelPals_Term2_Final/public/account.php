<?php
require_once '../app/includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="js/account.js" defer></script>
    <title>PixelPals - Account</title>
</head>
<body>
  <section class="accountCard">
    <div class="cardHeader">
      <h1>My Account</h1>
      <div class="actions">
        <button type="button">Edit</button> 
        <button type="submit" form="accountForm">Save Changes</button>
        <button type="button" class="ghost">Cancel</button>
      </div>
    </div>

    <p class="message">Your personal details</p>

    <form id="accountForm" method="POST" action="../app/actions/account_update.php">
      <div class="grid">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text">
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email">
        </div>

        <div class="field">
          <label for="first_Name">First Name</label>
          <input id="first_Name" name="first_name" type="text">
        </div>

        <div class="field">
          <label for="last_Name">Last Name</label>
          <input id="last_Name" name="last_name" type="text">
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob" name="dob" type="date" />
        </div>

        <div class="field fullWidth">
          <label for="address1">Address Line 1</label>
          <input id="address1" name="address1" type="text" placeholder="308 Negra Arroyo Lane" />
        </div>

        <div class="field fullWidth">
          <label for="address2">Address Line 2 (Optional)</label>
          <input id="address2" name="address2" type="text" placeholder="Flat 21" />
        </div>

        <div class="field">
          <label for="city">City</label>
          <input id="city" name="city" type="text" placeholder="Albuquerque" />
        </div>

        <div class="field">
          <label for="postcode">Postcode</label>
          <input id="postcode" name="postcode" type="text" placeholder="AL12 8XD" />
        </div>

        <div class="field fullWidth">
          <label for="country">Country</label>
          <input id="country" name="country" type="text" placeholder="New Mexico" />
        </div>
      </div>
    </form>

    <p class="message">Change your password</p>

    <form method="POST" action="../app/actions/change_password_post.php">
      <div class="grid">
        <div class="field">
          <label for="old_Password">Current Password</label>
          <input id="old_Password" name="old_password" type="password" />
        </div>

        <div class="field">
          <label for="new_Password">New Password</label>
          <input id="new_Password" name="new_password" type="password" />
        </div>

        <div class="field">
          <label for="confirm_Password">Confirm New Password</label>
          <input id="confirm_Password" name="confirm_password" type="password" />
        </div>
      </div>

      <div class="actions" style="margin-top: 16px;">
        <button type="submit">Update Password</button>
      </div>
    </form>
  </section>
 <section class="danger-zone">
    <h3>Danger Zone</h3>
    <p>Once you delete your account, there is no going back. Please be certain.</p>

    <form action="../app/actions/account_delete.php" method="POST" onsubmit="return confirm('WARNING: This will permanently delete your account and all your data. Are you absolutely sure?');">
        <button type="submit" class="btn-delete">
            Delete My Account
        </button>
    </form>
</section>
</body>
<style>
    * 
    { 
        box-sizing: border-box; 
    }

    body
    {
      margin: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 24px;
    }

    .accountCard
    {
      width: 100%;
      max-width: 920px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 14px;
      padding: 26px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .cardHeader
    {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    h1
    { 
        margin: 0; 
    }

    .actions
    { 
        display: flex; gap: 10px; 
    }

    button
    {
      padding: 10px 14px;
      background: #C0ED45;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: bold;
    }

    button.ghost
    { 
        background: #E7E7E7; 
    }

    .message
    {
      min-height: 20px;
      margin: 12px 0 0;
      font-weight: bold;
      color: #333;
    }

    .grid
    {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-top: 14px;
    }

    .field
    {
      display: flex;
      flex-direction: column;
    }

    .field.fullWidth
    { 
        grid-column: 1 / -1; 
    }

    label
    {
      font-weight: bold;
      margin-bottom: 6px;
      font-size: 14px;
    }

    input
    {
      width: 100%;
      padding: 10px;
      border: 1px solid #cccccc;
      border-radius: 8px;
      font-size: 14px;
      background: #F3F4F6;
      color: #4d4d4d;
    }
  </style>
</html>
