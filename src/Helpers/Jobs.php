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
use AshleyFae\WpQueue\DataObjects\PendingJob;
use AshleyFae\WpQueue\Enums\JobStatus;
use AshleyFae\WpQueue\JobQueryBuilder;
use AshleyFae\WpQueue\Models\QueuedJob;
use DateTime;

class Jobs
{
    public function __construct(
        protected QueuedJobRepository $queuedJobRepository,
        protected JobQueryBuilder $jobQueryBuilder
    ) {

    }

    /**
     * Schedules a job to run.
     *
     * @return int ID of the created job.
     */
    public function scheduleJob(PendingJob $pendingJob): int
    {
        $job                = new QueuedJob();
        $job->action        = $pendingJob->action;
        $job->arguments     = $pendingJob->arguments;
        $job->scheduled_for = $pendingJob->scheduledFor ?: new DateTime('now');

        $this->queuedJobRepository->save($job);

        return $job->id;
    }

    /**
     * Checks if the provided job is _scheduled_ (exists with status {@see JobStatus::Pending})
     * Fields checked: action name
     */
    public function isScheduled(PendingJob $pendingJob): bool
    {
        $results = $this->queuedJobRepository->query(
            $this->jobQueryBuilder->query()
                ->setAction($pendingJob->action)
                ->setStatusesIn([JobStatus::Pending])
                ->setLimit(1)
        );

        return ! empty($results);
    }

    /**
     * Checks if the provided job is _completed_ (exists with status {@see JobStatus::Complete})
     * Fields checked: action name
     */
    public function hasCompleted(PendingJob $pendingJob): bool
    {
        $results = $this->queuedJobRepository->query(
            $this->jobQueryBuilder->query()
                ->setAction($pendingJob->action)
                ->setStatusesIn([JobStatus::Complete])
                ->setLimit(1)
        );

        return ! empty($results);
    }

    /**
     * Checks if the provided job exists with any status.
     * Fields checked: action name
     */
    public function exists(PendingJob $pendingJob): bool
    {
        $results = $this->queuedJobRepository->query(
            $this->jobQueryBuilder->query()
                ->setAction($pendingJob->action)
                ->setLimit(1)
        );

        return ! empty($results);
    }
}
