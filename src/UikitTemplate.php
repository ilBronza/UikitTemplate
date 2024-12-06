<?php

namespace IlBronza\UikitTemplate;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UikitTemplate
{
	public string $pageTitle;
	public array $bodyHtmlClasses = [];

    public function getTemplateName()
    {
        return config('uikittemplate.template', 'uikit');
    }

	public function getNavbars()
	{
		return collect();
	}

    public function getContainerClass()
    {
        return 'uk-padding-small ibcontainer';
    }

	public function addBodyHtmlClass(string $class)
	{
		$this->bodyHtmlClasses[] = $class;
	}

	public function getBodyHtmlClasses() : array
	{
		return $this->bodyHtmlClasses;
	}

    public function getBodyClass() : string
    {
		$standardClass = ((! Auth::id()) ? 'guest' : 'user') . Str::slug($this->getPageTitle()) . ' ' . static::getPageClass();

	    $this->addBodyHtmlClass($standardClass);

		return implode(' ', $this->getBodyHtmlClasses());
    }

    static function getPageClass()
    {
        if(request()->ajax())
            return ;

        if(! request()->route())
            return ;

        if(! $routeName = request()->route()->getName())
            return ;

        $parameters = request()->route()->parameters();

        $routeParameters = [];

        foreach($parameters as $name => $parameter)
            if ($parameter instanceof Model)
                $routeParameters[$name] = $parameter->getName();
            else
                $routeParameters[$name] = $parameter;

        $key = 'pageClasses.' . $routeName;
        $translated = trans($key, $routeParameters);

        if($key == $translated)
            return null;

        $title = [$translated];

        foreach($parameters as $name => $parameter)
            if(! is_string($parameter))
                if($_title = $parameter->getBrowserTitle())
                    $title[] = $_title;

        return implode(" | ", $title);
    }

    static function getTranslationKey() : string
    {
        return request()->route()->action['routeTranslationPrefix'] ?? 'routes.';
    }

	public function setPageTitle(string $title) : self
	{
		$this->pageTitle = $title;

		return $this;
	}

    public function getPageTitle()
    {
        if(request()->ajax())
            return ;

		if(isset($this->pageTitle))
			return $this->pageTitle;

        if(! request()->route())
            return config('app.name');

        $parameters = request()->route()->parameters();

        $routeParameters = [];

        foreach($parameters as $name => $parameter)
            if ($parameter instanceof Model)
                $routeParameters[$name] = $parameter->getName();
            else
                $routeParameters[$name] = $parameter;

        $translationKey = static::getTranslationKey();

        $title = [trans($translationKey . request()->route()->getName(), $routeParameters)];

        foreach($parameters as $name => $parameter)
            if(! is_string($parameter))
                if($_title = $parameter->getBrowserTitle())
                    if(! in_array($_title, $title))
                        $title[] = $_title;

        return implode(" | ", $title);
    }

    public function getViewFolderName()
    {
        return 'uikit';
    }

    public function getNavbarPositionClass(string $position = null)
    {
        if(! $position)
            return null;

        return "uk-navbar-{$position}";
    }

    public function getFlexWrapClass($type = 'wrap')
    {
        $result = ['uk-flex'];

        if($type == 'wrap')
            $result[] = 'uk-flex-wrap';

        if($type == 'reverse')
            $result[] = 'uk-flex-wrap-reverse';

        if($type == 'nowrap')
            $result[] = 'uk-flex-nowrap';

        if(in_array($type, ['stretch', 'between', 'around', 'top', 'middle', 'bottom']))
            $result[] = "uk-flex-wrap uk-flex-{$type}";

        return implode(" ", $result);
    }

    public function getWidthFullClass()
    {
        return 'uk-width-1-1';
    }
}