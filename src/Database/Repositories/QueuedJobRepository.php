<?php
/**
 * QueuedJobRepository.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Database\Repositories;

use Ashleyfae\WPDB\DB;
use AshleyFae\WpQueue\Database\Tables\QueuedJobTable;
use AshleyFae\WpQueue\Models\QueuedJob;
use DateTime;

class QueuedJobRepository
{
    public function __construct(protected QueuedJobTable $table)
    {

    }

    public function get(int $id) : QueuedJob
    {
        $row = DB::get_row(
            DB::prepare(
                "SELECT * FROM {$this->table->getTableName()} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        return new QueuedJob($row);
    }

    public function save(QueuedJob $queuedJob) : QueuedJob
    {
        if (! empty($queuedJob->id)) {
            return $this->update($queuedJob);
        } else {
            return $this->create($queuedJob);
        }
    }

    protected function update(QueuedJob $queuedJob) : QueuedJob
    {
        $queuedJob->updated_at = new DateTime('now');

        DB::update(
            table: $this->table->getTableName(),
            data: $queuedJob->toDbArray(),
            where: ['id' => $queuedJob->id],
            format: '%s',
            where_format: '%d'
        );

        return $queuedJob;
    }

    protected function create(QueuedJob $queuedJob) : QueuedJob
    {
        DB::insert(
            table: $this->table->getTableName(),
            data: $queuedJob->toDbArray(),
            format: '%s'
        );

        if ($id = DB::lastInsertId()) {
            $queuedJob->setId($id);
        }

        return $queuedJob;
    }

    /**
     * Gets an array of jobs that are ready to be processed.
     *
     * @param  int  $number maximum number of results to retrieve
     *
     * @return QueuedJob[]
     */
    public function getReadyJobs(int $number = 10) : array
    {
        $rows = DB::get_results(
            DB::prepare(
                "SELECT * FROM {$this->table->getTableName()}
                WHERE status = 'pending'
                AND scheduled_for >= %s
                LIMIT %d",
                (new DateTime('now'))->format('Y-m-d H:i:s'),
                $number
            ),
            ARRAY_A
        );

        return array_map(
            fn(array $row) => new QueuedJob($row),
            $rows
        );
    }
}
