<?php
/**
 * Lock.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Cron;

class Lock
{
    protected const LOCK_OPTION_NAME = 'ag_wp_queue_lock';
    protected const LOCK_DURATION_SECONDS = 300;

    protected function isLocked() : bool
    {
        $lock = get_option(static::LOCK_OPTION_NAME);

        if (! $lock || ! is_numeric($lock)) {
            return false;
        }

        return (int) $lock > strtotime('now');
    }

    /**
     * @return bool true if a lock was obtained, false if not
     */
    public function getLock(): bool
    {
        if ($this->isLocked()) {
            return false;
        }

        return (bool) update_option(static::LOCK_OPTION_NAME, strtotime('now') + static::LOCK_DURATION_SECONDS);
    }

    public function release(): void
    {
        delete_option(static::LOCK_OPTION_NAME);
    }
}
