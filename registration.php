<?php

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST['Idno'];
    $lastname = $_POST['Lastname'];
    $firstname = $_POST['Firstname'];
    $midname = $_POST['Midname'];
    $course = $_POST['Course'];
    $year_level = $_POST['Year_Level'];
    $username = $_POST['Username'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    $defaultImage = 'image.jpg'; 

    $sql = "INSERT INTO users (IDNO, LAST_NAME, FIRST_NAME, MID_NAME, COURSE, YEAR_LEVEL, USER_NAME, PASSWORD_HASH, UPLOAD_IMAGE) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $idno, $lastname, $firstname, $midname, $course, $year_level, $username, $password, $defaultImage);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="logo/ccs.png" type="image/x-icon">
    <title>Student Registration</title>
    <style>
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        .bg-gradient {
            background-image: linear-gradient(111.3deg, rgba(74,105,187,1) 9.6%, rgba(205,77,204,1) 93.6%);
        }
        .form-container {
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .floating-label input:focus ~ .label, 
        .floating-label input:not(:placeholder-shown) ~ .label {
            transform: translateY(-24px) scale(0.85);
            color: #6366f1;
        }
        .btn-hover-effect:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 min-h-screen flex items-center justify-center p-4">
    <div class="form-container max-w-4xl w-full mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header with decorative elements -->
            <div class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 text-white p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                
                <div class="flex items-center justify-center relative z-10 space-x-4">
                    <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-wider uppercase">Student Registration</h2>
                        <p class="text-white/70 text-sm">Create your account to access the CCS SIT-IN MONITORING SYSTEM</p>
                    </div>
                </div>
            </div>
            
            <!-- Decorative element - top wave pattern -->
            <div class="bg-gradient-to-r from-indigo-500/10 to-purple-500/10 h-2"></div>
            
            <!-- Main form section -->
            <div class="p-8">
                <form id="registerForm" method="POST" action="" class="space-y-6">
                    <!-- ID Number with special styling -->
                    <div class="relative group col-span-1 md:col-span-2">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                        <div class="relative bg-white rounded-lg overflow-hidden">
                            <input type="text" id="Idno" name="Idno" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter ID Number" required>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                    <i class="fas fa-id-card text-white text-sm"></i>
                                </span>
                            </div>
                            <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-purple-500">ID Number</span>
                        </div>
                    </div>
                    
                    <!-- Name Fields with consistent styling -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <input type="text" id="Lastname" name="Lastname" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Last Name" required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </span>
                                </div>
                                <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-purple-500">Last Name</span>
                            </div>
                        </div>
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <input type="text" id="Firstname" name="Firstname" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="First Name" required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </span>
                                </div>
                                <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-purple-500">First Name</span>
                            </div>
                        </div>
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <input type="text" id="Midname" name="Midname" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Middle Name">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </span>
                                </div>
                                <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-purple-500">Middle Name</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course and Year Level with enhanced select styling -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <select id="Course" name="Course" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0 appearance-none" required>
                                    <option value="" disabled selected>Select a Course</option>
                                    <option value="BS IN ACCOUNTANCY">BS IN ACCOUNTANCY</option>
                                    <option value="BS IN BUSINESS ADMINISTRATION">BS IN BUSINESS ADMINISTRATION</option>
                                    <option value="BS IN CRIMINOLOGY">BS IN CRIMINOLOGY</option>
                                    <option value="BS IN CUSTOMS ADMINISTRATION">BS IN CUSTOMS ADMINISTRATION</option>
                                    <option value="BS IN INFORMATION TECHNOLOGY">BS IN INFORMATION TECHNOLOGY</option>
                                    <option value="BS IN COMPUTER SCIENCE">BS IN COMPUTER SCIENCE</option>
                                    <option value="BS IN OFFICE ADMINISTRATION">BS IN OFFICE ADMINISTRATION</option>
                                    <option value="BS IN SOCIAL WORK">BS IN SOCIAL WORK</option>
                                    <option value="BACHELOR OF SECONDARY EDUCATION">BACHELOR OF SECONDARY EDUCATION</option>
                                    <option value="BACHELOR OF ELEMENTARY EDUCATION">BACHELOR OF ELEMENTARY EDUCATION</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-book text-white text-sm"></i>
                                    </span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-purple-500"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <select id="Year_Level" name="Year_Level" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0 appearance-none" required>
                                    <option value="" disabled selected>Select a Year Level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-graduation-cap text-white text-sm"></i>
                                    </span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-purple-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Username and Password with enhanced styling -->
                    <div class="space-y-4">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <input type="text" id="Username" name="Username" class="w-full pl-14 pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Username" required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-blue-500 to-purple-500">
                                        <i class="fas fa-user-shield text-white text-sm"></i>
                                    </span>
                                </div>
                                <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-purple-500">Username</span>
                            </div>
                        </div>
                        
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-500 to-purple-500 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <input type="password" id="Password" name="Password" class="w-full pl-14 pr-10 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Password" required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="p-1.5 rounded-full bg-gradient-to-br from-pink-500 to-purple-600">
                                        <i class="fas fa-key text-white text-sm"></i>
                                    </span>
                                </div>
                                <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600" data-target="Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit button with enhanced styling -->
                    <div class="flex flex-col items-center justify-center space-y-6 mt-8">
                        <button type="submit" class="w-full sm:w-auto relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-lg font-medium hover:text-white transition-all duration-300 hover:shadow-lg btn-hover-effect">
                            <span class="relative rounded-md bg-white px-10 py-3.5 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white flex items-center">
                                <i class="fas fa-user-plus mr-2"></i>
                                <span>Create Account</span>
                            </span>
                        </button>
                        
                        <div class="text-center">
                            <p class="text-gray-600">Already have an account? 
                                <a href="login.php" class="font-medium text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 hover:underline transition-all">
                                    Sign In
                                </a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ID Number validation
            const idnoInput = document.getElementById('Idno');
            idnoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 8);
            });
            
            // Name fields validation - letters only
            const nameInputs = ['Lastname', 'Firstname', 'Midname'];
            nameInputs.forEach(function(id) {
                const input = document.getElementById(id);
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            });

            // Toggle password visibility
            const togglePassword = document.querySelector('.toggle-password');
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('Password');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });

            // Form submission
            const form = document.getElementById('registerForm');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // Check ID number validation only
                if (idnoInput.value.length !== 8) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid ID Number',
                        text: 'ID Number must be exactly 8 digits.'
                    });
                    return;
                }
                
                // Continue with form submission
                const formData = new FormData(this);
                
                fetch('registration.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'You can now login to your account.',
                            showConfirmButton: true,
                            confirmButtonText: 'Go to Login',
                            confirmButtonColor: '#6366F1'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Something went wrong',
                        text: 'Please try again later.'
                    });
                });
            });
            
            // Login link redirection
            const loginLink = document.querySelector("a[href='login.php']");
            loginLink.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'login.php';
            });
            
            // Add animation to form elements on load
            const formElements = document.querySelectorAll('input, select, button');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });
        });
    </script>
</body>
</html>