<?php
/**
 * QueueTable.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Database\Tables;

use Ashleyfae\WPDB\Tables\AbstractTable;

class QueuedJobTable extends AbstractTable
{
    public function getPackageTablePrefix(): string
    {
        return 'af_';
    }

    public function getName(): string
    {
        return 'queued_jobs';
    }

    public function getVersion(): int
    {
        return strtotime('2025-02-21 12:19:00');
    }

    public function getSchema(): string
    {
        return "
        id bigint(20) unsigned not null auto_increment primary key,
        status varchar(64) not null default 'pending',
        action varchar(191) not null,
        arguments longtext default null,
        output longtext default null,
        created_at datetime not null default CURRENT_TIMESTAMP,
        scheduled_for datetime not null default CURRENT_TIMESTAMP,
        started_at datetime default null,
        completed_at datetime default null,
        updated_at datetime not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
        index status_scheduled_for (status, scheduled_for),
        index action (action)
        ";
    }
}
