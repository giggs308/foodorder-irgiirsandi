<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FoodOrder</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="../../assets/css/admin.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            /* Match user dashboard theme */
            --primary-color: #ff6b35;   /* orange */
            --secondary-color: #ff814e; /* lighter orange */
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #fff8f3;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Main content wrapper */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Content area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
            padding-top: calc(var(--header-height) + 20px);
        }
        
        /* Page header */
        .page-header {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Buttons */
        .btn {
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn i {
            font-size: 1.1em;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-1px);
        }
        /* Ensure Bootstrap bg-primary follows theme */
        .bg-primary { background-color: var(--primary-color) !important; }
        .navbar.bg-primary { border-bottom: 1px solid rgba(0,0,0,0.05); }
        
        /* Forms */
        .form-control, .form-select {
            border-radius: 6px;
            padding: 0.6rem 0.9rem;
            border: 1px solid #dee2e6;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f0f2f5;
        }
        
        .table tbody tr:last-child td {
            border-bottom: 0;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.4em 0.7em;
            border-radius: 4px;
        }
        
        .badge-primary {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .badge-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .badge-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .badge-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 1040;
                height: 100vh;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1039;
            }
            
            .overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Overlay for mobile menu -->
    <div class="overlay"></div>
    
    <div class="main-wrapper">
