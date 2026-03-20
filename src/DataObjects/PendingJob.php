<?php
/**
 * PendingJob.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\DataObjects;

use Ashleyfae\WPDB\Exceptions\DatabaseQueryException;
use AshleyFae\WpQueue\Helpers\Jobs;
use AshleyFae\WpQueue\WpQueue;
use DateTime;

class PendingJob
{
    protected Jobs $jobs;

    /**
     * @param  string  $action  Action hook to execute when the job runs.
     * @param  array|null  $arguments  Arguments to use in the hook callback.
     * @param  DateTime|null  $scheduledFor  When the job should start.
     */
    public function __construct(
        public string $action,
        public ?array $arguments = null,
        public ?DateTime $scheduledFor = null
    ) {
        $this->jobs = WpQueue::instance()->get(Jobs::class);
    }

    /**
     * @throws DatabaseQueryException
     */
    public function schedule() : int
    {
        return $this->jobs->scheduleJob($this);
    }

    /**
     * @throws DatabaseQueryException
     */
    public function isScheduled(): bool
    {
        return $this->jobs->isScheduled($this);
    }

    /**
     * @throws DatabaseQueryException
     */
    public function hasCompleted(): bool
    {
        return $this->jobs->hasCompleted($this);
    }

    /**
     * @throws DatabaseQueryException
     */
    public function exists(): bool
    {
        return $this->jobs->exists($this);
    }
}
