<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pateldham Hostel Management</title>
  <link rel="stylesheet" href="../CSS/index.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">
      <a href="index.php"><h1>Pateldham</h1></a>
    </div>
    <div class="auth-buttons">
      <a href="login.php" class="btn">Login</a>
      <a href="register.php" class="btn">Sign Up</a>
    </div>
  </header>

  <main>
    <div class="description card">
      <h2>Welcome to Pateldham Hostel</h2>
      <p>Providing quality accommodation and a homely environment for students. Experience comfort, security, and convenience in one place.</p>
    </div>

    <div class="slideshow-container card">
      <div class="mySlides fade">
        <!-- <div class="numbertext">1 / 5</div> -->
        <img src="../../../photos/image11.jpeg" style="width:100%" />
        <div class="text"> </div>
      </div>

      <div class="mySlides fade">
        <!-- <div class="numbertext">2 / 5</div> -->
        <img src="../../../photos/image22.jpg" style="width:100%" />
        <div class="text"></div>
      </div>

      <div class="mySlides fade">
        <!-- <div class="numbertext">3 / 5</div> -->
        <img src="../../../photos/image33.jpg" style="width:100%" />
        <div class="text"></div>
      </div>

      <div class="mySlides fade">
        <!-- <div class="numbertext">4 / 5</div> -->
        <img src="../../../photos/image44.jpg" style="width:100%" />
        <div class="text"></div>
      </div>

      <div class="mySlides fade">
        <!-- <div class="numbertext">5 / 5</div> -->
        <img src="../../../photos/image55.jpg" style="width:100%" />
        <!-- <div class="text"></div> -->
      </div>

      <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
      <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <div class="dots">
      <span class="dot" onclick="currentSlide(1)"></span>
      <span class="dot" onclick="currentSlide(2)"></span>
      <span class="dot" onclick="currentSlide(3)"></span>
      <span class="dot" onclick="currentSlide(4)"></span>
      <span class="dot" onclick="currentSlide(5)"></span>
    </div>
  </main>

  <footer>
    <p>© 2025 Pateldham Hostel Management. All rights reserved.</p>
    <p>Maintain By TirthBhayani</p>
  </footer>

  <script>
    let slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
      showSlides(slideIndex += n);
    }

    function currentSlide(n) {
      showSlides(slideIndex = n);
    }

    function showSlides(n) {
      let i;
      let slides = document.getElementsByClassName("mySlides");
      let dots = document.getElementsByClassName("dot");
      if (n > slides.length) { slideIndex = 1 }
      if (n < 1) { slideIndex = slides.length }
      for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
      }
      slides[slideIndex - 1].style.display = "block";
      dots[slideIndex - 1].className += " active";
    }

    setInterval(() => {
      plusSlides(1);
    }, 4000);
  </script>
</body>
</html>
