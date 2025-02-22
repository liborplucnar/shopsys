<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240926162253 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE inquiries (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                product_id INT DEFAULT NULL,
                product_catnum VARCHAR(100) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                telephone VARCHAR(30) NOT NULL,
                company_name VARCHAR(100) DEFAULT NULL,
                company_number VARCHAR(50) DEFAULT NULL,
                company_tax_number VARCHAR(50) DEFAULT NULL,
                note TEXT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                customer_user_id INT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_1CCE4D54584665A ON inquiries (product_id)');
        $this->sql('
            ALTER TABLE
                inquiries
            ADD
                CONSTRAINT FK_1CCE4D54584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('COMMENT ON COLUMN inquiries.created_at IS \'(DC2Type:datetime_immutable)\'');

        $this->sql('
            ALTER TABLE
                inquiries
            ADD
                CONSTRAINT FK_1CCE4D5BBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_1CCE4D5BBB3772B ON inquiries (customer_user_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
