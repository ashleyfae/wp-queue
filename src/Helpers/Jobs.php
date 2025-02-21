<?php
/**
 * Jobs.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Helpers;

use AshleyFae\WpQueue\Database\Repositories\QueuedJobRepository;
use AshleyFae\WpQueue\Models\QueuedJob;
use DateTime;

class Jobs
{
    public function __construct(
        protected QueuedJobRepository $queuedJobRepository
    ) {

    }

    /**
     * Schedules a job to run.
     *
     * @param  string  $action Action hook to execute when the job runs.
     * @param  array|null  $arguments Arguments to use in the hook callback.
     * @param  DateTime|null  $scheduledFor When the job should start.
     *
     * @return int ID of the created job.
     */
    public function scheduleJob(string $action, ?array $arguments, DateTime $scheduledFor = null) : int
    {
        $job = new QueuedJob();
        $job->action = $action;
        $job->arguments = $arguments;
        $job->scheduled_for = $scheduledFor ?: new DateTime('now');

        $this->queuedJobRepository->save($job);

        return $job->id;
    }
}
