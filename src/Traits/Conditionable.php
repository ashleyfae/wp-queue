<?php
/**
 * Conditionable.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Traits;

trait Conditionable
{
    public function when($value, callable $callback, callable $default = null)
    {
        if ($value) {
            return $callback($this, $value) ?? $this;
        } elseif($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }
}
