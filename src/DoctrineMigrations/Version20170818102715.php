<?php
/**
 * Alfa Capital Holdings (Cyprus) Limited.
 *
 * The following source code is PROPRIETARY AND CONFIDENTIAL. Use of this source code
 * is governed by the Alfa Capital Holdings (Cyprus) Ltd. Non-Disclosure Agreement
 * previously entered between you and Alfa Capital Holdings (Cyprus) Limited.
 *
 * By accessing, using, copying, modifying or distributing this software, you acknowledge
 * that you have been informed of your obligations under the Agreement and agree
 * to abide by those obligations.
 *
 * @author Evgeny Efimov <evgeny.efimov@alfaforex.com>
 */

namespace Faecie\KeyValueBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20170315102715
 */
class Version20170818102715 extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
