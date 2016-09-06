<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Codex\Addon\Phpdoc;

use Codex\Documents\Document;
use Codex\Projects\Project;
use Codex\Projects\Ref;
use Illuminate\Contracts\Cache\Repository;

/**
 * This is the PhpdocDocument.
 *
 * @package        Codex\Hooks
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class PhpdocDocument extends Document
{

    protected $phpdoc;

    public function __construct($codex, Project $project, Ref $ref, Repository $cache, $path, $pathName)
    {
        $pathName = 'phpdoc';
        config()->set('debugbar.enabled', false);
        app()->bound('debugbar') && app('debugbar')->disable();
        parent::__construct($codex, $project, $ref, $cache, $path, $pathName);
        $this->mergeAttributes($project->config('phpdoc'));
        $codex->theme->addJavascript('phpdoc-templates', 'vendor/codex-phpdoc/scripts/phpdoc-templates', [ 'codex' ]);
        $codex->theme->addJavascript('phpdoc', 'vendor/codex-phpdoc/scripts/phpdoc', [ 'codex', 'phpdoc-templates' ]);
        $codex->theme->addStylesheet('phpdoc', 'vendor/codex-phpdoc/styles/phpdoc');
        $codex->theme->addBodyClass('sidebar-closed content-compact addon-phpdoc');
        $codex->theme->set('phpdoc', [
            'project'       => $project->getName(),
            'ref'           => $ref->getName(),
            'default_class' => $project->config('phpdoc.default_class', null),
            'title'         => $project->config('phpdoc.title', 'Api Documentation'),
            'document_slug' => $project->config('phpdoc.document_slug', 'phpdoc'),
            'debug'         => $project->config('phpdoc.default_class', false),
        ]);
        $codex->theme->addScript('phpdoc', <<<JS
codex.phpdoc.init();
JS
        );
    }

    public function render()
    {

        $this->hookPoint('document:render');
        $prismPlugins = array_replace($this->attr('processors.prismjs.plugins', [ ]), [
            'line-numbers',
            'autolinker',
        ]);
        $this->setAttribute('processors.prismjs.plugins', $prismPlugins);
        $this->runProcessor('prismjs');
        #$content = "<phpdoc project='{$this->project->getName()}' ref='{$this->project->getRef()}' full-name='{$this->p'></phpdoc>";
        $content = "<phpdoc></phpdoc>";
        $this->hookPoint('document:rendered');
        return $content;
    }


    /**
     * getBreadcrumb
     */
    public function getBreadcrumb()
    {
        return $this->getCodex()->menus->get('sidebar')->getBreadcrumbToHref($this->url());
    }

    public function getLastModified()
    {
        return parent::getLastModified();
    }


}
