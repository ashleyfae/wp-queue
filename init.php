<?php
/**
 * Initialize
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

if (function_exists('add_action')) {
    \AshleyFae\WpQueue\WpQueue::instance()->boot();
}
