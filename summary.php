<?php require_once __DIR__ . '/config/base_url.php'; ?>
<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();
$pageTitle = 'Ringkasan';

// ── DATE PARAMS ──
$today     = $_GET['day']   ?? date('Y-m-d');
$month     = $_GET['month'] ?? date('Y-m');
$year      = $_GET['year']  ?? date('Y');
$catFilter = $_GET['cat']   ?? '';
$activeTab = $_GET['tab']   ?? 'daily';

// ── CATEGORIES for filter ──
$allCats = $db->query("SELECT id, name, icon FROM categories ORDER BY name")->fetchAll();

// ── DAILY ──
$dailyIncome  = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND transaction_date=?");
$dailyIncome->execute([$today]);
$dailyIncome  = $dailyIncome->fetchColumn();

$dailyExpense = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND transaction_date=?");
$dailyExpense->execute([$today]);
$dailyExpense = $dailyExpense->fetchColumn();

$dailySql = "SELECT t.*, c.name as cat_name, c.icon as cat_icon, w.name as wallet_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             JOIN wallets w ON w.id = t.wallet_id
             WHERE t.transaction_date = ?";
$dailyParams = [$today];
if ($catFilter) { $dailySql .= " AND t.category_id = ?"; $dailyParams[] = $catFilter; }
$dailySql .= " ORDER BY t.created_at DESC";
$dailyTx = $db->prepare($dailySql);
$dailyTx->execute($dailyParams);
$dailyTx = $dailyTx->fetchAll();

// ── MONTHLY ──
$monthIncome  = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND strftime('%Y-%m',transaction_date)=?");
$monthIncome->execute([$month]); $monthIncome = $monthIncome->fetchColumn();

$monthExpense = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND strftime('%Y-%m',transaction_date)=?");
$monthExpense->execute([$month]); $monthExpense = $monthExpense->fetchColumn();

$monthByCatSql = "SELECT c.id, c.name, c.icon, COALESCE(SUM(t.amount),0) as total, t.type
                  FROM transactions t
                  JOIN categories c ON c.id = t.category_id
                  WHERE strftime('%Y-%m',t.transaction_date)=?";
$mbcParams = [$month];
if ($catFilter) { $monthByCatSql .= " AND t.category_id = ?"; $mbcParams[] = $catFilter; }
$monthByCatSql .= " GROUP BY c.id ORDER BY total DESC";
$monthByCat = $db->prepare($monthByCatSql); $monthByCat->execute($mbcParams); $monthByCat = $monthByCat->fetchAll();

$monthByDaySql = "SELECT transaction_date,
                         SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
                         SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
                  FROM transactions
                  WHERE strftime('%Y-%m',transaction_date)=?";
$mbdParams = [$month];
if ($catFilter) { $monthByDaySql .= " AND category_id = ?"; $mbdParams[] = $catFilter; }
$monthByDaySql .= " GROUP BY transaction_date ORDER BY transaction_date";
$monthByDay = $db->prepare($monthByDaySql); $monthByDay->execute($mbdParams); $monthByDay = $monthByDay->fetchAll();

// ── YEARLY ──
$yearIncome  = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND strftime('%Y',transaction_date)=?");
$yearIncome->execute([$year]); $yearIncome = $yearIncome->fetchColumn();

$yearExpense = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND strftime('%Y',transaction_date)=?");
$yearExpense->execute([$year]); $yearExpense = $yearExpense->fetchColumn();

$yearByMonthSql = "SELECT strftime('%m',transaction_date) as m,
                          SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
                          SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
                   FROM transactions
                   WHERE strftime('%Y',transaction_date)=?";
$ybmParams = [$year];
if ($catFilter) { $yearByMonthSql .= " AND category_id = ?"; $ybmParams[] = $catFilter; }
$yearByMonthSql .= " GROUP BY m ORDER BY m";
$yearByMonth = $db->prepare($yearByMonthSql); $yearByMonth->execute($ybmParams); $yearByMonth = $yearByMonth->fetchAll();

$monthNames = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
               '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
$monthNamesShort = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
                    '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];

// ── NAV HELPERS ──
function navUrl($params) {
    $base = array_merge($_GET, $params);
    return '/bukutabungan/summary.php?' . http_build_query($base);
}
$prevDay   = date('Y-m-d', strtotime($today . ' -1 day'));
$nextDay   = date('Y-m-d', strtotime($today . ' +1 day'));
$prevMonth = date('Y-m', strtotime($month . '-01 -1 month'));
$nextMonth = date('Y-m', strtotime($month . '-01 +1 month'));
$prevYear  = $year - 1;
$nextYear  = $year + 1;

