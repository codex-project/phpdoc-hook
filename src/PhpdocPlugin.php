<?php
/**
 * Part of the Codex Project packages.
 *
 * License and copyright information bundled with this package in the LICENSE file.
 *
 * @author    Robin Radic
 * @copyright Copyright 2016 (c) Codex Project
 * @license   http://codex-project.ninja/license The MIT License
 */
namespace Codex\Addon\Phpdoc;

use Codex\Addons\Annotations\Plugin;
use Codex\Addons\BasePlugin;
use Codex\Codex;
use Codex\Contracts\Documents\Documents;
use Codex\Exception\CodexException;
use Codex\Projects\Project;

/**
 * This is the class Plugin.
 *
 * @package        Codex\Addon
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 * @Plugin("phpdoc")
 */
class PhpdocPlugin extends BasePlugin
{
    ## BasePlugin attributes

    public $project = 'codex-phpdoc.default_project_config';

    public $document = [];

    public $views = [
        'phpdoc.document' => 'codex-phpdoc::document',
        'phpdoc.entity'   => 'codex-phpdoc::entity',
    ];

    public $extend = [
        Codex::class   => [ 'phpdoc' => Phpdoc::class ],
        Project::class => [ 'phpdoc' => PhpdocRef::class ],
    ];

    ## ServiceProvider attributes

    protected $configFiles = [ 'codex-phpdoc' ];

    protected $viewDirs = [ 'views' => 'codex-phpdoc' ];

    protected $assetDirs = [ 'assets' => 'codex-phpdoc' ];

    protected $commands = [
        Console\ClearCacheCommand::class,
        Console\CreateCacheCommand::class,
    ];

    protected $bindings = [
        'codex.phpdoc.project'  => PhpdocRef::class,
        'codex.phpdoc.document' => PhpdocDocument::class,
    ];

    protected $shared = [ 'codex.phpdoc' => Phpdoc::class, ];



    public function register()
    {
        $app = parent::register();

        if ( $app[ 'config' ]->get('codex.http.enabled', false) ) {
            $this->registerRoutes();
        }

        // register link handler
        $app[ 'config' ]->set('codex.links.phpdoc', PhpdocLink::class . '@handle');

        // register custom document, this will handle showing the phpdoc
        $this->registerCustomDocument();

        return $app;
    }

    public function registerCustomDocument()
    {

        $this->hook('documents:constructed', function (Documents $documents) {
            /** @var \Codex\Contracts\Documents\Documents|\Codex\Documents\Documents $documents */
            $project = $documents->getProject();
            $documents->addCustomDocument($project->config('phpdoc.document_slug', 'phpdoc'), function (Documents $documents) use ($project) {
                $path = $project->refPath($project->config('phpdoc.path'));
                $pfs  = $project->getFiles();
                if ( !$pfs->exists($path) ) {
                    throw CodexException::documentNotFound('phpdoc');
                }
                return [ 'path' => $path, 'binding' => 'codex.phpdoc.document' ];
            });
        });
    }

    protected function registerRoutes()
    {
        $this->app->register(Http\HttpServiceProvider::class);
        $this->excludeRoute(config('codex-phpdoc.route_prefix'));
    }


}
