<?php
/**
 * WpQueue.php
 *
 * @package   wp-queue
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace AshleyFae\WpQueue;

use Ashleyfae\LaravelContainer\Container;
use AshleyFae\WpQueue\Admin\JobsPage;
use AshleyFae\WpQueue\Components\ComponentInterface;
use AshleyFae\WpQueue\Cron\CronHandler;
use AshleyFae\WpQueue\Database\Tables\QueuedJobTable;
use Exception;

class WpQueue implements ComponentInterface
{
    protected static WpQueue $instance;
    protected static bool $booted = false;
    private Container $container;

    /** @var array components loaded on start-up */
    protected array $components = [
        CronHandler::class,
    ];

    public function __construct()
    {
        $this->container = new Container();
    }

    public static function instance(): WpQueue
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function get(string $abstract): object
    {
        return $this->container->make($abstract);
    }

    public function boot(): void
    {
        if (static::$booted) {
            return;
        }

        add_action('plugins_loaded', [$this, 'createOrUpdateTable']);
        add_action('admin_menu', [$this, 'registerAdminMenu']);

        foreach($this->components as $componentClassName) {
            $component = $this->get($componentClassName);
            if ($component instanceof ComponentInterface) {
                $component->boot();
            }
        }

        static::$booted = true;
    }

    public function createOrUpdateTable(): void
    {
        try {
            (new QueuedJobTable())->maybeUpdateOrCreate();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function registerAdminMenu(): void
    {
        add_submenu_page(
            'tools.php',
            __('Queued Jobs', 'wp-queue'),
            __('Queued Jobs', 'wp-queue'),
            'manage_options',
            'queued_options',
            static function () {
                return WpQueue::instance()->get(JobsPage::class)->render();
            }
        );
    }
}
