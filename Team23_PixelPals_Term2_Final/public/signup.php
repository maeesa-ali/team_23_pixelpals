<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PixelPals – Registration</title>
</head>

<body>
<header class="topBar">
    <img src="pixelPalsLogo.png" class="logo">

    <div class="searchContainer">
        <input type="text" placeholder="Search">
    </div>

    <div class="topLinks">
        <a href="basket.php"> Basket</a>
    </div>
</header>

<nav class="bottomNav">
    <a href="login.php"> Login</a>
    <a href="index.php"> Home</a>
    <a href="products.php"> Products</a>
    <a href="about.php"> About Us</a>
    <a href="contact.php"> Contact Us</a>
</nav>

<div class="pageWrapper">
    <form id="regForm" action="../app/actions/signup_post.php" method="POST" novalidate>

        <label for="email">Email</label>
        <input type="email" id="email" name="email">

        <label for="username">Username</label>
        <input type="text" id="username" name="username">

        <label for="first_Name">Name</label>
        <input type="text" id="first_Name" name="first_name">

        <label for="last_Name">Last Name</label>
        <input type="text" id="last_Name" name="last_name">

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <label for="confirm_Password">Confirm Password</label>
        <input type="password" id="confirm_Password" name="confirm_password">

        <button type="submit"><strong>Register</strong></button>

        <a href="login.php">Already have an account?</a>
    
        <p id="error" style="color:red;"></p>
    </form>
</div>
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
        background: linear-gradient(#DE4FFF, #77ADFF, #D5A4Ff);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .pageWrapper
    {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form
    {
        margin: 0 auto;
        background: #C9DAFF;
        padding: 20px;
        width: 400px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label
    {
        display: block;
        margin-top: 10px;
        font-weight: bold;
        font-size: 14px;
    }

    input
    {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        background-origin: border-box;
    }

    input:focus
    {
        outline: none;
        border-color: #4A90E2;
        box-shadow: 0 0 5px rgba(74,144,226,0.5);
    }

    button
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

    button:hover
    {
        background: #3a7ac8;
    }

    a
    {
        text-align: center;
        display: block;
        margin-top: 12px;
        color: #4A90E2;
        text-decoration: none;
    }

    a:hover
    {
        text-decoration: underline;
    }

    #error, #success
    {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    .topBar
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

    .topBar .logo
    {
        height: 60px;
    }

    .searchContainer
    {
        flex: 1;
        position: relative;
    }

    .searchContainer input
    {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border-radius: 20px;
        border: none;
        font-size: 16px;
    }

    .topLinks a
    {
        color: white;
        font-size: 18px;
        margin-left: 20px;
        text-decoration: none;
    }

    .bottomNav
    {
        background: #8962C6;
        display: flex;
        justify-content: space-evenly;
        padding: 10px 0;
        width: 100%;
        box-sizing: border-box;
    }

    .bottomNav a
    {
        color: white;
        font-size: 18px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>

<script src="js/validation.js"></script>
</html>
