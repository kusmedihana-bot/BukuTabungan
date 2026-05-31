<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();
$pageTitle = 'Transaksi';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $walletId   = (int)($_POST['wallet_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $amount     = (float)str_replace(['.', ','], ['', '.'], $_POST['amount'] ?? 0);
        $type       = $_POST['type'] ?? '';
        $desc       = trim($_POST['description'] ?? '');
        $date       = $_POST['transaction_date'] ?? date('Y-m-d');

        if (!$walletId || !$categoryId || $amount <= 0 || !in_array($type, ['income','expense'])) {
            $error = 'Lengkapi semua field dengan benar.';
        } else {
            try {
                $db->beginTransaction();
                $stmt = $db->prepare("INSERT INTO transactions (wallet_id, category_id, amount, type, description, transaction_date) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$walletId, $categoryId, $amount, $type, $desc, $date]);
                $op = $type === 'income' ? '+' : '-';
                $db->prepare("UPDATE wallets SET balance = balance $op ? WHERE id = ?")->execute([$amount, $walletId]);
                $db->commit();
                $success = 'Transaksi berhasil ditambahkan!';
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $tx = $db->prepare("SELECT * FROM transactions WHERE id = ?");
        $tx->execute([$id]);
        $tx = $tx->fetch();
        if ($tx) {
            try {
                $db->beginTransaction();
                $op = $tx['type'] === 'income' ? '-' : '+';
                $db->prepare("UPDATE wallets SET balance = balance $op ? WHERE id = ?")->execute([$tx['amount'], $tx['wallet_id']]);
                $db->prepare("DELETE FROM transactions WHERE id = ?")->execute([$id]);
                $db->commit();
                $success = 'Transaksi dihapus.';
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Gagal menghapus.';
            }
        }
    }
}

// Fetch all transactions
$transactions = $db->query("
    SELECT t.*, c.name as cat_name, c.icon as cat_icon, w.name as wallet_name
    FROM transactions t
    JOIN categories c ON c.id = t.category_id
    JOIN wallets w ON w.id = t.wallet_id
    ORDER BY t.transaction_date DESC, t.created_at DESC
")->fetchAll();

$wallets    = $db->query("SELECT * FROM wallets")->fetchAll();
$categories = $db->query("SELECT * FROM categories ORDER BY type, name")->fetchAll();

include 'includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="content-grid">
    <!-- Transaction List -->
    <div class="content-section">
        <div class="section-title">Riwayat Transaksi</div>
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>Belum ada transaksi.</p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Dompet</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                    <td><?= $tx['cat_icon'] ?> <?= htmlspecialchars($tx['cat_name']) ?></td>
                    <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($tx['wallet_name']) ?></td>
                    <td><span class="badge badge-<?= $tx['type'] ?>"><?= $tx['type'] === 'income' ? '📥 Masuk' : '📤 Keluar' ?></span></td>
                    <td class="amount-<?= $tx['type'] ?>">
                        <?= $tx['type'] === 'income' ? '+' : '-' ?>Rp <?= number_format($tx['amount'], 0, ',', '.') ?>
                    </td>
                    <td>
                        <form method="POST" data-confirm="Hapus transaksi ini?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Add Form -->
    <div class="content-section">
        <div class="section-title">Tambah Transaksi</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Tipe</label>
                <select name="type" required>
                    <option value="expense">📤 Pengeluaran</option>
                    <option value="income">📥 Pemasukan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Dompet</label>
                <select name="wallet_id" required>
                    <option value="">— Pilih Dompet —</option>
                    <?php foreach ($wallets as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?> (Rp <?= number_format($w['balance'], 0, ',', '.') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" required>
                    <option value="">— Pilih Kategori —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah (Rp)</label>
                <input type="number" name="amount" min="1" placeholder="0" required class="currency-input">
            </div>
            <div class="form-group">
                <label>Keterangan (opsional)</label>
                <input type="text" name="description" placeholder="Contoh: Makan siang">
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">➕ Tambah Transaksi</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
