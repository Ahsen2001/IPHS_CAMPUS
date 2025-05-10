<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | IPHS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        body {
            background-color: #f4f7fc;
            font-family: Arial, sans-serif;
            padding-top: 70px; /* Offset for fixed navbar */
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(to right, #0052D4, #4364F7, #6FB1FC);
        }

        .navbar-brand img {
            height: 50px;
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            color: white;
            font-size: 16px;
            transition: 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: #ffc107;
        }

        /* Contact Page Styling */
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 40px 20px;
        }

        .contact-info, .contact-form {
            flex: 1;
            max-width: 500px;
            background: white;
            padding: 25px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: 10px;
        }

        .contact-form button {
            width: 100%;
            background: #0052D4;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .contact-form button:hover {
            background: #0040a0;
        }

        /* Google Map */
        .map {
            text-align: center;
            margin: 30px 0;
        }

        iframe {
            width: 90%;
            max-width: 900px;
            height: 300px;
            border-radius: 10px;
            border: 2px solid #0040a0;
        }

        /* Footer */
        footer {
            background: #0040a0;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.jpg" alt="IPHS Logo"> IPHS Campus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><ion-icon name="storefront-outline"></ion-icon> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="application.php"><ion-icon name="laptop-outline"></ion-icon> Apply</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news.php"><ion-icon name="newspaper-outline"></ion-icon> News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><ion-icon name="business-outline"></ion-icon> About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><ion-icon name="chatbubbles-outline"></ion-icon> Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Information -->
    <div class="contact-container">
        <div class="contact-info">
            <h3>📍 Our Contact Details</h3>
            <p><strong>📍 Address:</strong> No. 304, Main Street, Akkaraipattu, Sri Lanka</p>
            <p><strong>📞 Mobile:</strong> +94 77 267 6345</p>
            <p><strong>☎ Hotline:</strong> +94 67 728 40 80</p>
            <p><strong>📧 Email:</strong> info@iphs.lk</p>
            <p><strong>🕒 Working Hours:</strong> Monday - Sunday, 9 AM - 5 PM</p>
        </div>

        <!-- Contact Form -->
        <div class="contact-form">
            <h3>📩 Send a Message</h3>
            <form>
                <input type="text" placeholder="Your Name" required>
                <input type="email" placeholder="Your Email" required>
                <textarea placeholder="Your Message" required></textarea>
                <button type="submit">📨 Send</button>
            </form>
        </div>
    </div>

    <!-- Google Map -->
    <div class="map">
        <h3>📍 Find Us</h3>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.815524078035!2d81.83242357577526!3d7.156074792863568!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afbfdd5b87fef7f%3A0xa832f5c13df9f94d!2sMain%20St%2C%20Akkaraipattu%2C%20Sri%20Lanka!5e0!3m2!1sen!2s!4v1708192456789"
            allowfullscreen="" loading="lazy">
        </iframe>
    </div>

    <p class="text-center mt-4"><?= isset($page['content']) ? $page['content'] : "No additional content available."; ?></p>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 IPHS | No. 304, Main Street, Akkaraipattu</p>
    </footer>

</body>
</html>
