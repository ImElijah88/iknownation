<?php
// --- SERVER-SIDE AUTHENTICATION LOGIC ---

// This block only runs when the JavaScript 'fetch' sends a POST request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- ERROR REPORTING (for debugging) ---
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // --- DATABASE CONFIGURATION ---
    $servername = "localhost";
    $username = "root";
    $password = ""; // Default XAMPP/MAMP password is empty.
    $dbname = "nownation";

    // Establish connection and handle errors
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    } catch (Exception $e) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection error: ' . $e->getMessage()]);
        exit(); // Stop script on connection failure
    }
    
    // --- HEADERS ---
    header("Content-Type: application/json; charset=UTF-8");

    // --- MAIN LOGIC ---
    $data = json_decode(file_get_contents('php://input'));
    $response = ['status' => 'error', 'message' => 'Invalid Request'];

    if ($data && isset($data->action)) {
        switch ($data->action) {
            case 'register':
                if (!empty($data->email) && !empty($data->password)) {
                    $email = $conn->real_escape_string($data->email);
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $response['message'] = 'An account with this email already exists.';
                    } else {
                        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
                        $insert_stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                        $insert_stmt->bind_param("ss", $email, $hashed_password);
                        if ($insert_stmt->execute()) {
                            $response = ['status' => 'success', 'message' => 'Registration successful! Please log in.'];
                        } else {
                            $response['message'] = 'Registration failed. Please try again.';
                        }
                        $insert_stmt->close();
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Email and password are required.';
                }
                break;

            case 'login':
                if (!empty($data->email) && !empty($data->password)) {
                    $email = $conn->real_escape_string($data->email);
                    $stmt = $conn->prepare("SELECT id, email, password, role, progress_data FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        if (password_verify($data->password, $user['password'])) {
                            unset($user['password']);
                            if (!empty($user['progress_data'])) {
                                $user['progress_data'] = json_decode($user['progress_data']);
                            } else {
                                $user['progress_data'] = new stdClass();
                            }
                            $response = ['status' => 'success', 'message' => 'Login successful!', 'user' => $user];
                        } else {
                            $response['message'] = 'Incorrect password.';
                        }
                    } else {
                        $response['message'] = 'No user found with that email.';
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Email and password are required.';
                }
                break;
        }
    }

    $conn->close();
    echo json_encode($response);

    // --- CRITICAL STEP ---
    // This stops the script from outputting the HTML below.
    exit();
}

