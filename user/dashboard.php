<?php
session_start();
require '../db.php'; // Updated path

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
    
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';

if ($userId) {
    $stmt = $conn->prepare("SELECT UPLOAD_IMAGE FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userImage);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
} else {
    $profileImage = "../images/image.jpg";
}

// Fetch announcements from the database with DESC order
$announcements = [];
$result = $conn->query("SELECT CONTENT, CREATED_DATE, CREATED_BY FROM announcement 
                       WHERE CREATED_BY = 'ADMIN' 
                       ORDER BY ID DESC, CREATED_DATE DESC"); // Changed ordering to show newest first
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Dashboard</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                }
            }
        }
    </script>
    <style>
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        /* Custom scrollbar for content */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, rgba(74,105,187,0.7), rgba(205,77,204,0.7));
            border-radius: 10px;
        }
        
        /* Custom animation for announcements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .announcement-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        /* Custom border for rules */
        .custom-border-left {
            border-left: 3px solid;
            border-image: linear-gradient(to bottom, rgba(74,105,187,1), rgba(205,77,204,1)) 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 min-h-screen font-poppins">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer" onclick="toggleNav(this)">
            <div class="bar1 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar2 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar3 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-indigo-900 to-purple-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto">
        <div class="absolute top-0 right-0 m-3">
            <button onclick="closeNav()" class="text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex flex-col items-center mt-6">
            <div class="relative">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0"><?php echo htmlspecialchars($firstName); ?></p>
            <p class="text-purple-200 text-xs mb-3">Student</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium">HISTORY</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-eye w-5 mr-2 text-center"></i>
                            <span class="font-medium">VIEW</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="lab_resource.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Resource</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION</span>
                </a>
                <div class="border-t border-white/10 my-2"></div>
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="flex-grow">
        <!-- Announcements Section -->
        <div class="w-11/12 md:w-4/12 mx-4 my-8 bg-white rounded-lg shadow-lg overflow-hidden float-left border border-gray-200">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-bullhorn text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Announcements</h2>
            </div>
            <div class="p-6">
                <?php if (empty($announcements)): ?>
                    <p class="text-gray-500 text-center py-4">No announcements available.</p>
                <?php else: ?>
                    <div class="h-[60vh] overflow-y-auto custom-scrollbar pr-2">
                        <div class="space-y-4">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="bg-white/80 rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-gradient-purple" 
                                     style="border-image: linear-gradient(to bottom, #4A69BB, #CD4DCC) 1;">
                                    <div class="flex items-center text-sm font-bold text-purple-600 mb-3">
                                        <i class="fas fa-user-shield mr-2"></i>
                                        <?php echo htmlspecialchars($announcement['CREATED_BY']); ?>
                                        <span class="mx-2">•</span>
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        <?php echo date('Y-M-d', strtotime($announcement['CREATED_DATE'])); ?>
                                    </div>
                                    <div class="text-gray-700 bg-gray-50/80 p-4 rounded-lg">
                                        <?php echo htmlspecialchars($announcement['CONTENT']); ?>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <span class="text-xs text-gray-500 italic">
                                            <?php echo date('h:i A', strtotime($announcement['CREATED_DATE'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rules Section - Modified Styling -->
        <div class="w-11/12 md:w-7/12 mx-4 my-8 bg-white rounded-lg shadow-lg overflow-hidden float-right border border-gray-200">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-clipboard-list text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Rules and Regulations</h2>
            </div>
            <div class="p-6">
                <div class="text-center mb-6 bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-800 to-purple-800">University of Cebu</h1>
                    <h2 class="text-xl font-semibold text-gray-700">COLLEGE OF INFORMATION & COMPUTER STUDIES</h2>
                    <h3 class="text-lg font-medium text-gray-600 border-t border-b border-gray-200 py-2 my-2 inline-block px-8">LABORATORY RULES AND REGULATIONS</h3>
                </div>
                
                <div class="h-[60vh] overflow-y-auto custom-scrollbar pr-4">
                    <p class="text-gray-700 mb-4 italic font-medium">To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                    
                    <ol class="list-none space-y-4 text-gray-700">
                        <?php
                        $rules = [
                            "Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.",
                            "Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.",
                            "Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.",
                            "Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.",
                            "Deleting computer files and changing the set-up of the computer is a major offense.",
                            "Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to \"sit-in\".",
                            "Observe proper decorum while inside the laboratory.",
                            "Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.",
                            "Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.",
                            "Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.",
                            "For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.",
                            "Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately."
                        ];
                        
                        $subRules = [
                            "Do not get inside the lab unless the instructor is present.",
                            "All bags, knapsacks, and the likes must be deposited at the counter.",
                            "Follow the seating arrangement of your instructor.",
                            "At the end of class, all software programs must be closed.",
                            "Return all chairs to their proper places after using."
                        ];
                        
                        foreach ($rules as $index => $rule):
                            $ruleNum = $index + 1;
                        ?>
                            <li class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-300 <?php echo $ruleNum % 2 == 0 ? 'border-r-4 border-indigo-400' : 'border-l-4 border-purple-400'; ?>">
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm mr-3 shadow-md"><?php echo $ruleNum; ?></span>
                                    <div class="flex-grow">
                                        <p class="text-gray-800"><?php echo $rule; ?></p>
                                        
                                        <?php if ($ruleNum == 7): ?>
                                            <ul class="list-none pl-4 pt-3 space-y-2 mt-2 bg-gray-50 rounded-lg p-3">
                                                <?php foreach ($subRules as $subIndex => $subRule): ?>
                                                    <li class="flex items-center">
                                                        <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-xs mr-2 shadow-sm"><?php echo $subIndex + 1; ?></span>
                                                        <p class="text-gray-700"><?php echo $subRule; ?></p>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <div class="mt-8 bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-lg shadow-sm border-t-2 border-indigo-500">
                        <h2 class="text-xl font-bold text-indigo-800 mb-4 text-center">DISCIPLINARY ACTION</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-lg font-bold mr-3">First Offense</span>
                                <p class="text-gray-700">The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="flex-shrink-0 px-3 py-1 bg-purple-100 text-purple-800 rounded-lg font-bold mr-3">Second and<br>Subsequent<br>Offenses</span>
                                <p class="text-gray-700">A recommendation for a heavier sanction will be endorsed to the Guidance Center.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Clear the floats -->
        <div class="clear-both"></div>
    </div>
    
    <!-- Footer -->
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
    </div>
    
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }

        // Add this to your existing script section
        document.addEventListener('DOMContentLoaded', function() {
            // Format dates if needed and apply animations
            const announcementItems = document.querySelectorAll('.announcement-fade-in');
            
            // Stagger animation effect
            announcementItems.forEach((item, index) => {
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>