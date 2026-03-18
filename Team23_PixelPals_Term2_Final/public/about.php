<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
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

      #about-us-info {
        display: flex;
        flex-direction: column; 
        flex-wrap: wrap; 
        margin: 20px 0;
        max-width: 100%; 
        box-sizing: border-box; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);

        padding: 30px 50px; 
        background-color: rgba(255, 255, 255, 0.7); 
        border-radius: 40px;
      }

      .about-us-header, .outro {
        text-align: center;
      }

      .outro {
        width: fit-content;
        margin: 20px auto;
        background-color: #3F8BE0;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
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

    <!-- Main content -->
    <main class="container">
      <section id="about-us-info">
        <h2 class="about-us-header">About Us | PixelPals</h2>

        <p>
          Hello there, Player One! 
        </p>

        <p>
          Welcome to PixelPals, a colourful corner of the internet where gaming, fun, and healthy habits level up together.
        </p>

        <p>
          We noticed something important in the world of gaming: kids love to play, explore, and learn through games, 
          but not all gaming gear is designed with young players’ bodies in mind; that’s where PixelPals comes in!
        </p>

        <p>
          Our mission is simple: keep gaming comfortable, safe, and supportive.
        </p>

        <p>
          Young gamers are still growing, learning, and developing their skills. That’s why our accessories are designed 
          to support motor development, coordination, and healthy movement.
        </p>

         <p> <!-- include link to specific product page -->
          From <b><a href="">easy-grip controllers</a></b> to <b><a href="">adaptive keyboards</a></b>, every PixelPals product is created to help kids:
        </p>

        <ul>
          <li>Improve hand-eye coordination</li>
          <li>Build fine motor skills</li>
          <li>Maintain good posture while gaming</li>
          <li>Stay comfortable during play</li>
        </ul>

        <p>
          Because when kids feel comfortable, they can focus on learning, exploring, and having fun.
        </p>

        <p>
          We understand that children connect through gaming and technology, it is the modern-day community, and it is important
          to protect that space as well as the consumer. Our products are here to make sure kids are healthy and secure whilst 
          interacting with technology and help support them as much as possible without having to sacrifice their joy.
        </p>

        <p>
          Many PixelPals products are designed with ergonomics and accessibility in mind, for ease on joints, eyesight, hearing, 
          and posture. Its focus on ergonomic handling makes each product helpful for children with different abilities, learning 
          needs, or motor challenges.
        </p>

        <p>
          And PixelPals is a great place to shop for your kids; parents, guardians, and teachers who want to promote positive habits 
          when using technology can find a product with us that supports their views!
        </p>

        <p>
          So, whether you're a young adventurer, a curious learner, or a parent guiding the next generation of gamers, PixelPals 
          is here to help every player game smarter, safer, and happier.
        </p>

        <br>

        <h3 class="outro">
          <a href="products.php">Game On!</a>
        </h3>
      </section>
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
  </body>
</html>
