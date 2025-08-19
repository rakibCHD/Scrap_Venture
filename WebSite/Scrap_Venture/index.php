<?php
require_once 'includes/functions.php';
startSessionIfNotStarted();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Clean phone number (remove all non-digits)
    $cleanPhone = preg_replace('/\D/', '', $phone);
    
    if (empty($name) || empty($cleanPhone)) {
        $error = "Please enter both name and phone number";
    } elseif (strlen($cleanPhone) !== 11) {
        $error = "Please enter exactly 11-digit phone number";
    } else {
        // Check if user exists using cleaned phone number
        $user = getUserByPhone($cleanPhone);
        
        if ($user) {
            // Existing user - login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            // New user - register
            $userId = createUser($name, $cleanPhone);
            
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <!-- Logo in top right corner -->
    <div class="logo-container">
        <img src="./assets/image/image.png" alt="Company Logo" class="logo">
    </div>
    
    <div class="container">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <h4>Plastic Collector Vending Machine</h4>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Account Number: (Bkash/Nagad)</label>
                <input type="tel" id="phone" name="phone" 
                       pattern="\d{11}" 
                       title="Please enter exactly 11 digits"
                       maxlength="11" 
                       required>
                <small class="hint">Enter 11 digits (no spaces or dashes)</small>
            </div>
            <button type="submit" class="btn">OK</button>
        </form>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>