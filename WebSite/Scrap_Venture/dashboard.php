<?php
require_once 'includes/functions.php';
startSessionIfNotStarted();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$totalBottles = getUserTotalBottles($userId);
$totalAmount = $totalBottles * 0.50; // Total lifetime earnings

// Get current balance (not yet withdrawn)
$currentBalance = getCurrentBalance($userId); // You'll need to implement this function

// Handle bottle submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bottle_count'])) {
        $bottleCount = (int)$_POST['bottle_count'];
        
        if ($bottleCount > 0) {
            if (addRecyclingRecord($userId, $bottleCount)) {
                $message = "Thank you for recycling $bottleCount bottles!";
                $totalBottles = getUserTotalBottles($userId);
                $totalAmount = $totalBottles * 0.50;
                $currentBalance = getCurrentBalance($userId); // Refresh balance
            } else {
                $message = "Failed to record your recycling. Please try again.";
            }
        } else {
            $message = "Please enter a valid number of bottles.";
        }
    }
    
    // Handle withdrawal request
    if (isset($_POST['withdraw'])) {
        if ($currentBalance > 0) {
            if (processWithdrawal($userId, $currentBalance)) {
                $message = "Withdrawal request successful! Please wait 12 hours for processing.";
                $currentBalance = 0;
            } else {
                $message =  "Withdrawal failed. You need at least 2 BDT (4 bottles) to withdraw. Current balance: $currentBalance BDT";
            }
        } else {
            $message = "No balance available for withdrawal.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function confirmWithdraw() {
            if (confirm("Are you sure you want to withdraw your balance? Please allow 12 hours for processing.")) {
                document.getElementById('withdrawForm').submit();
            }
        }
    </script>
</head>
<body class="dashboard-page">
        <!-- Logo in top right corner -->
    <div class="logo-container">
        <img src="./assets/image/image.png" alt="Company Logo" class="logo">
    </div>
    <div class="container">
        <h1>Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'failed') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="bottle_count">How many bottles are you recycling today?</label>
                <input type="number" id="bottle_count" name="bottle_count" min="1" value="1" required>
                <small>Price per bottle: 0.50 BDT</small>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
        
        <div class="stats">
            <div class="stat-box">
                <h3><i class="fas fa-chart-line"></i> Lifetime Statistics</h3>
                <p>Total recycled bottles: <strong><?php echo $totalBottles; ?></strong></p>
                <p>Total earnings: <strong><?php echo $totalAmount; ?> BDT</strong></p>
            </div>
            
 <div class="balance-box">
            <h3><i class="fas fa-wallet"></i> Current Balance</h3>
            <p>Available for withdrawal: <strong class="balance-amount"><?php echo $currentBalance; ?> BDT</strong></p>
            <button onclick="openWithdrawModal()" class="btn withdraw-btn" <?php echo $currentBalance == 0 ? 'disabled' : ''; ?>>
                <i class="fas fa-money-bill-wave"></i> Withdraw Balance
            </button>
        </div>
        </div>
  
  <!-- Withdrawal Confirmation Modal -->
<div id="withdrawModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="modal-title">Confirm Withdrawal</div>
        <div class="modal-message">
            You are about to withdraw <strong><?php echo $currentBalance; ?> BDT</strong>.
            This may take up to 12 hours to process.
        </div>
        <div class="modal-actions">
            <button type="button" class="modal-btn cancel-btn">Cancel</button>
            <button type="button" class="modal-btn confirm-btn" onclick="submitWithdraw()">Confirm</button>
        </div>
    </div>
</div>

        <!-- Add this hidden form for withdrawals -->
<form id="withdrawForm" method="POST" style="display: none;">
    <input type="hidden" name="withdraw" value="1">
</form>

        <a href="logout.php" class="btn logout"><i class="fas fa-power-off"></i> Logout</a>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>