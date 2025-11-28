<?php

include "DbConn.php";
 session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="Image/png" href="doll.png">
  <title>DOP{e} - Daily Outfit Planner</title>
  <link rel="stylesheet" href="wholestyle.css">
  <script src="javascript.js" defer ></script>
</head>
<body>

<header > <!-- LightPink -->
  <h1>Daily Outfit Planner</h1> <!-- BlueViolet -->
  <p>Dress Pressed NOT Depressed 😁😍</p> <!-- HotPink -->
  <hr > <!-- PaleVioletRed -->
 <input type="checkbox" id="menu-toggle" />
<label for="menu-toggle" class="hamburger-icon">&#9776;</label>

<nav class="topnav" aria-label="Main navigation">
  <ul class="nav-links">

    <!-- Main links -->
    <li><a href="#home" id="homeBtn">Home</a></li>
    
    <!-- Outfit Planner dropdown -->
    <li>
      <a href="#planner" class="dropdown-toggle">Outfit Planner</a>
      <ul class="dropdown-menu">
        <li><a href="#Tips">Tips</a></li>
        <li><a href="#FAQs">FAQs</a></li>
        <li><a href="view_outfits.php">Save Outfits</a></li>
      </ul>
    </li>

    <!-- Reviews dropdown -->
    <li>
      <a href="#" class="dropdown-toggle">Reviews</a>
      <ul class="dropdown-menu">
        <li><a href="reviews.php">Submit Review</a></li>
        <li><a href="view_reviews.php">View Reviews</a></li>
      </ul>
    </li>

    <!-- Info links -->
    <li><a href="#about">About Us</a></li>
    <li><a href="#contact">Contact</a></li>

    <!-- User options -->
    <?php if (isset($_SESSION['Userid'])): ?>
      <li><a href="profile.php">Profile</a></li>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>

  </ul>
</nav>

<div class="sidebar">
  <ul>
    <li><a href="#home">Home</a></li>
    <li>
      <a href="#planner" class="dropdown-toggle-sidebar">Outfit Planner</a>
      <ul class="dropdown-menu">
        <li><a href="#Tips">Tips</a></li>
        <li><a href="#FAQs">FAQs</a></li>
        <li><a href="view_outfits.php">Saved Outfits</a></li>
      </ul>
    </li>
    <li><a href="#about">About Us</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="reviews.php">Submit Review</a></li>
    <li><a href="view_reviews.php">View Reviews</a></li>
    <li><a href="#browser-details" id="Browser-info">Browser Info</a></li>
  </ul>
</div>

</header>

<main>
  <!-- Home Section -->
  <section id="home" >
    <h2>Welcome to DOP{e}</h2> <!-- Thistle -->
    <p>
     Our squad helps you boss up your wardrobe based on the weather and your vibes.<br> 
     With us, you'll never look like your problems, slay all day, every day. 
    </p>
    

    <iframe width="360" height="640" 
          src="https://www.youtube.com/embed/1UCqa6-CyFo" 
          title="YouTube video player" frameborder="0" 
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
          allowfullscreen>
  </iframe>
  <hr style="border: 1px dashed #da70d6;">

  </section>


  <!-- About Section -->
 <section id="about">
  <h2 style="color: #c71585; text-align: center;">Meet The Glam Squad</h2>

  <div class="about-container">
    
    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>
    
    <article class="mySlides Snette">
      <h3>Snette</h3>
      <img src="member1 (2).jpg" alt="Photo of Snette" width="150" title="Emotional support">
      <p>Hi, I’m Sinethemba. <br />
        I do not know anything about fashion, but I can judge your outfit. <br />
        My skills include: HTML, CSS, JavaScript, and reality-checking.
      </p>
    </article>

    <article class="mySlides Kulungile">
      <h3>Kulungile</h3>
      <img src="member3.jpeg" alt="Photo of Kulungile" width="150" title="Fashionista">
      <p>Hi, I’m Kulungile. <br />
        I am your fashion guru. <br />
        My skills include: HTML, CSS, and JavaScript.
      </p>
    </article>

    <article class="mySlides Sibongiseni">
      <h3>Sibongiseni</h3>
      <img src="member2.jpg" alt="Photo of Sibongiseni" width="150" title="Back-end wizard">
      <p style="font-weight: bold;">Hi, I’m Sibongiseni. <br />
        I handle design and creativity. <br />
        I'm your back-end girl, making sure that you feel safe in our space.
      </p>
    </article>
    
  </div>
