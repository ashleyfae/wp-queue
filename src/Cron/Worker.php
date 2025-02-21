<?php
/**
 * Worker.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Cron;

use AshleyFae\WpQueue\Database\Repositories\QueuedJobRepository;
use AshleyFae\WpQueue\Enums\JobStatus;
use AshleyFae\WpQueue\Models\QueuedJob;
use DateTime;

class Worker
{
    public function __construct(
        protected QueuedJobRepository $jobRepository
    )
    {
    }

    public function process(): bool
    {
        $nextJob = $this->jobRepository->getNextReadyJob();
        if (! $nextJob) {
            error_log('No jobs found');
            return false;
        }

        error_log(sprintf('Processing job #%d', $nextJob->id));
        try {
            $this->startJob($nextJob);

            do_action($nextJob->action, $nextJob->arguments);

            $this->completeJob($nextJob);
        } catch(\Exception $e) {
            $this->handleFailure($nextJob, $e);
        }
    }

    protected function startJob(QueuedJob $job): void
    {
        $job->status = JobStatus::InProgress;
        $job->started_at = new DateTime('now');

        $this->jobRepository->save($job);
    }

    protected function completeJob(QueuedJob $job): void
    {
        $job->status = JobStatus::Complete;
        $job->completed_at = new DateTime('now');

        $this->jobRepository->save($job);
    }

    protected function handleFailure(QueuedJob $job, \Exception $exception): void
    {
        try {
            $job->status = JobStatus::Failed;
            $job->completed_at = new DateTime('now');
            $job->output = json_encode([
                'error' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ], JSON_PRETTY_PRINT);

            $this->jobRepository->save($job);
        } catch(\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
