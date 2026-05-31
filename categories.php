<?php require_once __DIR__ . '/config/base_url.php'; ?>
<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();
$pageTitle = 'Kategori';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '📦');
        if (!$name) {
            $error = 'Nama kategori wajib diisi.';
        } else {
            $db->prepare("INSERT INTO categories (name, icon, type) VALUES (?, ?, 'expense')")->execute([$name, $icon]);
            $success = 'Kategori ditambahkan!';
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // Check if in use
        $used = $db->prepare("SELECT COUNT(*) FROM transactions WHERE category_id=?");
        $used->execute([$id]);
        if ($used->fetchColumn() > 0) {
            $error = 'Kategori sedang digunakan dan tidak bisa dihapus.';
        } else {
            $db->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
            $success = 'Kategori dihapus.';
        }
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
include 'includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="content-grid">
    <div class="content-section">
        <div class="section-title">Daftar Kategori</div>
        <table class="data-table">
            <thead>
                <tr><th>Ikon</th><th>Nama</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="font-size:22px"><?= $cat['icon'] ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    
                    <td>
                        <form method="POST" data-confirm="Hapus kategori ini?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="content-section">
        <div class="section-title">Tambah Kategori</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="name" placeholder="Contoh: Pulsa, Parkir" required>
            </div>
            <div class="form-group">
                <label>Ikon (emoji)</label>
                <input type="text" name="icon" placeholder="📦" value="📦" style="font-size:18px;">
            </div>
            <button type="submit" class="btn btn-primary btn-full">➕ Tambah Kategori</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
