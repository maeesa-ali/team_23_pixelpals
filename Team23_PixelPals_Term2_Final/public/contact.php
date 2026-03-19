<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />

    <!-- in line styling (temp) -->
    <style>
      a:link, a:visited {
      color: inherit;
      font-weight: bold;
      text-decoration: none;
      }

      a:hover {
          text-decoration: underline; 
      }

      a:active {
          text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
      }

      .contact-page {
        text-align: center;
        background-color: rgb(255, 255, 255, 0.7);
        max-width: 700px;
        width: 90%; 
        margin: 50px auto;
        padding: 30px 40px;
        border-radius: 40px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
      }

      .contact form {
        background-color: #9b59b6; 
        border-radius: 10px;
        max-width: 500px;
        margin: 20px auto;
      }

      .contact-form form {
        display: flex;
        flex-direction: column;
        gap: 12px;
      }

      .contact-form input, 
      .contact-form textarea {
        border-color: #9b59b6;
        border-style: solid;
        border-radius: 10px;
        border-width: 2px;
        box-shadow: 0 0 0 rgba(0,0,0,0.2);
        padding: 10px;
        display: block; 
        margin: 0 auto;
      }

      .contact-form input {
          width: 30%;
          height:20px;
      }

      #order {
        width: 20%;
      }

      .contact-form textarea {
        width: 80%;
        min-height: 120px;
        max-height: 200px;
        resize: vertical;
      }

      .contact-form button {
        width: fit-content;
        margin: 20px auto;
        background-color: #3a8fe7;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        border: none;
      }

      .contact-form button:hover {
        background-color: #3F8BE0;
      }

      .contact-form button:active {
      background-color: #ffffff;
      color: #3a8fe7;
      }
      .contact-info-2,
      .contact-info-3 {
        font-size: 80%;
      }

      .contact-info-3 {
      color: rgb(219, 0, 0);
      }
    </style>
  </head>

  <body>
    <header>
      <nav class="navbar">

        <div class="nav-left">
          <a href="/index.php">
            <img src="/assets/img/logo.png" class="logo" alt="PixelPals Logo">
          </a>

          <a href="/index.php">PixelPals</a>
        </div>

        <div class="nav-links">
          <a href="/index.php">Home</a>
          <a href="/products.php">Products</a>
          <a href="/about.php">About</a>
          <a href="/contact.php">Contact</a>
        </div>

        <div class="nav-right">
          <a href="/basket.php">Basket</a>
          <a href="/account.php">Account</a>

          <?php if(isset($_SESSION['user_id'])): ?>
          <a href="/logout.php">Logout</a>
          <?php else: ?>
          <a href="/login.php">Login</a>
          <a href="/signup.php">Signup</a>
          <?php endif; ?>

          <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="/admin/dashboard.php">Admin</a>
          <?php endif; ?>

          <!-- Search Bar 
          <div class="searchContainer">
            <input type="text" placeholder="Search">
          </div>
          -->
        </div>
      </nav>
    </header>

    <?php if(isset($_SESSION['success'])): ?>
    <div class="flash-success">
    <?php echo $_SESSION['success']; ?>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div class="flash-error">
    <?php echo $_SESSION['error']; ?>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
        
    <main class="container">
      <div class="contact-page">
        <h1 class="contact-header">Contact Form</h1>

        <div class="contact-info-0">
          <p class="contact-info-1"> 
            Have some questions? Don't hesitate to email us by filling out your information and 
            inquiries in the form below. 
            <br><br>
            Our team has a response time of 24-48 hours and will get 
            back to you via the email provided. If we cannot respond within the designated time,
            we will contact you further with an update. 
            <br><br>
          </p>

          <p class="contact-info-2">
            (Any inquiries about orders, please provide the order number and email 
            used in purchase.)
            <br>
          </p>
          <p class="contact-info-3">
            *required
            <br>
          </p>
        </div>

        <!-- Contact form -->
        <div class="contact-form">
          <form action="../app/actions/contact_submit.php" method="POST">

            <label for="name"><b>Name</b></label>
            <input type="text" id="name" name="name" placeholder="Name">

            <label for="email"><b>*Email</b></label>
            <input type="email" id="email" name="email" required placeholder="someone@email.com">

            <label for="number"><b>Order Number</b></label>
            <input type="number" id="order" name="order" placeholder="0123" min="0">

            <label for="message"><b>*Message</b></label>
            <textarea id="message" name="message" required placeholder="Type your message here"></textarea>

            <button type="submit"><strong>Submit</strong></button>
          </form>
        </div>

        <div class="contact-info-0">
          <p class="contact-info-1">
            You can also find us on Social Media for regular shop updates.
          </p>

          <img src="assets/img/logo.png" class="logo">
          <img src="assets/img/logo.png" class="logo">
          <img src="assets/img/logo.png" class="logo">
          <img src="assets/img/logo.png" class="logo">
        </div>
      </div> 
    </main>

    <footer class="footer">
      <p><strong>PixelPals</strong></p>
      <p>Ergonomic gaming accessories for children</p>
      <p>© 2026 PixelPals</p>

      <img src="assets/img/logo.png" class="logo">
      <img src="assets/img/logo.png" class="logo">
      <img src="assets/img/logo.png" class="logo">
      <img src="assets/img/logo.png" class="logo">
    </footer>

      <!-- javascript
      <script>
            document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector(".contact-form form");

            form.addEventListener("submit", function(e) {
              e.preventDefault();

              const name = form.name.value.trim();
              const email = form.email.value.trim();
              const order = form.order.value.trim();
              const message = form.message.value.trim();

              const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
              let errors = [];

              if (!email) {
              errors.push("Email is required.");
              } else if (!emailPattern.test(email)) {
              errors.push("Please enter a valid email address.");
              }

              if (!message) {
              errors.push("Please type your query.");
              }

              if (order && !/^\d+$/.test(order)) {
              errors.push("Must contain only numbers.");
              }

              if (errors.length > 0) {
              alert(errors.join("\n"));
              return;
              }

              const formData = new FormData(form);

              fetch(form.action, {
              method: "POST",
              body: formData
              })
              .then(response => response.text())
              .then(data => {
              alert("Your query has been submitted. We will get back to you soon. Thank you.");
              form.reset(); 
              })
              .catch(error => {
              alert("Please try again later.");
              console.error(error);
            });
          });
        });
      </script>
      -->
    </body>
</html>
