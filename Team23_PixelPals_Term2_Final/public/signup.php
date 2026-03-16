
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PixelPals – Registration</title>
</head>

<body>
<!-- Top blue bar -->
<header class="topBar">
    <img src="pixelPalsLogo.png" class="logo"> <!-- Logo Image -->

    <!-- Search Bar -->
    <div class="searchContainer">
        <input type="text" placeholder="Search">
    </div>

    <!-- Basket Link -->
    <div class="topLinks">
        <a href="basket.html"> Basket</a>
    </div>
</header>

<!-- Bottom purple nav bar -->
<nav class="bottomNav">
    <a href="login.html"> Login</a>
    <a href="index.html"> Home</a>
    <a href="products.html"> Products</a>
    <a href="about.html"> About Us</a>
    <a href="contact.html"> Contact Us</a>
</nav>

<div class="pageWrapper"> <!-- Main container for the registration page -->
    <!-- Registration Form-->
     <form id="regForm" action="../backend/registration.php" method="POST" novalidate>

        <!-- Email input field-->
        <label>Email</label>
        <input type="email" id="email">

        <!-- Username input field-->
        <label>Username</label>
        <input type="text" id="username">

        <!-- Name input field-->
         <label>Name</label>
        <input type="text" id="first_Name">

        <!-- Last Name input field-->
        <label>Last Name</label>
        <input type="text" id="last_Name">

        <label>Date of Birth</label>
        <input type="date" id="dob">

        <!-- Password input field-->
        <label>Password</label>
        <input type="password" id="password">

        <!-- Confirm Password input field-->
        <label>Confirm Password</label>
        <input type="password" id="confirm_Password">

        <div class="adminRow">
            <!-- Admin Account Creation Checkbox-->
            <label>Admin Account<input type="checkbox" id="adminCheckbox"></label>
        </div>

        <div id="adminPasswordRow" style="display:none;">
            <!-- Admin Password input field (only shown if admin checkbox is checked)-->
            <label>Admin Password</label>
            <input type="password" id="admin_Password">
        </div>

        <!-- Register button-->
        <button type="submit"><strong>Register</strong></button>

        <!-- Links to login page-->
        <a href="login.html">Already have an account?</a>
    
        <p id="error" style="color:red;"></p> <!-- Error message display-->
    </form>
</div>
</body>
</html>

<!-- Styles for registration form-->
<style>
    * 
    {
        box-sizing: border-box;
    }

    body /*overall page style*/
    {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(#DE4FFF, #77ADFF, #D5A4Ff);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .pageWrapper /* Main container*/
    {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form /*form container styles*/
    {
        margin: 0 auto;
        background: #C9DAFF;
        padding: 20px;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label /*input field labels*/
    {
        display: block;
        margin-top: 10px;
        font-weight: bold;
        font-size: 14px;
    }

    input /*input field styles*/
    {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        background-origin: border-box;
    }

    input:focus /*input focus styles*/
    {
        outline: none;
        border-color: #4A90E2;
        box-shadow: 0 0 5px rgba(74,144,226,0.5);
    }

    button /*login button styles*/
    {
        width: 100%;
        padding: 10px;
        margin-top: 15px;
        background: #C0ED45;
        color: black;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover /*login button hover styles*/
    {
        background: #3a7ac8;
    }

    a /*registration link styles*/
    {
        text-align: center;
        display: block;
        margin-top: 12px;
        color: #4A90E2;
        text-decoration: none;
    }

    a:hover /*registration link hover styles*/
    {
        text-decoration: underline;
    }

    #error, #success /*error and success message styles*/
    {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    .topBar /* Top blue bar*/
    {
        background: #3F8BE0;
        display: flex;
        align-items: center;
        padding: 10px 20px;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
        justify-content: space-between;
    }

    .topBar .logo /* Logo*/
    {
        height: 60px;
    }

    .searchContainer /* Search bar*/
    {
        flex: 1;
        position: relative;
    }

    .searchContainer input /* Search bar input field*/
    {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border-radius: 20px;
        border: none;
        font-size: 16px;
    }

    .topLinks a /* Basket Link*/
    {
        color: white;
        font-size: 18px;
        margin-left: 20px;
        text-decoration: none;
    }

    .bottomNav /* Purple nav bar*/
    {
        background: #8962C6;
        display: flex;
        justify-content: space-evenly;
        padding: 10px 0;
        width: 100%;
        box-sizing: border-box;
    }

    .bottomNav a /* Navigation links*/
    {
        color: white;
        font-size: 18px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .adminRow 
    {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;   
    }

    .adminRow input[type="checkbox"]   
    {
        width: auto;
        margin: 10;
        padding: 0;
        transform: translateY(3px);
    }

</style>

<!-- Links to validation script-->
<script src="js/validation.js"></script>