// If it's a normal GET request, the script will skip the block above and just render the HTML.
?>
<!DOCTYPE html>
<html lang="en" class="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Now nation - Interactive Presentations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <script>
      // Set theme on initial load
      if (
        localStorage.getItem("theme") === "light" ||
        (!("theme" in localStorage) &&
          window.matchMedia("(prefers-color-scheme: light)").matches)
      ) {
        document.documentElement.classList.remove("dark");
      } else {
        document.documentElement.classList.add("dark");
      }
    </script>
    <style>
      :root {
        --primary-color: #06b6d4; /* cyan-600 */
        --primary-color-light: #67e8f9; /* cyan-400 */
        --primary-color-hover: #0891b2; /* cyan-500 */
      }
      @keyframes gradient-flow {
        0% {
          background-position: 0% 50%;
        }
        50% {
          background-position: 100% 50%;
        }
        100% {
          background-position: 0% 50%;
        }
      }
      body {
        font-family: "Inter", sans-serif;
        background-size: 400% 400%;
        animation: gradient-flow 15s ease infinite;
        transition: background-image 0.5s ease-in-out;
      }
      /* Light Theme Gradient */
      body {
        background-image: linear-gradient(
          -45deg,
          #e0c3fc,
          #8ec5fc,
          #f0f2f5,
          #a8edea
        );
      }
      /* Dark Theme Gradient */
      .dark body {
        background-image: linear-gradient(
          -45deg,
          #0f2027,
          #203a43,
          #2c5364,
          #232526
        );
      }
      .presentation-container {
        display: none;
      }
      .presentation-container.active {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
      }
      .slide {
        display: none;
      }
      .slide.active {
        display: block;
      }
      .transition-all {
        transition: all 0.3s ease-in-out;
      }
      #sidebar {
        transition: transform 0.3s ease-in-out;
        transform: translateX(-100%);
      }
      #sidebar.open {
        transform: translateX(0);
      }
      .sidebar-item .sidebar-text-container {
        max-width: 0;
        transition: max-width 0.4s ease-in-out, padding-left 0.4s ease-in-out;
        white-space: nowrap;
        overflow: hidden;
      }
      .sidebar-item:hover .sidebar-text-container,
      .sidebar-item.active .sidebar-text-container {
        max-width: 250px;
        padding-left: 0.75rem; /* ml-3 */
      }
      .neon-text {
        font-weight: bold;
        color: #fff;
        text-shadow: 0 0 5px #39ff14, 0 0 10px #39ff14, 0 0 20px #39ff14,
          0 0 40px #0fa, 0 0 80px #0fa;
      }
      .dark .neon-text {
        text-shadow: 0 0 7px #39ff14, 0 0 10px #39ff14, 0 0 21px #39ff14,
          0 0 42px #0fa, 0 0 82px #0fa;
      }
      /* Notification Style */
      #notification {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        color: white;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
      }
      #notification.show {
        opacity: 1;
        visibility: visible;
      }
      #notification.success {
        background-color: #10B981; /* Emerald-500 */
      }
      #notification.error {
        background-color: #EF4444; /* Red-500 */
      }
    </style>
  </head>
  <body class="text-gray-800 dark:text-gray-200">
    <div class="flex h-screen">
      <!-- Sidebar Hover Trigger -->
      <div
        id="sidebar-hover-trigger"
        class="fixed top-0 left-0 h-full z-10 hidden md:block"
        style="width: 100px"
      ></div>

      <!-- Sidebar Navigation -->
      <nav
        id="sidebar"
        class="p-4 flex flex-col items-start space-y-2 z-20 fixed top-0 left-0 h-full w-auto"
      >
        <!-- App Header -->
        <a href="#" class="w-full flex items-center p-3 mb-4">
          <span
            class="p-2 rounded-lg"
            style="background-color: var(--primary-color)"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 text-white"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 4h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"
              ></path>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            </svg>
          </span>
          <div
            class="sidebar-text-container"
            style="max-width: 250px; padding-left: 0.75rem"
          >
            <h1 class="text-2xl neon-text">Now nation</h1>
          </div>
        </a>

        <!-- Menu Items -->
        <div id="sidebar-menu" class="w-full space-y-2">
          <a
            href="#"
            data-presentation="web-dynamics"
            class="sidebar-item w-full flex items-center p-3 rounded-lg text-gray-800 dark:text-white cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 flex-shrink-0"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9V3m-9 9h18"
              />
            </svg>
            <div class="sidebar-text-container flex items-center">
              <span class="font-semibold">Dynamics of Modern Web</span>
              <button
                class="download-speech-btn ml-2 p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
              </button>
            </div>
          </a>
          <a
            href="#"
            data-presentation="dev-methodologies"
            class="sidebar-item w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10 hover:text-gray-800 dark:hover:text-white cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 flex-shrink-0"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
            <div class="sidebar-text-container flex items-center">
              <span class="font-semibold">Software Methodologies</span>
              <button
                class="download-speech-btn ml-2 p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
              </button>
            </div>
          </a>
          <a
            href="#"
            data-presentation="ai-chatbot"
            class="sidebar-item w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10 hover:text-gray-800 dark:hover:text-white cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 flex-shrink-0"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
              />
            </svg>
            <div class="sidebar-text-container flex items-center">
              <span class="font-semibold">AI Study Assistant</span>
              <button
                class="download-speech-btn ml-2 p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
              </button>
            </div>
          </a>
          <a
            href="#"
            data-presentation="network-troubleshooting"
            class="sidebar-item w-full flex items-center p-3 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-black/10 dark:hover:bg-white/10 hover:text-gray-800 dark:hover:text-white cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 flex-shrink-0"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9V3m-9 9h18"
              />
            </svg>
            <div class="sidebar-text-container flex items-center">
              <span class="font-semibold">Network Troubleshooting</span>
              <button
                class="download-speech-btn ml-2 p-1 rounded-full hover:bg-black/20 dark:hover:bg-white/20"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
              </button>
            </div>
          </a>
        </div>
      </nav>

      <!-- Main Content -->
      <main
        class="flex-1 flex flex-col items-center justify-center p-6 pt-20 overflow-y-auto relative"
      >
        <!-- Hamburger Menu -->
        <button
          id="hamburger-btn"
          class="md:hidden absolute top-6 left-6 p-2 rounded-lg bg-white/30 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 z-30"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>

        <!-- Top Right Controls -->
        <div class="absolute top-6 right-6 flex items-center space-x-4 z-10" id="user-controls">
          <button
            id="theme-toggle"
            class="p-2 rounded-lg bg-white/30 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-600/50 backdrop-blur-sm transition-colors"
          >
            <svg
              id="theme-icon-sun"
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
            <svg
              id="theme-icon-moon"
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 hidden"
              fill="none"
              viewBox="0 0 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          </button>
          
          <!-- This container will be updated dynamically -->
          <div id="auth-container">
              <button
                id="login-btn"
                class="px-4 py-2 font-semibold text-white rounded-lg backdrop-blur-sm transition-colors"
                style="background-color: var(--primary-color)"
              >
                Login
              </button>
          </div>
        </div>

        <!-- Presentation Wrapper -->
        <div id="web-dynamics" class="presentation-container active"></div>
        <div id="dev-methodologies" class="presentation-container"></div>
        <div id="ai-chatbot" class="presentation-container"></div>
        <div id="network-troubleshooting" class="presentation-container"></div>
      </main>
    </div>

    <!-- Quiz Modal -->
    <div
      id="quiz-modal"
      class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50"
    >
      <div
        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-lg w-full"
      >
        <h2
          id="quiz-question"
          class="text-2xl font-bold mb-6 text-center"
          style="color: var(--primary-color-light)"
        ></h2>
        <div id="quiz-options" class="space-y-4"></div>
        <div id="quiz-feedback" class="mt-6 text-center font-semibold"></div>
      </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-md w-full relative">
            <button id="close-profile-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-center">
                <h2 class="text-2xl font-bold mb-2">My Profile</h2>
                <p id="profile-email" class="text-sm text-gray-500 dark:text-gray-400 mb-6"></p>
            </div>
            
            <div class="bg-black/5 dark:bg-white/5 p-4 rounded-lg mb-6">
                <h3 class="font-bold text-lg mb-2" style="color: var(--primary-color-light);">Total Knowledge XP</h3>
                <p id="profile-total-xp" class="text-3xl font-bold text-center"></p>
            </div>

            <div>
                <h3 class="font-bold text-lg mb-4" style="color: var(--primary-color-light);">Earned Badges</h3>
                <div id="profile-badges-container" class="space-y-3">
                    <!-- Badges will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div
      id="login-modal"
      class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50"
    >
      <div
        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl shadow-xl p-8 max-w-sm w-full relative"
      >
        <button
          id="close-login-modal"
          class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <!-- Login View -->
        <div id="login-view">
          <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
          <form id="login-form">
            <div class="mb-4">
              <label for="email" class="block mb-2 text-sm font-medium"
                >Email</label
              >
              <input
                type="email"
                id="email"
                name="email"
                class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg"
                placeholder="you@example.com"
                required
              />
            </div>
            <div class="mb-4">
              <label for="password" class="block mb-2 text-sm font-medium"
                >Password</label
              >
              <div class="relative">
                 <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg pr-10"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />
                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 dark:text-gray-400 toggle-password">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-open"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-closed hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" /></svg>
                </button>
              </div>
            </div>
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" id="remember-me" class="h-4 w-4 rounded border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 text-cyan-600 focus:ring-cyan-500">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Remember me</span>
                </label>
                <a href="#" id="forgot-password-link" class="text-sm font-medium" style="color: var(--primary-color-light);">Forgot password?</a>
            </div>
            <button
              type="submit"
              class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all"
              style="background-color: var(--primary-color)"
            >
              Login
            </button>
            <p class="mt-4 text-center text-sm">
                Don't have an account? 
                <a href="#" id="show-register-link" class="font-medium" style="color: var(--primary-color-light);">
                    Register with us
                </a>
            </p>
          </form>
        </div>

        <!-- Register View -->
        <div id="register-view" class="hidden">
           <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>
            <form id="register-form">
                <div class="mb-4">
                    <label for="register-email" class="block mb-2 text-sm font-medium">Email</label>
                    <input type="email" id="register-email" name="email" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="you@example.com" required>
                </div>
                <div class="mb-4">
                    <label for="register-password" class="block mb-2 text-sm font-medium">Password</label>
                    <div class="relative">
                        <input type="password" id="register-password" name="password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg pr-10" placeholder="8+ characters, letters, numbers & symbols" required autocomplete="new-password">
                        <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 dark:text-gray-400 toggle-password">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-open"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-closed hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" /></svg>
                        </button>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="confirm-password" class="block mb-2 text-sm font-medium">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirm-password" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg pr-10" placeholder="••••••••" required autocomplete="new-password">
                         <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 dark:text-gray-400 toggle-password">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-open"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 eye-closed hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" /></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">
                    Register
                </button>
                <p class="mt-4 text-center text-sm">
                    Already have an account? 
                    <a href="#" id="show-login-link" class="font-medium" style="color: var(--primary-color-light);">
                        Login
                    </a>
                </p>
            </form>
        </div>

        <!-- Forgot Password View -->
        <div id="forgot-password-view" class="hidden">
            <h2 class="text-2xl font-bold mb-4 text-center">Recover Password</h2>
            <p class="text-center text-sm mb-6 text-gray-600 dark:text-gray-300">Enter your email to get a recovery link.</p>
             <form id="forgot-password-form">
                <div class="mb-6">
                    <label for="recover-email" class="block mb-2 text-sm font-medium">Email</label>
                    <input type="email" id="recover-email" name="email" class="w-full p-2 border border-gray-300/50 dark:border-gray-600/50 bg-white/50 dark:bg-gray-700/50 rounded-lg" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="w-full py-2 px-4 text-white font-bold rounded-lg transition-all" style="background-color: var(--primary-color);">
                    Send Recovery Link
                </button>
                <p class="mt-4 text-center text-sm">
                    <a href="#" id="back-to-login-link" class="font-medium" style="color: var(--primary-color-light);">
                        &larr; Back to Login
                    </a>
                </p>
            </form>
        </div>
      </div>
    </div>
    
    <!-- Notification Element -->
    <div id="notification"></div>

    <script>
      // --- DATA ---
      const presentations = {
        "web-dynamics": {
          title: "The Dynamics of Modern Web",
          slides: [
            `<div class="slide active w-full text-center">
                <h2 class="text-4xl font-bold mb-4">The Dynamics of Modern Web</h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">An interactive journey into the benefits and challenges of dynamic websites.</p>
                <button class="start-btn text-white font-bold py-3 px-6 rounded-lg text-xl transition-all transform hover:scale-105" style="background-color: var(--primary-color);">Start Presentation</button>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Introduction</h2>
                <p class="text-lg mb-4">The web has evolved from static documents to a vibrant, interactive environment. This transformation is powered by <span class="font-bold text-yellow-600 dark:text-yellow-300">dynamic web technologies</span>.</p>
                <p class="text-lg">We'll explore this by looking at case studies like <span class="font-semibold">TikTok</span>, <span class="font-semibold">eBay</span>, and <span class="font-semibold">Figma</span>.</p>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">What is a Static Website?</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">A static website is composed of fixed content, where every visitor sees the exact same information.</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li><span class="font-bold text-green-600 dark:text-green-400">Pros:</span> Fast, secure, simple.</li>
                        <li><span class="font-bold text-red-600 dark:text-red-400">Cons:</span> Inflexible, hard to update.</li>
                    </ul>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">What is a Dynamic Website?</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">A dynamic website generates content "on-the-fly" using server-side languages and databases for an interactive experience.</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li><span class="font-bold text-green-600 dark:text-green-400">Pros:</span> Personalized, interactive, easier content management.</li>
                        <li><span class="font-bold text-red-600 dark:text-red-400">Cons:</span> Complex, potential performance/security issues.</li>
                    </ul>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-6" style="color: var(--primary-color-light);">Dynamic Features in Action</h2>
                <div class="grid md:grid-cols-3 gap-6 text-center">
                    <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg"><h3 class="text-xl font-bold mb-2 text-yellow-600 dark:text-yellow-300">TikTok</h3><p>User-generated content is stored and served dynamically.</p></div>
                    <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg"><h3 class="text-xl font-bold mb-2 text-yellow-600 dark:text-yellow-300">eBay</h3><p>Reputation system dynamically calculates and displays seller scores.</p></div>
                    <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg"><h3 class="text-xl font-bold mb-2 text-yellow-600 dark:text-yellow-300">Figma</h3><p>Real-time collaboration features like commenting are managed dynamically.</p></div>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Key Benefits</h2>
                <ul class="space-y-4 text-lg">
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-green-600 dark:text-green-400">Enhanced User Engagement</li>
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-green-600 dark:text-green-400">Efficient Content Management</li>
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-green-600 dark:text-green-400">Personalization</li>
                </ul>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Potential Challenges</h2>
                <ul class="space-y-4 text-lg">
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-red-600 dark:text-red-400">Performance:</strong> Slow load times if not optimized.</li>
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-red-600 dark:text-red-400">Security Concerns:</strong> Larger "attack surface" for threats.</li>
                    <li class="p-4 bg-black/5 dark:bg-white/5 rounded-lg"><strong class="text-red-600 dark:text-red-400">Complex Development:</strong> Higher costs and resource commitment.</li>
                </ul>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Conclusion</h2>
                <p class="text-lg bg-black/5 dark:bg-white/5 p-4 rounded-lg">Success lies in harnessing the powerful benefits while diligently managing the inherent risks of <span class="font-semibold text-yellow-600 dark:text-yellow-300">performance, security, and complexity</span>.</p>
            </div>`,
            `<div class="slide w-full text-center">
                <h2 class="text-4xl font-bold text-green-500 mb-4">Congratulations!</h2>
                <div class="text-5xl mb-4">🏆</div>
                <p class="text-xl text-gray-600 dark:text-gray-300">You've earned the</p>
                <p class="text-2xl font-bold text-yellow-500 my-2 bg-black/10 dark:bg-white/10 inline-block px-4 py-2 rounded-lg">Web Dynamics Expert Badge</p>
                <p class="text-xl mt-4">Your Final Knowledge XP: <span class="final-xp-score font-bold"></span></p>
                <button class="restart-btn mt-8 text-white font-bold py-3 px-6 rounded-lg transition-all" style="background-color: var(--primary-color);">Restart Presentation</button>
            </div>`,
          ],
          quizzes: [
            {
              slideAfter: 3,
              question:
                "Which type of website is best for a simple, unchanging portfolio?",
              options: [
                "Dynamic Website",
                "Static Website",
                "E-commerce Website",
                "Social Media Website",
              ],
              correctAnswer: "Static Website",
            },
            {
              slideAfter: 6,
              question:
                "Which is a major SECURITY concern for dynamic websites?",
              options: [
                "Slow loading times",
                "Difficult content updates",
                "SQL Injection",
                "Simple design",
              ],
              correctAnswer: "SQL Injection",
            },
            {
              slideAfter: 7,
              question:
                "What is the primary benefit of eBay's dynamic recommendation engine?",
              options: [
                "Faster site speed",
                "Improved security",
                "Personalization to drive sales",
                "Easier for sellers to list items",
              ],
              correctAnswer: "Personalization to drive sales",
            },
          ],
          speech: `Hello and welcome. In this presentation, we're diving into the world of the modern web. The internet has evolved from simple, static pages into the interactive, dynamic environment we use every day. We'll explore the technologies that make this possible by looking at case studies like TikTok, eBay, and Figma.

Next, what is a static website? Think of it as a digital brochure. The content is fixed, and every visitor sees the exact same thing. They're fast and secure, but inflexible. Perfect for a portfolio or a simple info page.

In contrast, a dynamic website generates content 'on-the-fly'. It uses databases and server-side code to create a personalized, real-time experience. This is the technology that powers almost every modern feature, from social media feeds to online shopping carts.

Time for a quick knowledge check!

Let's look at some examples. On TikTok, every video you see, every like and comment, is dynamic content. On eBay, the seller reputation system is a dynamic feature that builds trust. And on Figma, dynamic features allow for real-time collaboration between designers.

The benefits of going dynamic are massive. It leads to far greater user engagement, allows for efficient content management on a huge scale, and enables the kind of personalization that improves user experience and helps businesses achieve their goals.

Alright, another quiz coming up.

However, it's not without its challenges. Dynamic websites can suffer from performance issues if they aren't optimized. They have major security vulnerabilities that need to be addressed, and their complexity makes them more expensive to develop and maintain.

One final question!

To conclude, dynamic websites are the foundation of the modern internet. While the benefits of interactivity and personalization are clear, building a successful dynamic site means carefully managing the inherent risks of performance, security, and complexity.

Congratulations on completing the presentation! You've learned the fundamentals of the dynamic web.`,
          colors: { primary: "#06b6d4", light: "#67e8f9", hover: "#0891b2" },
        },
        "dev-methodologies": {
          title: "Software Development Methodologies",
          slides: [
            `<div class="slide active w-full text-center">
                <h2 class="text-4xl font-bold mb-4">Software Development Methodologies</h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">An overview of Waterfall vs. Agile.</p>
                <button class="start-btn text-white font-bold py-3 px-6 rounded-lg text-xl transition-all transform hover:scale-105" style="background-color: var(--primary-color);">Start Presentation</button>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">What Are Methodologies?</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">They are frameworks to structure, plan, and control the process of creating software. Think of them as blueprints for building a house.</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Provide clear structure and common understanding.</li>
                        <li>Manage project scope, timelines, and resources.</li>
                        <li>Ensure the final product meets user requirements.</li>
                    </ul>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">The Waterfall Methodology</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">A traditional, linear, and sequential approach where each phase must be completed before the next one begins. It flows downwards, like a waterfall.</p>
                    <p class="text-lg font-semibold mt-4">Phases: Requirements → Design → Implementation → Verification → Maintenance</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">The Agile Methodology</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">An iterative and incremental approach focusing on flexibility, collaboration, and rapid delivery. Work is broken into small chunks called "sprints".</p>
                    <p class="text-lg font-semibold mt-4">Core Principle: Responding to change over following a plan.</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Waterfall vs. Agile</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg text-center">
                    <p class="text-lg"><strong class="text-red-500">Waterfall:</strong> Rigid, low customer involvement, best for stable projects.</p>
                     <p class="text-2xl font-bold my-2">vs.</p>
                     <p class="text-lg"><strong class="text-green-500">Agile:</strong> Flexible, high customer involvement, best for evolving projects.</p>
                </div>
            </div>`,
            `<div class="slide w-full text-center">
                <h2 class="text-4xl font-bold text-green-500 mb-4">Congratulations!</h2>
                <div class="text-5xl mb-4">🏆</div>
                <p class="text-xl text-gray-600 dark:text-gray-300">You've completed the presentation and earned the</p>
                <p class="text-2xl font-bold text-yellow-500 my-2 bg-black/10 dark:bg-white/10 inline-block px-4 py-2 rounded-lg">Methodologies Master Badge</p>
                <p class="text-xl mt-4">Your Final Knowledge XP: <span class="final-xp-score font-bold"></span></p>
                <button class="restart-btn mt-8 text-white font-bold py-3 px-6 rounded-lg transition-all" style="background-color: var(--primary-color);">Restart Presentation</button>
            </div>`,
          ],
          quizzes: [
            {
              slideAfter: 2,
              question:
                "What is the primary characteristic of the Waterfall model?",
              options: [
                "Iterative",
                "Flexible",
                "Linear and Sequential",
                "Customer-centric",
              ],
              correctAnswer: "Linear and Sequential",
            },
            {
              slideAfter: 4,
              question:
                "Which methodology is best suited for projects with evolving requirements?",
              options: ["Waterfall", "Agile", "Both", "Neither"],
              correctAnswer: "Agile",
            },
          ],
          speech: `Hello everyone, and welcome to this presentation on software development methodologies. Today we'll be exploring the purpose of these methodologies and dive into two specific examples: Waterfall and Agile.

First, what are software development methodologies? At their core, they're like a roadmap for building software. They give a team a clear process to follow. Using a methodology helps everyone stay organized and ensure the final product is exactly what the client needs.

The Waterfall model is a very straightforward, step-by-step process. You complete one phase completely before starting the next: Requirements, then Design, then Coding, then Testing, and finally, Maintenance. It’s very rigid and best for projects with clear, unchanging requirements.

Time for a quick knowledge check!

Agile is a completely different way of thinking. It's not a single, long project, but a series of short, repeated cycles called sprints. The core idea is to be flexible and adaptive, with a continuous feedback loop with the customer.

So, to compare: Waterfall is linear and rigid. Agile is iterative and flexible. This key difference impacts everything. In Waterfall, a late change can be a major problem. In Agile, those changes are expected.

Let's test your understanding with another question.

To wrap up, there's no single 'best' methodology. The right choice depends on the project. Waterfall gives you a predictable path, while Agile gives you the freedom to adapt.

Congratulations! You've successfully learned about the core software development methodologies.`,
          colors: { primary: "#8b5cf6", light: "#c4b5fd", hover: "#7c3aed" },
        },
        "ai-chatbot": {
          title: "AI Study Assistant",
          slides: [
            `<div class="slide active w-full text-center">
                <h2 class="text-4xl font-bold mb-4">AI-Powered Study Assistant</h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Exploring the feasibility of a chatbot study partner.</p>
                <button class="start-btn text-white font-bold py-3 px-6 rounded-lg text-xl transition-all transform hover:scale-105" style="background-color: var(--primary-color);">Start Presentation</button>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Project Feasibility</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">We must evaluate if the project is viable from three perspectives:</p>
                    <ul class="list-disc list-inside space-y-2 font-semibold">
                        <li><span class="text-green-500">Economic:</span> Can we afford it? Will it provide long-term value?</li>
                        <li><span class="text-blue-500">Operational:</span> Do we have the right team and timeline?</li>
                        <li><span class="text-yellow-500">Technical:</span> Do we have the necessary hardware and software?</li>
                    </ul>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Chosen Methodology: Agile</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">We chose the <strong class="text-yellow-500">Agile (Scrum)</strong> methodology.</p>
                     <p class="text-lg">Why? It's flexible and allows us to adapt to changes and user feedback quickly. We can deliver a working product faster and improve it in small, manageable steps called "sprints."</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Requirement Analysis: MoSCoW</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">We use the MoSCoW method to prioritize features:</p>
                    <ul class="list-none space-y-2">
                        <li><strong><span class="text-red-500">M</span>ust Have:</strong> Secure login, answer course questions, 24/7 availability.</li>
                        <li><strong><span class="text-orange-500">S</span>hould Have:</strong> Practice quizzes, user feedback system.</li>
                        <li><strong><span class="text-yellow-500">C</span>ould Have:</strong> Personalized tips, voice-to-text.</li>
                        <li><strong><span class="text-gray-500">W</span>on't Have:</strong> Give grades, live human chat.</li>
                    </ul>
                </div>
            </div>`,
            `<div class="slide w-full text-center">
                <h2 class="text-4xl font-bold text-green-500 mb-4">Congratulations!</h2>
                <div class="text-5xl mb-4">🏆</div>
                <p class="text-xl text-gray-600 dark:text-gray-300">You've completed the presentation and earned the</p>
                <p class="text-2xl font-bold text-yellow-500 my-2 bg-black/10 dark:bg-white/10 inline-block px-4 py-2 rounded-lg">AI Project Planner Badge</p>
                <p class="text-xl mt-4">Your Final Knowledge XP: <span class="final-xp-score font-bold"></span></p>
                <button class="restart-btn mt-8 text-white font-bold py-3 px-6 rounded-lg transition-all" style="background-color: var(--primary-color);">Restart Presentation</button>
            </div>`,
          ],
          quizzes: [
            {
              slideAfter: 2,
              question:
                "Which methodology was chosen for the chatbot project due to its flexibility?",
              options: ["Waterfall", "Agile", "MoSCoW", "Feasibility"],
              correctAnswer: "Agile",
            },
            {
              slideAfter: 3,
              question: "In the MoSCoW method, what does 'M' stand for?",
              options: ["Maybe Have", "Might Have", "Must Have", "Most Have"],
              correctAnswer: "Must Have",
            },
          ],
          speech: `Welcome! Today, we're looking into the planning of an AI-Powered Study Assistant Chatbot.

First, we must determine if the project is feasible. This means checking if it's economically viable, ensuring we have the operational resources like a skilled team and a realistic timeline, and confirming we meet the technical requirements for hardware and software.

Let's test your knowledge.

For a project like this, we've chosen the Agile methodology, specifically Scrum. Why? Because a chatbot project requires flexibility. Agile allows us to work in short cycles, or sprints, adapting to user feedback and delivering value quickly.

Now for another quiz.

To decide which features to build first, we use the MoSCoW method to prioritize. This stands for Must Have, Should Have, Could Have, and Won't Have. For example, a secure login is a 'Must Have', while personalized study tips might be a 'Could Have'.

Congratulations! You've learned the key planning stages for developing an AI-powered tool.`,
          colors: { primary: "#f97316", light: "#fb923c", hover: "#ea580c" }, // Orange theme
        },
        "network-troubleshooting": {
          title: "Network Troubleshooting",
          slides: [
            `<div class="slide active w-full text-center">
                <h2 class="text-4xl font-bold mb-4">Network Troubleshooting Adventures</h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Solving helpdesk tickets like a pro.</p>
                <button class="start-btn text-white font-bold py-3 px-6 rounded-lg text-xl transition-all transform hover:scale-105" style="background-color: var(--primary-color);">Start Your Quest</button>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">The Helpdesk Ticket</h2>
                <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">Our adventure begins with a helpdesk ticket! This is a real-world request from a user who has a problem, like "I can't connect to the internet!"</p>
                    <p class="text-lg">Our job is to be the hero and solve the mystery.</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Under Pressure!</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">In the real world, you're often on a clock. Many IT jobs require you to solve problems within a 24-hour window.</p>
                    <p class="text-lg">It can be stressful, but it's also a thrilling challenge!</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Our Secret Weapon: The OSI Model</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                    <p class="text-lg mb-3">To solve network mysteries, we use a map called the <strong class="text-yellow-500">OSI Model</strong>. It breaks down the complex network process into 7 simple layers.</p>
                     <p class="text-lg">We can check each layer, one by one, to find where the problem is hiding!</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Layer 1: The Physical Layer</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg text-center">
                     <p class="text-2xl mb-3">This is the first and easiest check!</p>
                    <p class="text-4xl font-bold text-yellow-500">"Is it plugged in?"</p>
                    <p class="text-lg mt-3">You'd be surprised how often a loose cable is the villain!</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Layers 2 & 3: The Address Layers</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                     <p class="text-lg">Is the message going to the right house? Layer 2 (Data Link) uses <strong class="text-yellow-500">MAC addresses</strong> for local delivery, and Layer 3 (Network) uses <strong class="text-yellow-500">IP addresses</strong> for global delivery.</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Layer 4: The Transport Layer</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                     <p class="text-lg">This layer makes sure the data arrives safely and in the right order. Think of it as a delivery service that checks if the whole package made it.</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">The Top Layers (5-7)</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                     <p class="text-lg">These are the layers closest to you! They handle sessions, data formatting, and the application itself (like your web browser). If everything else works, the problem might be with the software.</p>
                </div>
            </div>`,
            `<div class="slide w-full">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--primary-color-light);">Our Detective Tools</h2>
                 <div class="bg-black/5 dark:bg-white/5 p-6 rounded-lg">
                     <p class="text-lg">We use simple command-line tools like <strong class="text-yellow-500">Ping</strong> (to see if a computer is reachable) and <strong class="text-yellow-500">Traceroute</strong> (to see the path the data takes) to find clues.</p>
                </div>
            </div>`,
            `<div class="slide w-full text-center">
                <h2 class="text-4xl font-bold text-green-500 mb-4">Ticket Closed!</h2>
                <div class="text-5xl mb-4">✅</div>
                <p class="text-xl text-gray-600 dark:text-gray-300">You've solved the mystery and earned the</p>
                <p class="text-2xl font-bold text-yellow-500 my-2 bg-black/10 dark:bg-white/10 inline-block px-4 py-2 rounded-lg">Network Detective Badge</p>
                <p class="text-xl mt-4">Your Final Knowledge XP: <span class="final-xp-score font-bold"></span></p>
                <button class="restart-btn mt-8 text-white font-bold py-3 px-6 rounded-lg transition-all" style="background-color: var(--primary-color);">Restart Presentation</button>
            </div>`,
          ],
          quizzes: [
            {
              slideAfter: 4,
              question:
                "What is the name of the 'map' used to troubleshoot network issues in layers?",
              options: [
                "The Helpdesk Model",
                "The OSI Model",
                "The TCP Model",
                "The Network Plan",
              ],
              correctAnswer: "The OSI Model",
            },
            {
              slideAfter: 5,
              question:
                "What is the very first thing to check at the Physical Layer?",
              options: [
                "The IP Address",
                "If the cables are plugged in",
                "The web browser",
                "The computer's name",
              ],
              correctAnswer: "If the cables are plugged in",
            },
          ],
          speech: `Slide 1: Welcome to Network Troubleshooting Adventures! In this session, we're going to learn how to solve helpdesk tickets like a pro. Let's start our quest!
Slide 2: Our adventure begins with a helpdesk ticket. This is just a fancy way of saying a user has a problem, like "I can't connect to the internet!" Our job is to be the hero and figure out what's wrong.
Slide 3: In a real IT job, you're often working against the clock. Problems sometimes need to be solved in 24 hours. It can be a little stressful, but it's also a fun challenge to test your skills.
Slide 4: To solve these network mysteries, we have a secret weapon: The OSI Model. It's like a map that breaks down a network connection into 7 simple layers. By checking each layer, we can find the problem!
Slide 5: Let's start with Layer 1, the Physical Layer. This is always the first and easiest step. All you have to ask is, "Is it plugged in?" You would be shocked how many problems are just a loose cable!
Slide 6: Next up are Layers 2 and 3, the address layers. Think of this as making sure a letter is sent to the right house. Layer 2 uses MAC addresses for the local street, and Layer 3 uses IP addresses for the city and country.
Slide 7: Now for a quick quiz to see what you've learned.
Slide 8: Layer 4 is the Transport Layer. Its job is to make sure your data arrives safely and in the correct order. It's like a delivery service that double-checks that the entire package has arrived without any missing pieces.
Slide 9: The top layers, 5 through 7, are the ones closest to you. They handle things like your session, how the data is formatted, and the application you're using, like your web browser. If all the other layers are working, the problem might just be with the software itself.
Slide 10: Here's the final quiz for this presentation!
Slide 11: To help us find clues, we use simple tools like 'Ping' to see if another computer is online, and 'Traceroute' to see the exact path our data takes across the internet.
Slide 12: And just like that, the ticket is closed! You've successfully solved the mystery. Great job, network detective!`,
          colors: { primary: "#eab308", light: "#fde047", hover: "#ca8a04" }, // Yellow theme
        },
      };

      // --- SCRIPT ---
      document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById("sidebar");
        const hamburgerBtn = document.getElementById("hamburger-btn");
        const hoverTrigger = document.getElementById("sidebar-hover-trigger");
        const mainContent = document.querySelector("main");

        const themeToggleBtn = document.getElementById("theme-toggle");
        const sunIcon = document.getElementById("theme-icon-sun");
        const moonIcon = document.getElementById("theme-icon-moon");
        
        // --- Login & Profile Modal Elements & Forms ---
        const authContainer = document.getElementById('auth-container');
        const loginModal = document.getElementById("login-modal");
        const profileModal = document.getElementById("profile-modal");
        const closeLoginModalBtn = document.getElementById("close-login-modal");
        const closeProfileModalBtn = document.getElementById("close-profile-modal");

        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const forgotPasswordForm = document.getElementById('forgot-password-form');
        const notificationEl = document.getElementById('notification');

        const loginView = document.getElementById('login-view');
        const registerView = document.getElementById('register-view');
        const forgotPasswordView = document.getElementById('forgot-password-view');

        const showRegisterLink = document.getElementById('show-register-link');
        const showLoginLink = document.getElementById('show-login-link');
        const forgotPasswordLink = document.getElementById('forgot-password-link');
        const backToLoginLink = document.getElementById('back-to-login-link');
        const togglePasswordBtns = document.querySelectorAll('.toggle-password');


        const quizModal = document.getElementById("quiz-modal");
        const quizQuestionEl = document.getElementById("quiz-question");
        const quizOptionsEl = document.getElementById("quiz-options");
        const quizFeedbackEl = document.getElementById("quiz-feedback");
        const root = document.documentElement;

        let activePresentation = null;
        let currentUser = null;

        // --- Notification Function ---
        function showNotification(message, type = 'success') {
            notificationEl.textContent = message;
            notificationEl.className = ''; // Clear previous classes
            notificationEl.classList.add(type, 'show');
            setTimeout(() => {
                notificationEl.classList.remove('show');
            }, 3000);
        }

        // --- Authentication & Profile UI Management ---
        function updateUIForLoggedInUser(user) {
            currentUser = user;
            authContainer.innerHTML = `
                <div class="flex items-center space-x-2">
                     <button id="profile-btn" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color)">My Profile</button>
                    <button id="logout-btn" class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: var(--primary-color-hover)">Logout</button>
                </div>
            `;
            loginModal.classList.add('hidden');
        }
        
        function updateUIForLoggedOutUser() {
            currentUser = null;
             authContainer.innerHTML = `
                <button id="login-btn" class="px-4 py-2 font-semibold text-white rounded-lg backdrop-blur-sm transition-colors" style="background-color: var(--primary-color)">
                    Login
                </button>
            `;
        }

        function showProfileModal() {
            if (!currentUser) return;

            const profileEmailEl = document.getElementById('profile-email');
            const totalXpEl = document.getElementById('profile-total-xp');
            const badgesContainerEl = document.getElementById('profile-badges-container');

            profileEmailEl.textContent = currentUser.email;
            badgesContainerEl.innerHTML = '';

            let totalXp = 0;
            if (currentUser.progress_data && typeof currentUser.progress_data === 'object') {
                Object.values(currentUser.progress_data).forEach(progress => {
                    totalXp += progress.xp || 0;
                });
            }
            totalXpEl.textContent = `${totalXp} XP`;
            
            let hasBadges = false;
            for (const presentationId in presentations) {
                const presentationData = presentations[presentationId];
                const userProgress = currentUser.progress_data ? currentUser.progress_data[presentationId] : null;
                const finalSlideIndex = presentationData.slides.length - 1;

                if (userProgress && userProgress.slide === finalSlideIndex) {
                    hasBadges = true;
                    const badgeName = presentationData.slides[finalSlideIndex].match(/<p class="text-2xl.*?>(.*?)<\/p>/)[1];
                    const badgeElement = document.createElement('div');
                    badgeElement.className = 'bg-black/10 dark:bg-white/10 p-3 rounded-lg flex items-center space-x-3';
                    badgeElement.innerHTML = `
                        <span class="text-2xl">🏆</span>
                        <span class="font-semibold">${badgeName}</span>
                    `;
                    badgesContainerEl.appendChild(badgeElement);
                }
            }

            if (!hasBadges) {
                badgesContainerEl.innerHTML = '<p class="text-gray-500 dark:text-gray-400">No badges earned yet. Complete a presentation to get your first one!</p>';
            }

            profileModal.classList.remove('hidden');
        }


        // --- Sidebar Logic ---
        hamburgerBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          sidebar.classList.toggle("open");
        });

        hoverTrigger.addEventListener("mouseenter", () =>
          sidebar.classList.add("open")
        );
        sidebar.addEventListener("mouseleave", () =>
          sidebar.classList.remove("open")
        );

        mainContent.addEventListener("click", () => {
          if (sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
          }
        });

        // --- Theme & Modal Logic ---
        function updateThemeIcons() {
          if (document.documentElement.classList.contains("dark")) {
            moonIcon.classList.add("hidden");
            sunIcon.classList.remove("hidden");
          } else {
            moonIcon.classList.remove("hidden");
            sunIcon.classList.add("hidden");
          }
        }

        themeToggleBtn.addEventListener("click", () => {
          document.documentElement.classList.toggle("dark");
          localStorage.setItem(
            "theme",
            document.documentElement.classList.contains("dark")
              ? "dark"
              : "light"
          );
          updateThemeIcons();
        });

        // --- New Login & Profile Modal Logic ---
        
        document.getElementById('user-controls').addEventListener('click', (e) => {
            if (e.target.id === 'login-btn') {
                loginModal.classList.remove('hidden');
            }
            if (e.target.id === 'logout-btn') {
                updateUIForLoggedOutUser();
                showNotification('You have been logged out.', 'success');
            }
            if (e.target.id === 'profile-btn') {
                showProfileModal();
            }
        });

        closeLoginModalBtn.addEventListener("click", () => loginModal.classList.add("hidden"));
        closeProfileModalBtn.addEventListener("click", () => profileModal.classList.add("hidden"));
        loginModal.addEventListener("click", (e) => {
          if (e.target === loginModal) loginModal.classList.add("hidden");
        });
        profileModal.addEventListener("click", (e) => {
          if (e.target === profileModal) profileModal.classList.add("hidden");
        });

        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginView.classList.add('hidden');
            forgotPasswordView.classList.add('hidden');
            registerView.classList.remove('hidden');
        });

        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            registerView.classList.add('hidden');
            forgotPasswordView.classList.add('hidden');
            loginView.classList.remove('hidden');
        });

        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginView.classList.add('hidden');
            registerView.classList.add('hidden');
            forgotPasswordView.classList.remove('hidden');
        });
        
        backToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            forgotPasswordView.classList.add('hidden');
            registerView.classList.add('hidden');
            loginView.classList.remove('hidden');
        });

        togglePasswordBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const passwordInput = btn.previousElementSibling;
                const eyeOpen = btn.querySelector('.eye-open');
                const eyeClosed = btn.querySelector('.eye-closed');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClosed.classList.add('hidden');
                }
            });
        });
        
        // --- Form Submission Logic ---
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                showNotification('Passwords do not match.', 'error');
                return;
            }

            const formData = new FormData(registerForm);
            const data = {
                action: 'register',
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('', { // Post to the current URL
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                showNotification(response.message, response.status);
                if (response.status === 'success') {
                    // Switch to login view after successful registration
                    registerView.classList.add('hidden');
                    loginView.classList.remove('hidden');
                    registerForm.reset();
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                showNotification('An error occurred. Check the console.', 'error')
            });
        });

        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const data = {
                action: 'login',
                email: formData.get('email'),
                password: formData.get('password')
            };

            fetch('', { // Post to the current URL
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                showNotification(response.message, response.status);
                if (response.status === 'success') {
                    updateUIForLoggedInUser(response.user);
                    // Here you would merge localStorage progress with DB progress
                    // For now, we'll just log the received progress data
                    console.log('Progress from DB:', response.user.progress_data);
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                showNotification('An error occurred. Check the console.', 'error')
            });
        });
        
        forgotPasswordForm.addEventListener('submit', (e) => {
            e.preventDefault();
             // This part is front-end only for now, as the backend isn't set up for it.
            showNotification('Password recovery is not yet implemented.', 'error');
        });


        // --- Presentation Core Logic ---
        function initPresentation(presentationId) {
          if (activePresentation) {
            // Clear previous presentation state if needed
          }
          activePresentation = presentations[presentationId];
          const container = document.getElementById(presentationId);

          container.innerHTML = ""; // Clear previous content

          const presentationWrapper = document.createElement("div");
          presentationWrapper.className =
            "w-full max-w-4xl mx-auto bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden";

          presentationWrapper.innerHTML = `
            <div class="presentation-header p-6 border-b border-black/10 dark:border-white/10 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold" style="color: var(--primary-color-light);">${
                      activePresentation.title
                    }</h1>
                    <div class="text-right">
                        <p class="font-semibold text-lg">Knowledge XP: <span class="xp-score">0</span></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Complete slides and quizzes to learn!</p>
                    </div>
                </div>
                <div class="w-full bg-black/10 dark:bg-white/10 rounded-full h-4">
                    <div class="progress-bar h-4 rounded-full transition-all" style="width: 0%; background-color: var(--primary-color);"></div>
                </div>
            </div>
            <div class="slides-container p-8 md:p-12 min-h-[50vh] flex items-center">
                ${activePresentation.slides.join("")}
            </div>
            <div class="presentation-nav p-6 border-t border-black/10 dark:border-white/10 flex justify-between items-center hidden">
                <button class="prev-btn bg-black/10 dark:bg-white/10 hover:bg-black/20 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold py-2 px-4 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</button>
                <div class="slide-counter text-sm text-gray-500 dark:text-gray-400"></div>
                <button class="next-btn text-white font-bold py-2 px-4 rounded-lg transition-all" style="background-color: var(--primary-color);">Next</button>
            </div>
        `;
          container.appendChild(presentationWrapper);

          runPresentationLogic(presentationId, presentationWrapper);
        }

        function runPresentationLogic(presentationId, wrapper) {
          const presentationData = presentations[presentationId];
          let currentSlide = 0;
          let xpScore = 0;
          const slideXP = 10;
          const quizXP = 50;

          const slides = wrapper.querySelectorAll(".slide");
          const totalSlides = slides.length;
          const contentSlidesCount = totalSlides > 1 ? totalSlides - 1 : 1;

          const startBtn = wrapper.querySelector(".start-btn");
          const nextBtn = wrapper.querySelector(".next-btn");
          const prevBtn = wrapper.querySelector(".prev-btn");
          const restartBtn = wrapper.querySelector(".restart-btn");
          const headerEl = wrapper.querySelector(".presentation-header");
          const navEl = wrapper.querySelector(".presentation-nav");
          const progressBar = wrapper.querySelector(".progress-bar");
          const xpScoreEl = wrapper.querySelector(".xp-score");
          const slideCounterEl = wrapper.querySelector(".slide-counter");
          const finalXpScoreEl = wrapper.querySelector(".final-xp-score");

          const storageKey = `presentationProgress_${presentationId}`;

          function saveProgress() {
            // We still save to localStorage for guests
            // Logged in user progress should be saved to DB via API call (future step)
            localStorage.setItem(
              storageKey,
              JSON.stringify({ xp: xpScore, slide: currentSlide })
            );
          }

          function loadProgress() {
            // If user is logged in and has progress data, use it
            if (currentUser && currentUser.progress_data && currentUser.progress_data[presentationId]) {
                 const progress = currentUser.progress_data[presentationId];
                 xpScore = progress.xp || 0;
                 currentSlide = progress.slide || 0;
            } else {
                // Otherwise, fall back to localStorage for guests
                const saved = localStorage.getItem(storageKey);
                if (saved) {
                  const progress = JSON.parse(saved);
                  xpScore = progress.xp || 0;
                  currentSlide = progress.slide || 0;
                } else {
                  xpScore = 0;
                  currentSlide = 0;
                }
            }
          }

          function updateSlide() {
            const isStarted = currentSlide > 0;
            headerEl.classList.toggle("hidden", !isStarted);
            navEl.classList.toggle("hidden", !isStarted || totalSlides <= 1);

            slides.forEach((slide, index) =>
              slide.classList.toggle("active", index === currentSlide)
            );

            const progress = isStarted
              ? (currentSlide / contentSlidesCount) * 100
              : 0;
            progressBar.style.width = `${progress}%`;

            if (navEl) {
              prevBtn.disabled = currentSlide <= 0;
              if (totalSlides <= 1) {
                navEl.classList.add("hidden");
              } else {
                prevBtn.disabled = currentSlide <= 0;
                nextBtn.disabled = currentSlide === totalSlides - 1;
                nextBtn.textContent =
                  currentSlide === totalSlides - 2 ? "Finish" : "Next";
                slideCounterEl.textContent = isStarted
                  ? `Slide ${currentSlide} of ${contentSlidesCount}`
                  : "";
              }
            }

            if (finalXpScoreEl && currentSlide === totalSlides - 1) {
              finalXpScoreEl.textContent = xpScore;
              if (navEl) navEl.classList.add("hidden");
            }
            xpScoreEl.textContent = xpScore;
            saveProgress();
          }

          function showQuiz(quiz) {
            quizQuestionEl.textContent = quiz.question;
            quizOptionsEl.innerHTML = "";
            quizFeedbackEl.innerHTML = "";

            quiz.options.forEach((optionText) => {
              const button = document.createElement("button");
              button.textContent = optionText;
              button.className =
                "w-full text-left bg-black/10 dark:bg-white/10 hover:bg-opacity-20 p-4 rounded-lg transition-all";
              button.onclick = () => {
                const buttons = quizOptionsEl.querySelectorAll("button");
                buttons.forEach((btn) => {
                  btn.disabled = true;
                  if (btn.textContent === quiz.correctAnswer)
                    btn.classList.add("bg-green-500/50");
                  else if (btn.textContent === optionText)
                    btn.classList.add("bg-red-500/50");
                });
                if (optionText === quiz.correctAnswer) {
                  xpScore += quizXP;
                  xpScoreEl.textContent = xpScore;
                  quizFeedbackEl.textContent = `Correct! +${quizXP} XP`;
                  quizFeedbackEl.className =
                    "mt-6 text-center font-semibold text-green-500";
                } else {
                  quizFeedbackEl.textContent = `Not quite. The correct answer was "${quiz.correctAnswer}".`;
                  quizFeedbackEl.className =
                    "mt-6 text-center font-semibold text-red-500";
                }
                setTimeout(() => quizModal.classList.add("hidden"), 2000);
              };
              quizOptionsEl.appendChild(button);
            });
            quizModal.classList.remove("hidden");
          }

          if (startBtn)
            startBtn.addEventListener("click", () => {
              currentSlide++;
              updateSlide();
            });
          if (nextBtn) {
            nextBtn.addEventListener("click", () => {
              if (currentSlide < totalSlides - 1) {
                if (currentSlide > 0) xpScore += slideXP;
                currentSlide++;
                updateSlide();
                const quizToShow = presentationData.quizzes.find(
                  (q) => q.slideAfter === currentSlide - 1
                );
                if (quizToShow) showQuiz(quizToShow);
              }
            });
          }
          if (prevBtn)
            prevBtn.addEventListener("click", () => {
              if (currentSlide > 0) {
                currentSlide--;
                updateSlide();
              }
            });
          if (restartBtn)
            restartBtn.addEventListener("click", () => {
              currentSlide = 0;
              xpScore = 0;
              saveProgress();
              updateSlide();
            });

          loadProgress();
          updateSlide();
        }

        function switchPresentation(presentationId) {
          document
            .querySelectorAll(".presentation-container")
            .forEach((c) => c.classList.remove("active"));
          document.getElementById(presentationId).classList.add("active");

          document.querySelectorAll(".sidebar-item").forEach((item) => {
            const isActive = item.dataset.presentation === presentationId;
            item.classList.toggle("active", isActive);
            item.classList.toggle("bg-black/10", isActive);
            item.classList.toggle("dark:bg-white/10", isActive);
            item.classList.toggle("text-gray-500", !isActive);
            item.classList.toggle("dark:text-gray-400", !isActive);
          });

          const colors = presentations[presentationId].colors;
          root.style.setProperty("--primary-color", colors.primary);
          root.style.setProperty("--primary-color-light", colors.light);
          root.style.setProperty("--primary-color-hover", colors.hover);

          initPresentation(presentationId);
        }

        document
          .getElementById("sidebar-menu")
          .addEventListener("click", (e) => {
            const sidebarItem = e.target.closest(".sidebar-item");
            if (sidebarItem) {
              e.preventDefault();
              const presentationId = sidebarItem.dataset.presentation;
              if (e.target.closest(".download-speech-btn")) {
                const speech = presentations[presentationId].speech;
                const title = presentations[presentationId].title.replace(
                  /\s+/g,
                  "_"
                );
                const blob = new Blob([speech], { type: "text/plain" });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = `${title}_Speech.txt`;
                link.click();
                URL.revokeObjectURL(link.href);
              } else {
                switchPresentation(presentationId);
              }
            }
          });

        // Initial setup
        updateThemeIcons();
        switchPresentation("web-dynamics"); // Default presentation
      });
    </script>
  </body>
</html>

