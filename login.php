<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Health Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #007bff, #00c6ff); /* Match dashboard gradient */
            color: #fff;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
            border: 1px solid rgba(0, 123, 255, 0.3); /* Subtle blue border */
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #F9FAFB;
            font-weight: 700;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: none;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            box-shadow: none;
            border: 1px solid #00bcd4; /* Cyan focus border */
        }
        .btn-custom {
            background-color: #00bcd4; /* Cyan button */
            color: #fff;
            border-radius: 10px;
            transition: 0.3s;
            font-weight: 600;
        }
        .btn-custom:hover {
            background-color: #0097a7; /* Darker cyan on hover */
            color: #fff;
        }
        .toggle-btn {
            cursor: pointer;
            padding: 10px;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            text-align: center;
            transition: 0.3s;
            background-color: rgba(0, 123, 255, 0.2); /* Subtle blue background */
        }
        .toggle-btn.active {
            background-color: #00bcd4; /* Active toggle matches button */
        }
        /* Fade-in Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: rgba(0, 255, 0, 0.1);
            border: 1px solid #00ff00;
            color: #00ff00;
        }
        .alert-danger {
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            color: #ff0000;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        <!-- Button Toggle -->
        <div class="d-flex justify-content-between mb-4">
            <div class="toggle-btn active" id="login-btn" onclick="toggleForm('login')">Login</div>
            <div class="toggle-btn" id="register-btn" onclick="toggleForm('register')">Register</div>
        </div>
        <!-- Login Form -->
        <form id="login-form" action="login_process.php" method="post">
            <h2>Login</h2>
            <input type="text" class="form-control" placeholder="Username" name="username" required>
            <input type="password" class="form-control" placeholder="Password" name="password" required>
            <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>
        <!-- Register Form -->
        <form id="register-form" action="register_process.php" method="post" style="display: none;">
            <h2>Register</h2>
            <input type="text" class="form-control" placeholder="Username" name="username" required>
            <input type="email" class="form-control" placeholder="Email" name="email" required>
            <input type="password" class="form-control" placeholder="Password" name="password" required>
            <select name="usertype" class="form-control" required>
                <option value="" disabled selected>Select User Type</option>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
            <input type="text" class="form-control" placeholder="City/Town" name="town" required>
            <button type="submit" class="btn btn-custom w-100">Register</button>
        </form>
    </div>

    <!-- Script -->
    <script>
        const loginBtn = document.getElementById("login-btn");
        const registerBtn = document.getElementById("register-btn");
        const loginForm = document.getElementById("login-form");
        const registerForm = document.getElementById("register-form");

        function toggleForm(form) {
            if (form === "login") {
                loginForm.style.display = "block";
                registerForm.style.display = "none";
                loginBtn.classList.add("active");
                registerBtn.classList.remove("active");
            } else {
                loginForm.style.display = "none";
                registerForm.style.display = "block";
                registerBtn.classList.add("active");
                loginBtn.classList.remove("active");
            }
        }
    </script>
</body>
</html>