// Label display
$dayLabel   = date('d F Y', strtotime($today));
$monthLabel = $monthNames[date('m', strtotime($month . '-01'))] . ' ' . date('Y', strtotime($month . '-01'));
$yearLabel  = $year;

include 'includes/header.php';
?>

<div class="summary-tabs">
    <button class="tab-btn <?= $activeTab==='daily'?'active':'' ?>" data-tab="tab-daily" onclick="setTab('daily')">📅 Harian</button>
    <button class="tab-btn <?= $activeTab==='monthly'?'active':'' ?>" data-tab="tab-monthly" onclick="setTab('monthly')">🗓 Bulanan</button>
    <button class="tab-btn <?= $activeTab==='yearly'?'active':'' ?>" data-tab="tab-yearly" onclick="setTab('yearly')">📆 Tahunan</button>
</div>

<!-- ── CATEGORY FILTER ── -->
<div class="summary-filter">
    <select
        class="summary-category-select"
        onchange="window.location=this.value">

        <option value="<?= navUrl(['cat'=>'']) ?>">
            ✨ Semua Kategori
        </option>

        <?php foreach ($allCats as $cat): ?>
        <option
            value="<?= navUrl(['cat'=>$cat['id']]) ?>"
            <?= $catFilter==$cat['id'] ? 'selected' : '' ?>>
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
        </option>
        <?php endforeach; ?>

    </select>
</div>

