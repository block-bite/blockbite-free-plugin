<?php

namespace Blockbite\Blockbite\Rest;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Rest\Routes\BlockHelpers as BlockHelperRoute;
use Blockbite\Blockbite\Rest\Routes\EditorSettings as EditorSettingsRoute;
use Blockbite\Blockbite\Rest\Routes\BlockSupport as BlockSupportRoute;
use Blockbite\Blockbite\Rest\Routes\Items as ItemsRoute;
use Blockbite\Blockbite\Rest\Routes\Bites as BitesRoute;
use Blockbite\Blockbite\Rest\Routes\Settings as SettingsRoute;
use Blockbite\Blockbite\Rest\Routes\DynamicContent as DynamicContentRoute;
use Blockbite\Blockbite\Rest\Routes\Projects as ProjectsRoute;

/**
 * Register all routes for REST API
 */
class Api
{

    /**
     * Plugin instance
     *
     * @since 1.0.0
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register all routes
     *
     * @since 1.0.0
     *
     * @uses "rest_api_init" action
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     * wp-json/blockbite/v1/
     * @return void
     */
    public function registerRoutes()
    {
        $routes = [
            new BlockHelperRoute($this->plugin),
            new EditorSettingsRoute($this->plugin),
            new BlockSupportRoute($this->plugin),
            new ItemsRoute($this->plugin),
            new SettingsRoute($this->plugin),
            new BitesRoute($this->plugin),
            new DynamicContentRoute($this->plugin),
            new ProjectsRoute($this->plugin),
        ];

        foreach ($routes as $route) {
            $route->Register();
        }
    }
}
