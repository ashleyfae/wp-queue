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
            'status' => esc_html__('Status', 'wp-queue'),
            'arguments' => esc_html__('Arguments', 'wp-queue'),
            'scheduled_for' => esc_html__('Scheduled For', 'wp-queue'),
            'logs' => esc_html__('Logs', 'wp-queue'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'action' => ['action', true],
        ];
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'].'_id',
            $item->id
        );
    }

    public function column_default($item, $column_name)
    {
        if (! $item instanceof QueuedJob) {
            return '';
        }

        switch($column_name) {
            case 'action' :
                return esc_html($item->action);
            case 'status' :
                return esc_html($item->getStatusDisplayName());
            case 'arguments' :
                if ($item->arguments) {
                    return esc_html(json_encode($item->arguments));
                } else {
                    return '&ndash;';
                }
            case 'created_at' :
            case 'scheduled_for' :
                return esc_html(sprintf('%s UTC', $item->{$column_name}->format('Y-m-d H:I:s')));
            case 'logs' :
                $logs = [
                    sprintf(__('Created at %s UTC', 'wp-queue'), $item->created_at->format('Y-m-d H:i:s')),
                ];

                if ($item->started_at) {
                    $logs[] = sprintf(__('Started processing at %s UTC', 'wp-queue'), $item->started_at->format('Y-m-d H:i:s'));
                }
                if ($item->output) {
                    $logs[] = $item->output;
                }
                if ($item->completed_at) {
                    $logs[] = sprintf(__('Completed at %s UTC', 'wp-queue'), $item->completed_at->format('Y-m-d H:i:s'));
                }
                $logs = array_map(fn($log) => '<li>'.$log.'</li>', $logs);
                return '<ol>'.implode("\n", $logs).'</ol>';
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