</section>
  
  <section id="planner" style="margin: 20px;">
    <h2 style=" color:#c71585; text-align: center;">Outfit Planner</h2>

    <!-- Form 1: Select style -->
<form id="style-form" class="dop-form">
  <label for="style">Choose a style:</label>
  <select id="style" name="style" required>
    <option value="gothic">Gothic</option>
    <option value="formal">Formal</option>
    <option value="casual">Casual</option>
  </select> <br>

  <label for="mood">How are you feeling today?</label>
  <input type="text" id="mood" name="mood" placeholder="Sad, happy, lazy..."><br><br>
  <button type="button" id="generateBtn"> Generate Outfit</button>

</form>



<!-- Outfit Suggestions -->
<h3 class="section-title everyday">Everyday Styles</h3>
<ul class="everyday-list">
  <li>Party: denim shorts, crop-shirts</li>
  <li>Gothic: black jacket, combat boots</li>
  <li>Formal: blazer, dress shoes</li>
</ul>

<h3 class="section-title occasion">Occasion Styles</h3>
<ol class="occasion-list">
  <li>Date: nice shirt, jeans</li>
  <li>Graduation: suit, polished shoes</li>
  <li>Anniversary: dress, heels</li>
</ol>

<h3 class="section-title problems">Dress Like My Problems</h3>
<dl class="problems-list">
  <dt>Hoodie</dt>
  <dd>Comfy and warm, perfect for a lazy day.</dd>
  <dt>Sweatpants</dt>
  <dd>Relaxed fit, go-to casual bottom.</dd>
  <dt>Slippers</dt>
  <dd>Keep your feet cozy.</dd>
</dl>

<!-- Tables -->
<h3 class="section-title everyday">Everyday Outfit Items</h3>
<table id="everyday-table" class="dop-table">
  <tr><th>Item</th><th>Type</th><th>Color</th></tr>
  <tr><td>Hoodie</td><td>Top</td><td>Grey</td></tr>
  <tr><td>Jeans</td><td>Bottom</td><td>Blue</td></tr>
  <tr><td>Boots</td><td>Shoes</td><td>Brown</td></tr>
</table>

<h3 class="section-title occasion">Occasion Outfit Items</h3>
<table id="occasion-table" class="dop-table">
  <tr><th>Item</th><th>Type</th><th>Event</th></tr>
  <tr><td>Blazer</td><td>Top</td><td>Graduation</td></tr>
  <tr><td>Dress</td><td>Full</td><td>Anniversary</td></tr>
  <tr><td>Heels</td><td>Shoes</td><td>Date</td></tr>
</table>


<!-- Tips -->
 
  <section id="Tips"> 
    <h2>Tips</h2>
    <ul>
    <li>Always balance loose and fitted clothing for a flattering look.</li>
    <li>Invest in timeless basics: white sneakers, a good pair of jeans, and a classic jacket.</li>
    <li>Stick to 2–3 colors in your outfit to avoid clashing.</li>
    <li>Accessories can upgrade even the simplest look—don’t skip them!</li>
    <li>Layering is your best friend for style + comfort.</li>
    <li>Most importantly, wear what makes <em>you</em> feel confident.</li>
  </ul>
  </section>

  <!-- FAQs -->
<section id="FAQs"> 
  <h2>FAQs</h2>
  <div class="faq">
    <button class="faq-question">♥ What is your fashion philosophy?</button>
    <div class="faq-answer">
      <p>Fashion should be fun, expressive, and make you feel confident every day!</p>
    </div>
  </div>

  <div class="faq">
    <button class="faq-question">♥ How can I mix colours without clashing?</button>
    <div class="faq-answer">
      <p>Use one as the main color and the other as an accent, balance is key!</p>
    </div>
  </div>

  <div class="faq">
    <button class="faq-question">♥ What accessories should I never skip?</button>
    <div class="faq-answer">
      <p>A statement bag, earrings, or a simple necklace can elevate any outfit.</p>
    </div>
  </div>
</section>


  <!-- Contact Section -->
  <section id="contact" style="margin: 20px; text-align: center;">
    <h2 style="color: #c71585;">Contact Us</h2>
    <p >
      Email: beAdiva@gmail.com <br /> Phone: 072 072 7133 <br />
      Connect with us on <a href="https://www.linkedin.com/in/sibongiseni" target="_blank" >LinkedIn</a>,
      <a href="https://www.instagram.com" target="_blank" >Instagram</a>,
      <a href="https://www.x.com" target="_blank" >X</a>
    </p>
  </section>
</main>
<div id="browser-details" ></div>
<footer >
  <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
  <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>

</body>
</html>
