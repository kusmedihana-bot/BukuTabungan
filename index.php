<?php require_once __DIR__ . '/config/base_url.php'; ?>
<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();
$pageTitle = 'Dashboard';

// Total balances
$totalBalance = $db->query("SELECT COALESCE(SUM(balance), 0) FROM wallets")->fetchColumn();

// This month income & expense
$month = date('Y-m');
$monthIncome = $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND strftime('%Y-%m', transaction_date)='$month'")->fetchColumn();
$monthExpense = $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND strftime('%Y-%m', transaction_date)='$month'")->fetchColumn();

// Recent transactions
$recentTx = $db->query("
    SELECT t.*, c.name as cat_name, c.icon as cat_icon, w.name as wallet_name
    FROM transactions t
    JOIN categories c ON c.id = t.category_id
    JOIN wallets w ON w.id = t.wallet_id
    ORDER BY t.transaction_date DESC, t.created_at DESC
    LIMIT 10
")->fetchAll();

// Wallets
$wallets = $db->query("SELECT * FROM wallets ORDER BY balance DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card accent">
        <div class="stat-label">💼 Total Saldo</div>
        <div class="stat-value">Rp <?= number_format($totalBalance, 0, ',', '.') ?></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">📥 Pemasukan Bulan Ini</div>
        <div class="stat-value">Rp <?= number_format($monthIncome, 0, ',', '.') ?></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">📤 Pengeluaran Bulan Ini</div>
        <div class="stat-value">Rp <?= number_format($monthExpense, 0, ',', '.') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">📊 Net Bulan Ini</div>
        <div class="stat-value" style="color: <?= ($monthIncome - $monthExpense) >= 0 ? 'var(--green)' : 'var(--red)' ?>">
            Rp <?= number_format($monthIncome - $monthExpense, 0, ',', '.') ?>
        </div>
    </div>
</div>

<!-- Wallets -->
<h2 class="section-title" style="margin-bottom:12px;">Dompet Saya</h2>
<div class="wallets-grid" style="margin-bottom: 24px;">
    <?php foreach ($wallets as $w): ?>
    <div class="wallet-card" style="--w-color: <?= htmlspecialchars($w['color']) ?>; background: var(--bg2);">
        <div class="w-name"><?= htmlspecialchars($w['name']) ?></div>
        <div class="w-balance">Rp <?= number_format($w['balance'], 0, ',', '.') ?></div>
        <div class="w-type"><?= $w['type'] === 'savings' ? '🏦 Tabungan' : '👛 Reguler' ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($wallets)): ?>
    <div style="color: var(--text2); font-size:14px;">Belum ada dompet. <a href="<?= BASE_URL ?>/wallets.php" style="color:var(--accent2)">Tambah sekarang</a></div>
    <?php endif; ?>
</div>

<!-- Recent Transactions -->
<div class="content-section">
    <div class="section-title">Transaksi Terakhir</div>
    <?php if (empty($recentTx)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>Belum ada transaksi. <a href="<?= BASE_URL ?>/transactions.php" style="color:var(--accent2)">Tambah transaksi</a></p>
        </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Dompet</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentTx as $tx): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                <td><?= $tx['cat_icon'] ?> <?= htmlspecialchars($tx['cat_name']) ?></td>
                <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
                <td><?= htmlspecialchars($tx['wallet_name']) ?></td>
                <td class="amount-<?= $tx['type'] ?>">
                    <?= $tx['type'] === 'income' ? '+' : '-' ?>Rp <?= number_format($tx['amount'], 0, ',', '.') ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
