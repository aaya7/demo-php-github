<?php
function getDB(): PDO
{
    $dbPath = __DIR__ . '/data/products.db';
    $isNew = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($isNew) {
        initDB($pdo);
    }

    return $pdo;
}

function initDB(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity) VALUES (?, ?, ?, ?)");
        $samples = [
            ['Laptop Pro 15"',      'High-performance laptop',  1499.99, 10],
            ['Wireless Mouse',      'Ergonomic wireless mouse',  29.99,  50],
            ['Mechanical Keyboard', 'RGB mechanical keyboard',   89.99,  30],
            ['4K Monitor',          '27-inch 4K UHD monitor',   399.00,  15],
            ['USB-C Hub',           'Multi-port USB-C hub',      49.99,  40],
            ['Noise-Cancel Headphones','Over-ear ANC headphones',199.99,  20],
            ['Webcam HD',           '1080p HD webcam',           59.99,  25],
            ['External SSD 1TB',    'Portable 1TB SSD',         129.99,  18],
            ['Bluetooth Speaker',   'Waterproof BT speaker',     79.99,  22],
            ['Laptop Stand',        'Aluminum laptop stand',     34.99,  35],
        ];
        foreach ($samples as $p) {
            $stmt->execute($p);
        }
    }
}