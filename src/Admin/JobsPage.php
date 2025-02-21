<?php
/**
 * JobsPage.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Admin;

class JobsPage
{
    public function __construct(
        protected JobsListTable $jobsListTable
    )
    {
    }

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Queued Jobs', 'wp-queue'); ?></h1>

            <form method="GET" action="">
                <?php $this->jobsListTable->display(); ?>
            </form>
        </div>
        <?php
    }
}
