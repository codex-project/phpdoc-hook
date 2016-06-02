<?php
namespace Codex\Addon\Phpdoc;

use Codex\Documents\Documents;
use Codex\Exception\CodexException;
use Codex\Projects\Project;
use Codex\Projects\Projects;
use Codex\Traits\CodexProviderTrait;
use Sebwite\Support\ServiceProvider;

class PhpdocServiceProvider extends ServiceProvider
{
    use CodexProviderTrait;

    protected $dir = __DIR__;

    protected $configFiles = [ 'codex-phpdoc' ];

    protected $viewDirs = [ 'views' => 'codex-phpdoc' ];

    protected $assetDirs = [ 'assets' => 'codex-phpdoc' ];

    protected $providers = [
        Http\HttpServiceProvider::class,
    ];

    protected $shared = [
        'codex.phpdoc' => Factory::class
    ];
    protected $bindings = [
        'codex.phpdoc.project' => ProjectPhpdoc::class,
        'codex.phpdoc.document' => PhpdocDocument::class
    ];

    public function register()
    {
        $app = parent::register();

        $this->codexIgnoreRoute(config('codex-phpdoc.route_prefix'));
        $this->codexProjectConfig('codex-phpdoc.default_project_config');

        $this->addMenuItem();
        $this->addCustomDocument();

        return $app;
    }

    protected function addMenuItem()
    {
        $this->codexHook('projects:active', function (Projects $projects, Project $project) {
            if ( $project->config('phpdoc.enabled') !== true ) {
                return;
            }
            $menu = $project->getCodex()->menus->get('sidebar');
            $node = $menu->add('phpdoc', $project->config('phpdoc.menu_name'));
            $node->setMeta('icon', $project->config('phpdoc.menu_icon'));
            $node->setAttribute('href', $project->url($project->config('phpdoc.document_slug'), $project->getRef()));
        });
    }

    protected function addCustomDocument()
    {
        $this->codexHook('documents:constructed', function (Documents $documents) {
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


}