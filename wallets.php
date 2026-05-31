<?php require_once __DIR__ . '/config/base_url.php'; ?>
<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();
$pageTitle = 'Dompet';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name    = trim($_POST['name'] ?? '');
        $type    = $_POST['type'] ?? 'regular';
        $balance = (float)str_replace(['.', ','], ['', '.'], $_POST['balance'] ?? 0);
        $color   = $_POST['color'] ?? '#6366f1';

        if (!$name) {
            $error = 'Nama dompet wajib diisi.';
        } else {
            $db->prepare("INSERT INTO wallets (name, type, balance, color) VALUES (?,?,?,?)")
               ->execute([$name, $type, $balance, $color]);
            $success = 'Dompet berhasil ditambahkan!';
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM wallets WHERE id = ?")->execute([$id]);
        $success = 'Dompet dihapus.';
    }

    if ($action === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6366f1';
        $type  = $_POST['type'] ?? 'regular';
        if ($name) {
            $db->prepare("UPDATE wallets SET name=?, type=?, color=? WHERE id=?")->execute([$name, $type, $color, $id]);
            $success = 'Dompet diperbarui.';
        }
    }
}

$wallets = $db->query("SELECT * FROM wallets ORDER BY created_at DESC")->fetchAll();
include 'includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="content-grid">
    <!-- Wallet List -->
    <div class="content-section">
        <div class="section-title">Daftar Dompet</div>
        <?php if (empty($wallets)): ?>
            <div class="empty-state"><div class="empty-icon">👛</div><p>Belum ada dompet.</p></div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Nama</th><th>Tipe</th><th>Saldo</th><th>Warna</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($wallets as $w): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($w['name']) ?></strong></td>
                    <td><?= $w['type'] === 'savings' ? '🏦 Tabungan' : '👛 Reguler' ?></td>
                    <td class="<?= $w['balance'] >= 0 ? 'amount-income' : 'amount-expense' ?>">
                        Rp <?= number_format($w['balance'], 0, ',', '.') ?>
                    </td>
                    <td><span class="color-dot" style="background:<?= htmlspecialchars($w['color']) ?>; width:20px;height:20px;border-radius:4px;display:inline-block;"></span></td>
                    <td style="display:flex;gap:6px;">
                        <button class="btn btn-sm" style="background:var(--bg3);color:var(--text2)"
                            onclick="openEdit(<?= htmlspecialchars(json_encode($w)) ?>)">✏️</button>
                        <form method="POST" data-confirm="Hapus dompet ini? Saldo akan hilang.">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $w['id'] ?>">
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
    <div>
        <div class="content-section" style="margin-bottom:16px;">
            <div class="section-title">Tambah Dompet</div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Nama Dompet</label>
                    <input type="text" name="name" placeholder="Contoh: BCA, OVO, Tunai" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="type">
                        <option value="regular">👛 Reguler</option>
                        <option value="savings">🏦 Tabungan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Saldo Awal (Rp)</label>
                    <input type="number" name="balance" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>Warna</label>
                    <input type="color" name="color" value="#6366f1" style="height:40px;cursor:pointer;">
                </div>
                <button type="submit" class="btn btn-primary btn-full">➕ Tambah Dompet</button>
            </form>
        </div>

        <!-- Edit modal inline -->
        <div class="content-section" id="editBox" style="display:none;">
            <div class="section-title">Edit Dompet</div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="type" id="edit_type">
                        <option value="regular">👛 Reguler</option>
                        <option value="savings">🏦 Tabungan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Warna</label>
                    <input type="color" name="color" id="edit_color" style="height:40px;cursor:pointer;">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1">💾 Simpan</button>
                    <button type="button" class="btn" style="background:var(--bg3);color:var(--text2)" onclick="document.getElementById('editBox').style.display='none'">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEdit(w) {
    document.getElementById('edit_id').value = w.id;
    document.getElementById('edit_name').value = w.name;
    document.getElementById('edit_type').value = w.type;
    document.getElementById('edit_color').value = w.color;
    document.getElementById('editBox').style.display = 'block';
    document.getElementById('editBox').scrollIntoView({behavior:'smooth'});
}
</script>

<?php include 'includes/footer.php'; ?>
