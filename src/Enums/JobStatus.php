<?php
/**
 * JobStatus.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Enums;

class JobStatus
{
    public const Pending = 'pending';
    public const InProgress = 'in_progress';
    public const Complete = 'complete';
    public const Failed = 'failed';
}
