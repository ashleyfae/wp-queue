<?php
/**
 * JobQueryBuilder.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue;

use Ashleyfae\WPDB\DB;
use AshleyFae\WpQueue\Traits\Conditionable;

class JobQueryBuilder
{
    use Conditionable;

    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $clauses = [];

    public function query(): static
    {
        $this->limit = null;
        $this->offset = null;
        $this->clauses = [];

        return $this;
    }

    public function setLimit(int $limit) : static
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset) : static
    {
        $this->offset = max($offset, 0);

        return $this;
    }

    public function setStatusesIn(array $statuses): static
    {
        $statusPlaceholders = implode(', ', array_fill(0, count($statuses), '%s'));

        $this->clauses[] = DB::prepare(
            "status IN({$statusPlaceholders})",
            $statuses
        );

        return $this;
    }

    public function setAction(string $action): static
    {
        $this->clauses[] = DB::prepare(
            "action = %s",
            $action
        );

        return $this;
    }

    public function setActionLike(string $action): static
    {
        $this->clauses[] = DB::prepare(
            "action LIKE '%%%s%%'",
            DB::esc_like($action)
        );

        return $this;
    }

    public function getLimitSql(): string
    {
        $limitSql = '';
        if ($this->limit) {
            $limitSql = DB::prepare('LIMIT %d, %d', $this->offset ?: 0, $this->limit);
        }

        return $limitSql;
    }

    public function getClauses(): array
    {
        return $this->clauses;
    }

    public function getClausesSql(): string
    {
        if ($this->clauses) {
            return 'WHERE '.implode(' AND ', $this->clauses);
        } else {
            return '';
        }
    }
}
