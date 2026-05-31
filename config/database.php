<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbPath = __DIR__ . '/../data/bukutabungan.db';
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0755, true);
        }
        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->createTables();
        $this->seedData();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function createTables() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                icon TEXT NOT NULL DEFAULT '📦',
                type TEXT NOT NULL DEFAULT 'expense',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS wallets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL DEFAULT 'regular',
                balance REAL NOT NULL DEFAULT 0,
                color TEXT NOT NULL DEFAULT '#6366f1',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                wallet_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                type TEXT NOT NULL,
                description TEXT,
                transaction_date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (wallet_id) REFERENCES wallets(id),
                FOREIGN KEY (category_id) REFERENCES categories(id)
            );
        ");
    }

    private function seedData() {
        $count = $this->pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($count > 0) return;

        $categories = [
            ['Makanan & Minuman', '🍔', 'expense'],
            ['Transportasi', '🚗', 'expense'],
            ['Listrik', '⚡', 'expense'],
            ['Air', '💧', 'expense'],
            ['Gas', '🔥', 'expense'],
            ['Internet', '📶', 'expense'],
            ['Kesehatan', '🏥', 'expense'],
            ['Belanja', '🛍️', 'expense'],
            ['Hiburan', '🎮', 'expense'],
            ['Pendidikan', '📚', 'expense'],
            ['Gaji', '💰', 'income'],
            ['Bonus', '🎁', 'income'],
            ['Investasi', '📈', 'income'],
            ['Lainnya', '📦', 'expense'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO categories (name, icon, type) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }

        // Seed default wallet
        $this->pdo->exec("INSERT INTO wallets (name, type, balance, color) VALUES ('Dompet Utama', 'regular', 0, '#6366f1')");
    }
}
?>
