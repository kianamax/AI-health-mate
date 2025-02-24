<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Health Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #1a1a1a;
        color: #ffffff;
    }
    .navbar {
        background-color: rgba(26, 26, 26, 0.8);
        transition: background-color 0.3s ease;
    }
    .navbar.scrolled {
        background-color: rgba(26, 26, 26, 1);
    }
    .navbar-brand, .nav-link {
        color: #ffffff !important;
        transition: color 0.3s ease;
    }
    .nav-link:hover {
        color: #3498db !important;
    }
    .hero {
        background-image: url('doc3.jpg');
        background-size: cover;
        background-position: center;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
    }
    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .hero-content {
        position: relative;
        z-index: 1;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    .section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 6rem 0;
    }
    .section-title {
        color: #3498db;
        margin-bottom: 2rem;
        font-weight: bold;
        text-transform: uppercase;
    }
    .feature-icon {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        color: #3498db;
    }
    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
        padding: 10px 20px;
        font-weight: bold;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    .card {
        background-color: rgba(255, 255, 255, 0.9);
        border: none;
        transition: all 0.3s ease;
        padding: 2rem;
        border-radius: 15px;
        color: #333;
    }
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
    }
    .social-icon {
        font-size: 2.5rem;
        margin: 0 15px;
        color: #3498db;
        transition: all 0.3s ease;
    }
    .social-icon:hover {
        color: #2980b9;
        transform: scale(1.2);
    }
    .about-section {
        background-image: url('doc2.jpg');
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .about-overlay {
        background-color: rgba(0, 0, 0, 0.7);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .features-section {
        background-image: url('doc4.jpg');
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .features-overlay {
        background-color: rgba(0, 0, 0, 0.7);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .social-section {
        background-color: rgba(52, 152, 219, 0.05);
    }
    .features-content {
        position: relative;
        z-index: 1;
    }
    .features-section .card {
        background-color: rgba(255, 255, 255, 0.9);
        color: #333;
    }
    .features-section .card-title {
        color: #3498db;
    }
</style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Smart Health Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="display-4 fw-bold mb-4">Your Health, Our Priority</h1>
                <p class="lead mb-4">Empowering you to take control of your health with AI-driven insights and personalized recommendations.</p>
                <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about-section">
        <div class="about-overlay"></div>
        <div class="container position-relative">
            <h2 class="section-title text-center">About Us</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <p class="lead text-center">Smart Health Tracker is revolutionizing personal healthcare with AI-driven insights and user-friendly tools. Our mission is to empower individuals with health data, provide personalized recommendations, and make healthcare proactive and accessible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    
    <!-- Features Section -->
    <section id="features" class="section features-section">
    <div class="features-overlay"></div>
    <div class="container position-relative">
        <h2 class="section-title text-center">Our Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-stethoscope feature-icon"></i>
                        <h5 class="card-title">Symptom Checker</h5>
                        <p class="card-text">Advanced AI analyzes your symptoms and provides potential health insights.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h5 class="card-title">Health Dashboard</h5>
                        <p class="card-text">Visualize your health metrics with interactive charts and graphs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-md feature-icon"></i>
                        <h5 class="card-title">Personalized Recommendations</h5>
                        <p class="card-text">Receive tailored health advice based on your unique health profile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="section-title text-center">Get in Touch</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="lead mb-4">Have questions? Reach out to us!</p>
                    <p><i class="fas fa-phone"></i> +254 721 858191</p>
                    <div class="mt-4">
                        <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <div class="mt-3">
                        <p>@SmartHealthFB | @SmartHealthTW | @SmartHealthIG | @SmartHealthLI</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 Smart Health Tracker. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>