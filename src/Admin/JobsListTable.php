<?php
/**
 * JobsListTable.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Admin;

use AshleyFae\WpQueue\Database\Repositories\QueuedJobRepository;
use AshleyFae\WpQueue\Models\QueuedJob;
use WP_List_Table;

class JobsListTable extends WP_List_Table
{
    protected int $perPage = 30;

    public function __construct(protected QueuedJobRepository $jobRepository)
    {
        parent::__construct([
            'singular' => 'job',
            'plural' => 'jobs',
            'ajax' => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'action' => esc_html__('Action', 'wp-queue'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'action' => ['action', true],
        ];
    }

    public function column_cb(QueuedJob $object)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'].'_id',
            $object->id
        );
    }

    public function column_default(QueuedJob $job, $columnName)
    {
        switch($columnName) {
            case 'action' :
                return esc_html($job->action);
        }
    }

    public function prepare_items()
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $this->items = $this->getJobs();
        $totalJobs = $this->getJobCount();

        $this->set_pagination_args([
            'total_items' => $totalJobs,
            'per_page' => $this->perPage,
            'total_pages' => ceil($totalJobs / $this->perPage),
        ]);
    }

    protected function getJobs() : array
    {
        return $this->jobRepository->query();
    }

    protected function getJobCount() : int
    {
        return 0;
    }
}
