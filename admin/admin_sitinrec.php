<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Initialize pagination variables
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Update query to match database column names
$query = "SELECT IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, TIME_OUT, DATE, STATUS 
          FROM curr_sitin 
          ORDER BY DATE DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entries_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total number of records for pagination
$total_query = "SELECT COUNT(*) as total FROM curr_sitin";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_entries = $total_row['total'];
$total_pages = ceil($total_entries / $entries_per_page);

$programCounts = [
    'C Programming' => 0,
    'C++ Programming' => 0,
    'C# Programming' => 0,
    'Java Programming' => 0,
    'Php Programming' => 0,
    'Python Programming' => 0,
    'Database' => 0,
    'Digital Logic & Design' => 0,
    'Embedded System & IOT' => 0,
    'System Integration & Architecture' => 0,
    'Computer Application' => 0,
    'Web Design & Development' => 0,
    'Project Management' => 0
];

$labCounts = [
    'Lab 517' => 0,
    'Lab 524' => 0,
    'Lab 526' => 0,
    'Lab 528' => 0,
    'Lab 530' => 0,
    'Lab 542' => 0,
    'Lab 544' => 0
];

// Get PURPOSE statistics
$result1 = $conn->query("SELECT PURPOSE, COUNT(*) as count FROM curr_sitin GROUP BY PURPOSE");
while ($row = $result1->fetch_assoc()) {
    $purpose = $row['PURPOSE'];
    if (array_key_exists($purpose, $programCounts)) {
        $programCounts[$purpose] = $row['count'];
    }
}

// Get LABORATORY statistics
$result2 = $conn->query("SELECT LABORATORY, COUNT(*) as count FROM curr_sitin GROUP BY LABORATORY");
while ($row = $result2->fetch_assoc()) {
    $lab = $row['LABORATORY'];
    if (array_key_exists($lab, $labCounts)) {
        $labCounts[$lab] = $row['count'];
    }
}

// Convert data to ECharts format
$programData = [];
foreach ($programCounts as $name => $value) {
    $programData[] = ['value' => $value, 'name' => $name];
}

$labData = [];
foreach ($labCounts as $name => $value) {
    $labData[] = ['value' => $value, 'name' => $name];
}

