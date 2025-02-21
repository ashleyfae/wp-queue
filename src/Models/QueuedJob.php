<?php
/**
 * QueuedJob.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Models;

use DateTime;

class QueuedJob extends AbstractModel
{
    public int $id;
    public string $status = 'pending';
    public string $action;
    public ?array $arguments = null;
    public ?string $output = null;
    public ?DateTime $created_at;
    public DateTime $scheduled_for;
    public ?DateTime $started_at;
    public ?DateTime $completed_at;
    public ?DateTime $updated_at;

    protected array $casts = [
        'id' => 'int',
        'status' => 'string',
        'action' => 'string',
        'arguments' => 'array',
        'output' => 'string',
        'created_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $dbColumnNames = [
        'id',
        'status',
        'action',
        'arguments',
        'output',
        'created_at',
        'scheduled_for',
        'started_at',
        'completed_at',
        'updated_at',
    ];

    public function getStatusDisplayName() : string
    {
        return match($this->status) {
            'completed' => __('Completed', 'wp-queue'),
            'in_progress' => __('In Progress', 'wp-queue'),
            'failed' => __('Failed', 'wp-queue'),
            default => __('Pending', 'wp-queue')
        };
    }
}
