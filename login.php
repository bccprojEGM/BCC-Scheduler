<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Binalbagan Catholic College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #0f766e, #115e59, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="glass max-w-md w-full p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                 <span class="text-teal-700 font-bold text-2xl">BCC</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Admin Login</h1>
            <p class="text-teal-100">Official Schedule Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded mb-6 text-sm">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-teal-500 mb-1">Username</label>
                <input type="text" id="username" name="username" required 
                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-teal-500 mb-1">Password</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-teal-100">
                    <input type="checkbox" class="mr-2 rounded bg-slate-800 border-slate-700 text-teal-500"> Remember Me
                </label>
                <a href="#" class="text-teal-400 hover:text-teal-300">Forgot Password?</a>
            </div>
            <button type="submit" 
                class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-500 text-white font-bold rounded-lg shadow-lg transform hover:-translate-y-1 transition-all duration-200">
                Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center text-teal-200 text-xs">
            &copy; <?php echo date('Y'); ?> Binalbagan Catholic College<br>
            Official Scheduling Management System
        </div>
    </div>
</body>
</html>