// Convert to JSON for JavaScript
$programDataJSON = json_encode($programData);
$labDataJSON = json_encode($labData);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Admin Sit-in Records</title>
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
                <img src="../images/image.jpg" alt="Logo" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0">Admin</p>
            <p class="text-purple-200 text-xs mb-3">Administrator</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-search w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-eye w-5 mr-2 text-center"></i>
                            <span class="font-medium">VIEW</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" class="pl-7 mt-2 space-y-1">
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-book w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-list w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-comments w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">LAB</span>
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
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>

                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Content Container -->
    <div class="w-[90%] mx-auto my-8 bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <i class="fas fa-book text-2xl mr-4 relative z-10"></i>
            <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Current Sit-in Records</h2>
        </div>

        <!-- Charts Container -->
        <div class="flex flex-wrap justify-between gap-5 p-5">
            <!-- Chart Card 1 -->
            <div class="flex-1 min-w-[400px] bg-white rounded-lg shadow-md p-4">
                <div class="text-lg font-bold text-[#003d64] text-center mb-4">
                    Programming Languages Distribution
                </div>
                <div id="programmingChart" class="h-[450px]"></div>  <!-- Increased height to accommodate legend -->
            </div>
            
            <!-- Chart Card 2 -->
            <div class="flex-1 min-w-[400px] bg-white rounded-lg shadow-md p-4">
                <div class="text-lg font-bold text-[#003d64] text-center mb-4">
                    Room Distribution
                </div>
                <div id="labChart" class="h-[450px]"></div>  <!-- Increased height to match -->
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="p-6">
            <!-- Redesigned controls -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <div class="flex items-center bg-gray-50 rounded-lg p-2 shadow-sm">
                    <label class="text-gray-600 mr-2 text-sm">Show</label>
                    <select id="entriesPerPage" onchange="changeEntries(this.value)" class="bg-white border border-gray-200 rounded-md px-3 py-1.5 shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
                        <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span class="text-gray-600 ml-2 text-sm">entries</span>
                </div>
                
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search records..." 
                        class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none"
                        onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                <thead style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93));" class="text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">ID Number</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Purpose</th>
                            <th class="px-6 py-3 text-left">Laboratory</th>
                            <th class="px-6 py-3 text-left">Time In</th>
                            <th class="px-6 py-3 text-left">Time Out</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-50'>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['IDNO']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['FULL_NAME']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['PURPOSE']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['LABORATORY']) . "</td>";
                                echo "<td class='px-6 py-4'>" . date('h:i A', strtotime($row['TIME_IN'])) . "</td>";
                                echo "<td class='px-6 py-4'>" . ($row['TIME_OUT'] ? date('h:i A', strtotime($row['TIME_OUT'])) : 'N/A') . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['DATE']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['STATUS']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='px-6 py-4 text-center text-gray-500 italic'>No data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Updated Pagination -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mt-6 gap-4">
                <div class="text-gray-600 text-sm">
                    <?php
                    $start_entry = $total_entries > 0 ? $offset + 1 : 0;
                    $end_entry = min($offset + $entries_per_page, $total_entries);
                    echo "Showing <span class='font-semibold'>$start_entry</span> to <span class='font-semibold'>$end_entry</span> of <span class='font-semibold'>$total_entries</span> entries";
                    ?>
                </div>
                <div class="inline-flex rounded-lg shadow-sm">
                    <?php
                    // First page button
                    echo "<button onclick=\"changePage(1)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angles-left\"></i>
                    </button>";

                    // Previous page button
                    $prev_page = max(1, $current_page - 1);
                    echo "<button onclick=\"changePage($prev_page)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-l border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-left\"></i>
                    </button>";

                    // Page numbers
                    for($i = 1; $i <= $total_pages; $i++) {
                        if($i == $current_page) {
                            echo "<button class=\"px-3.5 py-2 text-sm bg-indigo-600 text-white border border-indigo-600\">$i</button>";
                        } else {
                            echo "<button onclick=\"changePage($i)\" 
                                  class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 hover:bg-gray-50 text-gray-700\">$i</button>";
                        }
                    }

                    // Next page button
                    $next_page = min($total_pages, $current_page + 1);
                    echo "<button onclick=\"changePage($next_page)\" " . ($current_page == $total_pages ? 'disabled' : '') . "
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-right\"></i>
                    </button>";

                    // Last page button
                    echo "<button onclick=\"changePage($total_pages)\" " . ($current_page == $total_pages ? 'disabled' : '') . "
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angles-right\"></i>
                    </button>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
    </div>
    <!-- ECharts Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <script>
        // SearchTable functions
        function handleKeyPress(event) {
            if (event.key === "Enter") {
                searchTable();
            }
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const tbody = document.getElementById('tableBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                for (let j = 0; j < cells.length; j++) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Add event listener for Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission
                searchTable();
            }
        });

        // Navigation functions
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // ECharts Implementation
        document.addEventListener('DOMContentLoaded', function() {
            // Programming Languages Chart
            const programmingChart = echarts.init(document.getElementById('programmingChart'));
            const programmingOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    type: 'plain',
                    orient: 'horizontal',
                    bottom: 10,
                    left: 'center',
                    width: '95%',
                    itemGap: 10,
                    itemWidth: 12,
                    itemHeight: 12,
                    textStyle: {
                        fontSize: 11,
                        color: '#666',
                        padding: [0, 4, 0, 4]
                    },
                    formatter: name => {
                        if (name.includes('&')) {
                            return name.replace(' & ', '\n');
                        }
                        return name;
                    },
                    tooltip: {
                        show: true
                    },
                    pageTextStyle: {
                        color: '#666'
                    },
                    selectedMode: false,
                    grid: {
                        left: 10,
                        right: 10,
                        top: 5,
                        bottom: 10
                    }
                },
                series: [{
                    name: 'Programming Languages',
                    type: 'pie',
                    radius: ['30%', '60%'],
                    center: ['50%', '40%'],  // Moved up to accommodate legend
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold',
                            formatter: '{b}\n{d}%'
                        },
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: <?php echo $programDataJSON; ?>
                }],
                color: [
                    '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', 
                    '#FF9F40', '#4CAF50', '#E91E63', '#2196F3', '#FF5722', 
                    '#673AB7', '#009688', '#795548'
                ],
                backgroundColor: 'transparent'
            };
            programmingChart.setOption(programmingOption);
            
            // Laboratory Distribution Chart
            const labChart = echarts.init(document.getElementById('labChart'));
            const labOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    type: 'plain',
                    orient: 'horizontal',
                    bottom: 10,
                    left: 'center',
                    width: '95%',
                    itemGap: 10,
                    itemWidth: 12,
                    itemHeight: 12,
                    textStyle: {
                        fontSize: 11,
                        color: '#666',
                        padding: [0, 4, 0, 4]
                    },
                    tooltip: {
                        show: true
                    },
                    pageTextStyle: {
                        color: '#666'
                    },
                    selectedMode: false,
                    grid: {
                        left: 10,
                        right: 10,
                        top: 5,
                        bottom: 10
                    }
                },
                series: [{
                    name: 'Laboratory Distribution',
                    type: 'pie',
                    radius: ['30%', '60%'],
                    center: ['50%', '40%'],  // Moved up to accommodate legend
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold',
                            formatter: '{b}\n{d}%'
                        },
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: <?php echo $labDataJSON; ?>
                }],
                color: [
                    '#FF6384', '#FFCE56', '#FF9F40', '#36A2EB', '#9966FF', 
                    '#4BC0C0', '#4CAF50'
                ],
                backgroundColor: 'transparent'
            };
            labChart.setOption(labOption);

            // Handle window resize
            window.addEventListener('resize', function() {
                programmingChart.resize();
                labChart.resize();
            });
        });

        // Pagination functions
        function changeEntries(entries) {
            window.location.href = `admin_sitinrec.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesPerPage').value;
            window.location.href = `admin_sitinrec.php?entries=${entries}&page=${page}`;
        }
    </script>
</body>
</html>