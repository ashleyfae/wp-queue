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

    public function __construct(
        protected Lock $lock
    )
    {
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

        // @TODO process
        error_log('processing queue');

        $this->lock->release();
    }
}