<!-- ── DAILY ── -->
<div class="summary-block <?= $activeTab==='daily'?'active':'' ?>" id="tab-daily">
    <div class="date-nav">
        <a href="<?= navUrl(['day'=>$prevDay,'tab'=>'daily']) ?>" class="date-nav-btn">‹</a>
        <label class="date-nav-label">📅 <input type="date" value="<?= $today ?>" onchange="window.location='<?= navUrl(['tab'=>'daily']) ?>&day='+this.value" style="border:none;background:transparent;font:inherit;cursor:pointer;"></label>
        <a href="<?= navUrl(['day'=>$nextDay,'tab'=>'daily']) ?>" class="date-nav-btn">›</a>
    </div>
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="stat-card green">
            <div class="stat-label">📥 Pemasukan</div>
            <div class="stat-value">Rp <?= number_format($dailyIncome, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">📤 Pengeluaran</div>
            <div class="stat-value">Rp <?= number_format($dailyExpense, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card accent">
            <div class="stat-label">📊 Net Hari Ini</div>
            <div class="stat-value" style="color:<?= ($dailyIncome-$dailyExpense)>=0?'#059669':'var(--red)' ?>">
                Rp <?= number_format($dailyIncome - $dailyExpense, 0, ',', '.') ?>
            </div>
        </div>
    </div>
    <div class="content-section">
        <div class="section-title">Transaksi — <?= $dayLabel ?></div>
        <?php if (empty($dailyTx)): ?>
            <div class="empty-state"><div class="empty-icon">🌸</div><p>Belum ada transaksi di hari ini.</p></div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Waktu</th><th>Kategori</th><th>Keterangan</th><th>Dompet</th><th>Jumlah</th></tr></thead>
            <tbody>
                <?php foreach ($dailyTx as $tx): ?>
                <tr>
                    <td><?= date('H:i', strtotime($tx['created_at'])) ?></td>
                    <td><?= $tx['cat_icon'] ?> <?= htmlspecialchars($tx['cat_name']) ?></td>
                    <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($tx['wallet_name']) ?></td>
                    <td class="amount-<?= $tx['type'] ?>"><?= $tx['type']==='income'?'+':'-' ?>Rp <?= number_format($tx['amount'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── MONTHLY ── -->
<div class="summary-block <?= $activeTab==='monthly'?'active':'' ?>" id="tab-monthly">
    <div class="date-nav">
        <a href="<?= navUrl(['month'=>$prevMonth,'tab'=>'monthly']) ?>" class="date-nav-btn">‹</a>
        <label class="date-nav-label">
    🗓

    <input
        type="month"
        value="<?= $month ?>"
        onchange="window.location='<?= navUrl(['tab'=>'monthly']) ?>&month='+this.value"
        class="pretty-month-picker">
</label>
        <a href="<?= navUrl(['month'=>$nextMonth,'tab'=>'monthly']) ?>" class="date-nav-btn">›</a>
    </div>
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="stat-card green">
            <div class="stat-label">📥 Pemasukan</div>
            <div class="stat-value">Rp <?= number_format($monthIncome,0,',','.') ?></div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">📤 Pengeluaran</div>
            <div class="stat-value">Rp <?= number_format($monthExpense,0,',','.') ?></div>
        </div>
        <div class="stat-card accent">
            <div class="stat-label">📊 Net</div>
            <div class="stat-value" style="color:<?= ($monthIncome-$monthExpense)>=0?'#059669':'var(--red)' ?>">
                Rp <?= number_format($monthIncome-$monthExpense,0,',','.') ?>
            </div>
        </div>
    </div>
    <div class="content-grid">
        <div class="content-section">
            <div class="section-title">Per Hari</div>
            <?php if (empty($monthByDay)): ?>
                <div class="empty-state"><div class="empty-icon">🌺</div><p>Belum ada data.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Tanggal</th><th>Pemasukan</th><th>Pengeluaran</th><th>Net</th></tr></thead>
                <tbody>
                    <?php foreach ($monthByDay as $row): $net = $row['income']-$row['expense']; ?>
                    <tr>
                        <td><?= date('d F', strtotime($row['transaction_date'])) ?></td>
                        <td class="amount-income">+Rp <?= number_format($row['income'],0,',','.') ?></td>
                        <td class="amount-expense">-Rp <?= number_format($row['expense'],0,',','.') ?></td>
                        <td style="color:<?= $net>=0?'#059669':'var(--red)' ?>; font-family:'Quicksand',sans-serif; font-weight:700">
                            <?= $net>=0?'+':'' ?>Rp <?= number_format($net,0,',','.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <div class="content-section">
            <div class="section-title">Per Kategori</div>
            <?php if (empty($monthByCat)): ?>
                <div class="empty-state"><div class="empty-icon">🌺</div><p>Belum ada data.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Kategori</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($monthByCat as $row): ?>
                    <tr>
                        <td><?= $row['icon'] ?> <?= htmlspecialchars($row['name']) ?></td>
                        <td class="amount-<?= $row['type'] ?>">Rp <?= number_format($row['total'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── YEARLY ── -->
<div class="summary-block <?= $activeTab==='yearly'?'active':'' ?>" id="tab-yearly">
    <div class="date-nav">
        <a href="<?= navUrl(['year'=>$prevYear,'tab'=>'yearly']) ?>" class="date-nav-btn">‹</a>
        <label class="date-nav-label">📆 <input type="number" min="2000" max="2100" value="<?= $year ?>" onchange="window.location='<?= navUrl(['tab'=>'yearly']) ?>&year='+this.value" style="width:90px;border:none;background:transparent;font:inherit;text-align:center;"></label>
        <a href="<?= navUrl(['year'=>$nextYear,'tab'=>'yearly']) ?>" class="date-nav-btn">›</a>
    </div>
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="stat-card green">
            <div class="stat-label">📥 Pemasukan <?= $year ?></div>
            <div class="stat-value">Rp <?= number_format($yearIncome,0,',','.') ?></div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">📤 Pengeluaran <?= $year ?></div>
            <div class="stat-value">Rp <?= number_format($yearExpense,0,',','.') ?></div>
        </div>
        <div class="stat-card accent">
            <div class="stat-label">📊 Net <?= $year ?></div>
            <div class="stat-value" style="color:<?= ($yearIncome-$yearExpense)>=0?'#059669':'var(--red)' ?>">
                Rp <?= number_format($yearIncome-$yearExpense,0,',','.') ?>
            </div>
        </div>
    </div>
    <div class="content-section">
        <div class="section-title">Per Bulan — <?= $year ?></div>
        <?php if (empty($yearByMonth)): ?>
            <div class="empty-state"><div class="empty-icon">🌸</div><p>Belum ada data tahun ini.</p></div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Bulan</th><th>Pemasukan</th><th>Pengeluaran</th><th>Net</th></tr></thead>
            <tbody>
                <?php foreach ($yearByMonth as $row): $net = $row['income']-$row['expense']; ?>
                <tr>
                    <td><?= $monthNamesShort[$row['m']] ?? $row['m'] ?></td>
                    <td class="amount-income">+Rp <?= number_format($row['income'],0,',','.') ?></td>
                    <td class="amount-expense">-Rp <?= number_format($row['expense'],0,',','.') ?></td>
                    <td style="color:<?= $net>=0?'#059669':'var(--red)' ?>; font-family:'Quicksand',sans-serif; font-weight:700">
                        <?= $net>=0?'+':'' ?>Rp <?= number_format($net,0,',','.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function setTab(tab) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.location = url;
}
</script>

<?php include 'includes/footer.php'; ?>
