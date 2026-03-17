 <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contact Us</title>
        <link rel="stylesheet" type="text/css" href="assests/css/styles.css" />
    </head>

    <body>
        <!-- Top blue bar -->
        <header class="topBar">
            <img src="assets/img/logo.png" class="logo">

            <!-- Search Bar -->
            <div class="searchContainer">
                <input type="text" placeholder="Search">
            </div>

            <!-- Basket link -->
            <div class="topLinks">
                <a href="basket.php"> Basket</a>
            </div>
        </header>

        <!-- Bottom purple nav bar -->
        <nav class="bottomNav">
            <a href="login.php"> Login</a>
            <a href="index.php"> Home</a>
            <a href="products.php"> Products</a>
            <a href="about.php"> About Us</a>
            <a href="contact.php"> Contact Us</a>
        </nav>
        
        <main class="page-container">
            
                
            <div class="contact-page">
                <h1 class="contact-header">Contact Form</h1>
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
                    (Any inquiries about orders, please provide the email and order number
                    used in purchase.)
                    <br>
                </p>
                <p class="contact-info-3">
                    *required
                    <br>
                </p>

                <!-- Contact form -->
                <div class="contact-form">
                    <form action="../backend/routes.php" method="POST">

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
            </div> 
        </main>

        <!-- javascript -->
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
    </body>
</html>




