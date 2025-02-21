<?php
/**
 * ComponentInterface.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue\Components;

interface ComponentInterface
{
    public function boot() : void;
}
