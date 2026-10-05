<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261005140927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Lab 3: restaurant schema (customers, restaurant_tables, menu_items, reservations, orders, order_items)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, email VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_items (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, category VARCHAR(100) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, order_id INT NOT NULL, menu_item_id INT NOT NULL, INDEX IDX_62809DB08D9F6D38 (order_id), INDEX IDX_62809DB09AB44FE0 (menu_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, ordered_at DATETIME NOT NULL, customer_id INT NOT NULL, restaurant_table_id INT DEFAULT NULL, INDEX IDX_E52FFDEE9395C3F3 (customer_id), INDEX IDX_E52FFDEECC5AE6B3 (restaurant_table_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, reserved_for DATETIME NOT NULL, guests_count INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, customer_id INT NOT NULL, restaurant_table_id INT NOT NULL, INDEX IDX_4DA2399395C3F3 (customer_id), INDEX IDX_4DA239CC5AE6B3 (restaurant_table_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE restaurant_tables (id INT AUTO_INCREMENT NOT NULL, table_number INT NOT NULL, seats INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB09AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEECC5AE6B3 FOREIGN KEY (restaurant_table_id) REFERENCES restaurant_tables (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA2399395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239CC5AE6B3 FOREIGN KEY (restaurant_table_id) REFERENCES restaurant_tables (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB09AB44FE0');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE9395C3F3');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEECC5AE6B3');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA2399395C3F3');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239CC5AE6B3');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE menu_items');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE reservations');
        $this->addSql('DROP TABLE restaurant_tables');
    }
}
