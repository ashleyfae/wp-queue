<?php
/**
 * CronHandler.php
 *
 * @link https://github.com/deliciousbrains/wp-queue/blob/master/src/WP_Queue/Cron.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Cron;

use AshleyFae\WpQueue\Components\ComponentInterface;

class CronHandler implements ComponentInterface
{
    protected string $cronScheduleId = 'every_minute';
    protected string $cronRunnerActionName = 'ag_wp_queue_process_jobs';
    protected int $startedAt;

    public function __construct(
        protected Lock $lock,
        protected Worker $worker
    ) {
    }

    public function boot(): void
    {
        add_filter('cron_schedules', [$this, 'modifyCronSchedule']);
        add_action($this->cronRunnerActionName, [$this, 'processQueue']);

        if (! wp_next_scheduled($this->cronRunnerActionName)) {
            // Schedule health check
            wp_schedule_event(time(), $this->cronScheduleId, $this->cronRunnerActionName);
        }
    }

    public function modifyCronSchedule($schedules)
    {
        if (is_array($schedules)) {
            $schedules[$this->cronScheduleId] = [
                'interval' => MINUTE_IN_SECONDS,
                'display'  => __('Every Minute', 'wp-queue'),
            ];
        }

        return $schedules;
    }

    public function processQueue(): void
    {
        if (! $this->lock->getLock()) {
            return;
        }

        $this->startedAt = time();

        while (! $this->timeExceeded() && ! $this->memoryExceeded()) {
            if (! $this->worker->process()) {
                // no more jobs
                break;
            }
        }

        $this->lock->release();
    }

    protected function timeExceeded(): bool
    {
        $shouldFinishAt = $this->startedAt + 20; // max 20 seconds

        return time() >= $shouldFinishAt;
    }

    protected function memoryExceeded(): bool
    {
        $memoryLimit   = $this->getMemoryLimit() * 0.8; // 80% of max memory
        $currentMemory = memory_get_usage(true);

        return $currentMemory >= $memoryLimit;
    }

    protected function getMemoryLimit(): int
    {
        if (function_exists('ini_get')) {
            $memoryLimit = ini_get('memory_limit');
        } else {
            $memoryLimit = '256M';
        }

        if (! $memoryLimit || -1 == $memoryLimit) {
            // Unlimited, set to 1GB
            $memoryLimit = '1000M';
        }

        return wp_convert_hr_to_bytes($memoryLimit);
    }
}
