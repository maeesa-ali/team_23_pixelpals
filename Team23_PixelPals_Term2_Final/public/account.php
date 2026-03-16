 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="js/account.js" defer></script>
    <title>PixelPals - Account</title>
</head>
<body>
    <!-- Account page content -->
  <section class="accountCard">
    <!-- Header section with title and action buttons -->
    <div class="cardHeader">
      <h1>My Account</h1>
      <!-- Action buttons -->
      <div class="actions">
        <button type="button">Edit</button> 
        <button type="button">Save Changes</button>
        <button type="button" class="ghost">Cancel</button>
      </div>
    </div>

    <p class="message">Your personal details</p>

    <!-- Form for account details -->
    <form>
      <div class="grid">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" type="text">
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" type="email">
        </div>

        <div class="field">
          <label for="first_Name">First Name</label>
          <input id="first_Name" type="text">
        </div>

        <div class="field">
          <label for="last_Name">Last Name</label>
          <input id="last_Name" type="text">
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob"  type="date" />
        </div>

        <div class="field fullWidth">
          <label for="address1">Address Line 1</label>
          <input id="address1" type="text" placeholder="308 Negra Arroyo Lane" />
        </div>

        <div class="field fullWidth">
          <label for="address2">Address Line 2 (Optional)</label>
          <input id="address2" type="text" placeholder="Flat 21" />
        </div>

        <div class="field">
          <label for="city">City</label>
          <input id="city" type="text" placeholder="Albuquerque" />
        </div>

        <div class="field">
          <label for="postcode">Postcode</label>
          <input id="postcode" type="text" placeholder="AL12 8XD" />
        </div>

        <div class="field fullWidth">
          <label for="country">Country</label>
          <input id="country" type="text" placeholder="New Mexico" />
        </div>

        <div class="field">
          <label for="old_Password">Current Password</label>
          <input id="old_Password" type="password" />
        </div>

        <div class="field">
          <label for="new_Password">New Password</label>
          <input id="new_Password" type="password" />
        </div>

        <div class="field">
          <label for="confirm_Password">Confirm New Password</label>
          <input id="confirm_Password" type="password" />
        </div>
      </div>
    </form>
  </section>
</body>
</html>
<!-- Styles for the account page -->
<style>
    * 
    { 
        box-sizing: border-box; 
    }

    body /*overall page style*/
    {
      margin: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 24px;
    }

    .accountCard /*main card container for account details*/
    {
      width: 100%;
      max-width: 920px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 14px;
      padding: 26px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .cardHeader /*header section containing title and action buttons*/
    {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    h1 /*main heading style*/
    { 
        margin: 0; 
    }

    .actions /*container for action buttons*/
    { 
        display: flex; gap: 10px; 
    }

    button /*button styles for edit, save, and cancel actions*/
    {
      padding: 10px 14px;
      background: #C0ED45;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: bold;
    }

    button.ghost /*ghost button style for cancel action*/
    { 
        background: #E7E7E7; 
    }

    .message /*message text styles*/
    {
      min-height: 20px;
      margin: 12px 0 0;
      font-weight: bold;
      color: #333;
    }

    .grid /*grid layout for form fields*/
    {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-top: 14px;
    }

    .field /*individual field styles*/
    {
      display: flex;
      flex-direction: column;
    }

    .field.fullWidth /*full width fields like address and country*/
    { 
        grid-column: 1 / -1; 
    }

    label /*label styles*/
    {
      font-weight: bold;
      margin-bottom: 6px;
      font-size: 14px;
    }

    input /*input field styles*/
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

