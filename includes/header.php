<?php require_once dirname(__DIR__) . '/config/base_url.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BukuTabungan') ?> — BukuTabungan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

    <!-- Mobile top bar -->
    <header class="mobile-topbar">
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <span class="mobile-brand">💼 BukuTabungan</span>
        <div style="width:40px"></div>
    </header>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">💼</span>
            <span class="brand-name">BukuTabungan</span>
            <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">✕</button>
        </div>
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"><span class="nav-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/transactions.php" class="<?= basename($_SERVER['PHP_SELF']) === 'transactions.php' ? 'active' : '' ?>"><span class="nav-icon">💸</span> Transaksi</a></li>
            <li><a href="<?= BASE_URL ?>/wallets.php" class="<?= basename($_SERVER['PHP_SELF']) === 'wallets.php' ? 'active' : '' ?>"><span class="nav-icon">👛</span> Dompet</a></li>
            <li><a href="<?= BASE_URL ?>/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>"><span class="nav-icon">🏷️</span> Kategori</a></li>
            <li><a href="<?= BASE_URL ?>/summary.php" class="<?= basename($_SERVER['PHP_SELF']) === 'summary.php' ? 'active' : '' ?>"><span class="nav-icon">📊</span> Ringkasan</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        </div>
        <div class="page-body">
