<?php
include 'backend/dbconnection.php';

$contactMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $contactMessage = "<div class='contact-alert error'>Please fill name, email, and message.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $message);

        if ($stmt->execute()) {
            $contactMessage = "<div class='contact-alert success'>Message sent successfully.</div>";
        } else {
            $contactMessage = "<div class='contact-alert error'>Unable to send message.</div>";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pateldham Hostel Management</title>
    <link rel="stylesheet" href="frontend/global.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 40px;
            gap: 16px;
        }
        header .logo h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }
        header .auth-buttons a {
            margin-left: 12px;
        }
        main {
            max-width: 1180px;
            margin: 0 auto;
            padding: 40px;
            width: 100%;
        }
        .description {
            max-width: 640px;
            margin-bottom: 40px;
        }
        .description h2 {
            font-size: 42px;
            margin-bottom: 20px;
            line-height: 1.1;
        }
        .description p {
            font-size: 18px;
            color: rgba(255,255,255,0.9);
            line-height: 1.8;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
        }
        .slideshow-container {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            background: white;
            color: #1e293b;
        }
        .mySlides img {
            width: 100%;
            display: block;
            object-fit: cover;
        }
        .text {
            padding: 20px;
            color: #1e293b;
        }
        .auth-buttons a {
            text-decoration: none;
        }
        .btn{
          border: 1px solid white;
          color:white;
          display: inline-flex;
          align-items: center;
          justify-content: center;
        }
        .contact-section {
            margin-top: 44px;
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(320px, 1fr);
            gap: 28px;
            align-items: start;
        }
        .contact-copy h2 {
            margin: 0 0 12px;
            font-size: 32px;
        }
        .contact-copy p {
            color: rgba(255,255,255,0.88);
            line-height: 1.7;
        }
        .contact-form {
            background: white;
            color: #1e293b;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 20px 45px rgba(0,0,0,0.18);
        }
        .contact-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            margin-bottom: 14px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
        }
        .contact-form textarea {
            min-height: 110px;
            resize: vertical;
        }
        .contact-form button {
            border: none;
            cursor: pointer;
        }
        .contact-alert {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 600;
        }
        .contact-alert.success {
            background: #dcfce7;
            color: #166534;
        }
        .contact-alert.error {
            background: #fee2e2;
            color: #991b1b;
        }
        @media (max-width: 760px) {
            header,
            main {
                padding: 20px;
            }
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            .auth-buttons {
                width: 100%;
                display: flex;
                gap: 12px;
            }
            header .auth-buttons a {
                margin-left: 0;
                flex: 1;
            }
            .description h2 {
                font-size: 32px;
            }
            .description p {
                font-size: 16px;
                line-height: 1.7;
            }
            .contact-section {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            main {
                padding: 16px;
            }
            .description h2,
            .contact-copy h2 {
                font-size: 26px;
            }
            .prev,
            .next {
                padding: 12px;
                font-size: 18px;
            }
            .contact-form {
                padding: 18px;
            }
        }
    </style>
    
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo">
        <a href="index.php"><h1>Pateldham </h1></a>
        </div>
        <div class="auth-buttons">
            <a href="frontend/user/pages/login.php" class="btn">Login</a>
            <a href="frontend/user/pages/register.php" class="btn">Sign Up</a>
        </div>
    </header>

    <!-- Main Section -->
    <main>
        <div class="description">
            <h2>Welcome to Pateldham Hostel</h2>
            <p>We offer the best accommodation for students with all essential amenities to make your stay comfortable and enjoyable.</p>
        </div>
        <div class="slideshow-container">

            <!-- Full-width images with number and caption text -->
            <div class="mySlides fade">
                <div class="numbertext">1 / 5</div>
                <img src="photos/image1.png" style="width:100%">
                <div class="text">Caption Text</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">2 / 5</div>
                <img src="photos/image2.jpeg" style="width:100%">
                <div class="text">Caption Two</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">3 / 5</div>
                <img src="photos/image3.jpeg" style="width:100%">
                <div class="text">Caption Three</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">4 / 5</div>
                <img src="photos/image4.jpeg" style="width:100%">
                <div class="text">Caption Four</div>
            </div>

            <div class="mySlides fade">
                <div class="numbertext">5 / 5</div>
                <img src="photos/image5.jpeg" style="width:100%">
                <div class="text">Caption Five</div>
            </div>

            <!-- Next and previous buttons -->
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
        </div>
        <br>

        <!-- The dots/circles -->
        <div style="text-align:center">
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
            <span class="dot" onclick="currentSlide(4)"></span>
            <span class="dot" onclick="currentSlide(5)"></span>
        </div>

        <section class="contact-section">
            <div class="contact-copy">
                <h2>Contact Hostel Office</h2>
                <p>Send a message to the hostel team for admission, room, fees, or general questions.</p>
            </div>
            <form class="contact-form" method="POST" action="index.php">
                <?php echo $contactMessage; ?>
                <label>Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Phone</label>
                <input type="text" name="phone">

                <label>Message</label>
                <textarea name="message" required></textarea>

                <button type="submit" name="contact_submit" class="btn-primary">Send Message</button>
            </form>
        </section>
    </main>

 
    <footer>
        <p>© 2026 Pateldham Hostel Management. All rights reserved.</p>
    </footer>

    <script>
        let slideIndex = 1;
        showSlides(slideIndex);

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            let dots = document.getElementsByClassName("dot");

            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }

            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }

            if (n > slides.length) {slideIndex = 1}
            if (n < 1) {slideIndex = slides.length}
            slides[slideIndex - 1].style.display = "block";  
            dots[slideIndex - 1].className += " active";
        }

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        setInterval(() => {
            showSlides(slideIndex += 1);
        }, 2000);
    </script>
</body>
</html>
