<?php

namespace IlBronza\UikitTemplate\Traits;

trait UseTemplateTrait
{
	/**
	 * must return the name of the package to have correct view folder name
	 * example buttons::uikit.horizontal is obtained by
	 *
	 * public function getViewComponentName()
	 * {
	 * 		return 'buttons';
	 * }
	 *
	 * @return string|null
	 **/
	abstract function getViewComponentName() : ? string;

	public function getTemplateName()
    {
        return config('app.template', 'rinominare_template_in_generale');
    }

    public function template()
    {
        return app($this->getTemplateName());
    }

    public function getTemplateViewName(string $view)
    {
    	$componentName = $this->getViewComponentName();
    	$templateFolder = $this->template()->getViewFolderName();

    	return "{$componentName}::{$templateFolder}.{$view}";
    }
}