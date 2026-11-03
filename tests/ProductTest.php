<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        initDB($this->pdo);
    }

    public function testDatabaseSeedsTenProducts(): void
    {
        $count = (int)$this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $this->assertSame(10, $count);
    }

    public function testCreateProduct(): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO products (name, description, price, quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test Product', 'A test item', 12.5, 3]);

        $row = $this->pdo->query("SELECT * FROM products WHERE name = 'Test Product'")->fetch();
        $this->assertNotFalse($row);
        $this->assertSame('A test item', $row['description']);
        $this->assertEquals(12.5, (float)$row['price']);
        $this->assertSame(3, (int)$row['quantity']);
    }

    public function testUpdateProduct(): void
    {
        $this->pdo->exec("INSERT INTO products (name, description, price, quantity) VALUES ('Old', 'old', 1.0, 1)");
        $id = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("UPDATE products SET name=?, price=? WHERE id=?");
        $stmt->execute(['New Name', 99.99, $id]);

        $row = $this->pdo->query("SELECT * FROM products WHERE id = $id")->fetch();
        $this->assertSame('New Name', $row['name']);
        $this->assertEquals(99.99, (float)$row['price']);
    }

    public function testDeleteProduct(): void
    {
        $this->pdo->exec("INSERT INTO products (name, description, price, quantity) VALUES ('Temp', 'temp', 1.0, 1)");
        $id = (int)$this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        $row = $this->pdo->query("SELECT * FROM products WHERE id = $id")->fetch();
        $this->assertFalse($row);
    }
}