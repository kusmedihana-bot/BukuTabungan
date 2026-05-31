<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BukuTabungan') ?> — BukuTabungan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    <?php
    // Depth-aware relative path: works on localhost/subdir AND Railway root
    $depth = substr_count(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])), '/');
    // On Railway root: depth=1 (/index.php → dirname=/  → 1 slash → 0 steps up)
    // On localhost:    depth=2 (/bukutabungan/index.php → dirname=/bukutabungan → 2 slashes → 1 step up)
    $root = str_repeat('../', max(0, $depth - 1));
    ?>
    <link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

    <header class="mobile-topbar">
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <span class="mobile-brand">💼 BukuTabungan</span>
        <div style="width:40px"></div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">💼</span>
            <span class="brand-name">BukuTabungan</span>
            <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">✕</button>
        </div>
        <ul class="nav-links">
            <li><a href="<?= $root ?>index.php"        class="<?= basename($_SERVER['PHP_SELF']) === 'index.php'        ? 'active' : '' ?>"><span class="nav-icon">🏠</span> Dashboard</a></li>
            <li><a href="<?= $root ?>transactions.php"  class="<?= basename($_SERVER['PHP_SELF']) === 'transactions.php'  ? 'active' : '' ?>"><span class="nav-icon">💸</span> Transaksi</a></li>
            <li><a href="<?= $root ?>wallets.php"       class="<?= basename($_SERVER['PHP_SELF']) === 'wallets.php'       ? 'active' : '' ?>"><span class="nav-icon">👛</span> Dompet</a></li>
            <li><a href="<?= $root ?>categories.php"    class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php'    ? 'active' : '' ?>"><span class="nav-icon">🏷️</span> Kategori</a></li>
            <li><a href="<?= $root ?>summary.php"       class="<?= basename($_SERVER['PHP_SELF']) === 'summary.php'       ? 'active' : '' ?>"><span class="nav-icon">📊</span> Ringkasan</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        </div>
        <div class="page-body">
