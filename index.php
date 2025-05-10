<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPHS Campus - Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
	
	
	<style>
	.hero {
    background: url('images/iphs4.jpeg') no-repeat center center/cover;
    text-align: center;
    color: white;
    padding: 100px 20px;
    border-radius: 10px;
	margin-bottom: 40px;
	animation: fadeInUp 1s ease-out;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
</head>
<body>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="images/logo.jpg" alt="IPHS" class="logo">
        <div class="sidebar-menus">
            <a href="index.php"><ion-icon name="storefront-outline"></ion-icon> Home</a>
            <a href="application.php"><ion-icon name="laptop-outline"></ion-icon> Apply</a>
            <a href="news.php"><ion-icon name="newspaper-outline"></ion-icon> News</a>
            <a href="about.php"><ion-icon name="business-outline"></ion-icon> About us</a>
            <a href="contact.php"><ion-icon name="chatbubbles-outline"></ion-icon> Contact us</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content fade-in">
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <h1>Welcome to <span>IPHS Campus</span></h1>
                <p>Providing Quality Education & Professional Training</p>
                <div class="hero-buttons">
                    <a href="application.php" class="btn btn-primary">📄 Apply Now</a>
                    <a href="explore_courses.php" class="btn btn-secondary">📚 Explore Courses</a>
                </div>
            </div>
        </div>

        <div class="container mt-5 text-center">
            <h1>Welcome to IPHS Campus Management System</h1>
            <p>Your one-stop platform for managing student records, attendance, and academic performance.</p>
            <a href="login.php" class="btn btn-primary btn-lg">Login</a>
            <a href="register.php" class="btn btn-secondary btn-lg">Register</a>
        </div>

        <!-- Courses Section -->
        <h2 class="text-center mt-4">Our Courses</h2>
        <div class="gallery fade-in">
            <img src="images/building.jpg" alt="Building Studies" loading="lazy">
            <img src="images/safety.jpg" alt="Health and Safety" loading="lazy">
            <img src="images/business.jpg" alt="Business Management" loading="lazy">
            <img src="images/it.jpg" alt="Information Technology" loading="lazy">
            <img src="images/english.jpg" alt="English for Professionals" loading="lazy">
			<img src="images/english2.jpg" alt="English for Professionals" loading="lazy">
            <img src="images/project.jpg" alt="Project Management" loading="lazy">
        </div>

        <!-- Gallery Section -->
        <h2 class="text-center mt-4">Campus Gallery</h2>
        <div class="gallery fade-in">
            <img src="images/iphs1.jpg" alt="Campus View" loading="lazy">
            <img src="images/iphs2.jpg" alt="Classroom" loading="lazy">
            <img src="images/iphs3.jpeg" alt="Library" loading="lazy">
            <img src="images/iphs4.jpeg" alt="Lab" loading="lazy">
        </div>

        <!-- Facilities Section -->
        <div class="facilities fade-in">
            <h2>Our Facilities</h2>
            <p>✔ Modern Classrooms</p>
            <p>✔ E-Learning Resources</p>
            <p>✔ Career Guidance & Counseling</p>
        </div>

        <!-- Student Testimonials -->
        <div class="testimonials fade-in">
            <h2>Student Testimonials</h2>
            <p>"IPHS Campus helped me secure my dream job in IT!" - Maryam</p>
            <p>"The Business Management course was life-changing." - Suhatha</p>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
