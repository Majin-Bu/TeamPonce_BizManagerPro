<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BizManagerPro - Enterprise Business Management Platform">
    <meta name="theme-color" content="#0f766e">
    <title>BizManagerPro - Business Management System</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Premium Custom Styles -->
    <link rel="stylesheet" href="<?php echo strpos($_SERVER['PHP_SELF'], 'dashboard') !== false ? '../assets/css/style.css' : (strpos($_SERVER['PHP_SELF'], 'modules') !== false ? '../../assets/css/style.css' : 'assets/css/style.css'); ?>">
    
    <style>
        /* Smooth scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #0f766e;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #14b8a6;
        }
    </style>
    
    <!-- Bootstrap JS Bundle loaded in head for dropdown functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
