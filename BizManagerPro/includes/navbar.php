<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo strpos($_SERVER['PHP_SELF'], 'dashboard') !== false ? '../index.php' : (strpos($_SERVER['PHP_SELF'], 'modules') !== false ? '../../index.php' : 'index.php'); ?>">
            <i class="fas fa-chart-line"></i> BizManagerPro
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../dashboard/dashboard.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo 'dashboard.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../../dashboard/dashboard.php';
                        } else {
                            echo 'dashboard/dashboard.php';
                        }
                    ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($current_page, 'client') !== false ? 'active' : ''; ?>" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../modules/clients/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../clients/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo '../modules/clients/index.php';
                        } else {
                            echo 'modules/clients/index.php';
                        }
                    ?>">
                        <i class="fas fa-users"></i> Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($current_page, 'product') !== false ? 'active' : ''; ?>" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../modules/products/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../products/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo '../modules/products/index.php';
                        } else {
                            echo 'modules/products/index.php';
                        }
                    ?>">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($current_page, 'order') !== false ? 'active' : ''; ?>" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../modules/orders/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../orders/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo '../modules/orders/index.php';
                        } else {
                            echo 'modules/orders/index.php';
                        }
                    ?>">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($current_page, 'payment') !== false ? 'active' : ''; ?>" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../modules/payments/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../payments/index.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo '../modules/payments/index.php';
                        } else {
                            echo 'modules/payments/index.php';
                        }
                    ?>">
                        <i class="fas fa-credit-card"></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php
                        if (strpos($_SERVER['PHP_SELF'], 'dashboard/reports') !== false) {
                            echo '../../modules/users/logout.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
                            echo '../../modules/users/logout.php';
                        } elseif (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
                            echo '../modules/users/logout.php';
                        } else {
                            echo 'modules/users/logout.php';
                        }
                    ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
