<?php

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['Username'];
    $password = $_POST['Password'];

    // Check if admin is logging in
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin'] = true;
        echo json_encode(["status" => "success", "message" => "Admin login successful!"]);
        exit;
    }

    $sql = "SELECT STUD_NUM, PASSWORD_HASH, FIRST_NAME FROM users WHERE USER_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userId, $password_hash, $first_name);
    $stmt->fetch();

    header('Content-Type: application/json');
    if (password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $userId; 
        $_SESSION['first_name'] = $first_name; 
        echo json_encode(["status" => "success", "message" => "Login successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="logo/ccs.png" type="image/x-icon">
    <title>CCS Sit-in Monitoring - Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: none;
            background-size: cover;
            overflow: hidden;
        }
                
        .login-card {
            backdrop-filter: blur(5px);
            animation: float 6s ease-in-out infinite;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .input-effect {
            transition: all 0.3s ease;
        }
        
        .input-effect:focus {
            transform: scale(1.02);
        }
        
        .input-container {
            position: relative;
            overflow: hidden;
        }
        
        .input-border {
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .input-border:hover {
            border-color: #c7d2fe;
        }
        
        .input-border:focus-within {
            border-color: #8b5cf6;
        }
        
        .gradient-text {
            background: linear-gradient(90deg, #5e72eb, #ff2d95);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .spin-icon {
            animation: spin 20s linear infinite;
        }
        
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        .shine-effect {
            position: relative;
            overflow: hidden;
        }
        
        .shine-effect::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.3) 50%, 
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            transition: transform 0.7s;
            opacity: 0;
        }
        
        .shine-effect:hover::before {
            animation: shine 1.5s ease;
        }
        
        @keyframes shine {
            0% {
                opacity: 0;
                transform: translateX(-100%) rotate(30deg);
            }
            20% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateX(100%) rotate(30deg);
            }
        }
        
        .floating-circles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: -1;
            opacity: 0.3;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, #5e72eb, #ff2d95);
            opacity: 0.4;
            animation: float-circles 15s infinite;
        }
        
        @keyframes float-circles {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        
        .animate-in {
            animation: fadeInUp 0.8s forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .pulse-button {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.7);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(147, 51, 234, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(147, 51, 234, 0);
            }
        }
        
        /* Input Field Highlight Style */
        .input-highlight {
            position: relative;
            z-index: 10;
            border: 2px solid #d1d5db;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .input-highlight:focus-within {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
        }
        
        .input-highlight:hover:not(:focus-within) {
            border-color: #a78bfa;
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700">
    <!-- Animated background elements -->
    <div class="floating-circles">
        <div class="circle" style="width: 80px; height: 80px; top: 10%; left: 10%; animation-duration: 20s;"></div>
        <div class="circle" style="width: 120px; height: 120px; top: 25%; left: 70%; animation-duration: 25s;"></div>
        <div class="circle" style="width: 50px; height: 50px; top: 70%; left: 30%; animation-duration: 15s;"></div>
        <div class="circle" style="width: 100px; height: 100px; top: 60%; left: 80%; animation-duration: 18s;"></div>
        <div class="circle" style="width: 65px; height: 65px; top: 35%; left: 20%; animation-duration: 22s;"></div>
    </div>

    <div class="login-card glass-effect w-11/12 max-w-md rounded-3xl overflow-hidden">
        <!-- Header with decorative elements -->
        <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 pt-6 pb-8 px-6">
            <!-- Decorative circles -->
            <div class="absolute w-40 h-40 rounded-full bg-purple-500 opacity-10 -top-20 -left-20"></div>
            <div class="absolute w-28 h-28 rounded-full bg-indigo-500 opacity-20 -bottom-10 -right-10"></div>
            
            <!-- Logo display -->
            <div class="flex justify-center mb-3 relative animate-in" style="animation-delay: 0.1s;">
                <div class="relative">
                    <div class="absolute inset-0 rounded-full bg-white opacity-10 animate-ping"></div>
                    <div class="flex space-x-3 bg-white/10 p-3 rounded-2xl backdrop-blur-sm">
                        <img src="logo/uc.png" alt="UC Logo" class="w-14 h-14 object-contain">
                        <img src="logo/ccs.png" alt="CCS Logo" class="w-14 h-14 object-contain">
                    </div>
                </div>
            </div>
            
            <!-- Title with animated gradient border -->
            <div class="text-center relative animate-in" style="animation-delay: 0.2s;">
                <div class="inline-block relative">
                    <span class="absolute -inset-1 rounded-lg bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 opacity-50 blur"></span>
                    <h1 class="relative text-2xl font-bold text-white tracking-wider uppercase px-6 py-2 bg-white/10 rounded-lg backdrop-blur-sm">
                        CCS Sit-in Monitoring
                    </h1>
                </div>
                <p class="text-white/80 text-sm mt-2 font-light">Enter your credentials to access the system</p>
            </div>
        </div>
        
        <!-- Decorative divider -->
        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        
        <!-- Main form content -->
        <div class="px-8 py-8 bg-white relative overflow-hidden">
            <!-- Decorative tech pattern background -->
            <div class="absolute inset-0 opacity-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <pattern id="pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M0 20h40M20 0v40" stroke="#8b5cf6" stroke-width="0.5"/>
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#pattern)"/>
                    <circle cx="20%" cy="30%" r="50" fill="none" stroke="#8b5cf6" stroke-width="0.5"/>
                    <circle cx="70%" cy="60%" r="80" fill="none" stroke="#8b5cf6" stroke-width="0.5"/>
                </svg>
            </div>
            
            <form id="loginForm" method="POST" action="" class="relative z-10 space-y-6">
                <!-- Username input with visible border -->
                <div class="input-container animate-in" style="animation-delay: 0.3s;">
                    <label for="Username" class="block text-xs font-medium text-purple-700 mb-1.5 pl-2">USERNAME</label>
                    <div class="relative group">
                        <div class="input-highlight flex items-center bg-white rounded-xl overflow-hidden">
                            <div class="pl-4 pr-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                            </div>
                            <input type="text" id="Username" name="Username" 
                                   class="w-full py-3 px-2 border-none focus:ring-0 focus:outline-none input-effect" 
                                   placeholder="Enter your username" required>
                        </div>
                    </div>
                </div>
                
                <!-- Password input with visible border -->
                <div class="input-container animate-in" style="animation-delay: 0.4s;">
                    <label for="Password" class="block text-xs font-medium text-purple-700 mb-1.5 pl-2">PASSWORD</label>
                    <div class="relative group">
                        <div class="input-highlight flex items-center bg-white rounded-xl overflow-hidden">
                            <div class="pl-4 pr-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                                    <i class="fas fa-lock text-white text-sm"></i>
                                </div>
                            </div>
                            <input type="password" id="Password" name="Password" 
                                   class="w-full py-3 px-2 border-none focus:ring-0 focus:outline-none input-effect" 
                                   placeholder="Enter your password" required>
                            <button type="button" id="togglePassword" class="pr-4 text-gray-400 hover:text-purple-600 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Login button with pulse effect -->
                <div class="pt-2 animate-in flex justify-center" style="animation-delay: 0.5s;">
                    <button type="submit" class="w-full sm:w-auto relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-lg font-medium hover:text-white transition-all duration-300 hover:shadow-lg btn-hover-effect">
                        <span class="relative rounded-md bg-white px-10 py-3.5 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white flex items-center w-full justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span>ACCESS SYSTEM</span>
                        </span>
                    </button>
                </div>
                
                <!-- Registration link -->
                <div class="text-center mt-6 animate-in" style="animation-delay: 0.6s;">
                    <p class="text-gray-600 text-sm">
                        Don't have an account? 
                        <a href="registration.php" class="gradient-text font-semibold hover:underline transition-all">
                            Create Account
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('Password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
            
            // Create floating background circles dynamically
            const floatingCircles = document.querySelector('.floating-circles');
            for (let i = 0; i < 10; i++) {
                const size = Math.random() * 60 + 20;
                const circle = document.createElement('div');
                circle.classList.add('circle');
                circle.style.width = `${size}px`;
                circle.style.height = `${size}px`;
                circle.style.top = `${Math.random() * 100}%`;
                circle.style.left = `${Math.random() * 100}%`;
                circle.style.animationDuration = `${Math.random() * 10 + 10}s`;
                circle.style.animationDelay = `${Math.random() * 5}s`;
                circle.style.opacity = `${Math.random() * 0.3 + 0.1}`;
                floatingCircles.appendChild(circle);
            }
            
            // Add animated border highlight effect on focus
            const inputHighlights = document.querySelectorAll('.input-highlight');
            inputHighlights.forEach(container => {
                const input = container.querySelector('input');
                
                input.addEventListener('focus', function() {
                    container.classList.add('ring-2', 'ring-purple-300');
                });
                
                input.addEventListener('blur', function() {
                    container.classList.remove('ring-2', 'ring-purple-300');
                });
            });
        });
    </script>
</body>
</html>