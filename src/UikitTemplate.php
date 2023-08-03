<?php

namespace IlBronza\UikitTemplate;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UikitTemplate
{
    public function getTemplateName()
    {
        return config('uikittemplate.template', 'uikit');
    }

	public function getNavbars()
	{
		return collect();
	}

    static function getBodyClass()
    {
        if(! Auth::id())
            return Str::slug(static::getPageTitle()) . ' ' . static::getPageClass();

        return "user" . Auth::id() . " " . Str::slug(static::getPageTitle()) . ' ' . static::getPageClass();
    }

    static function getPageClass()
    {
        if(request()->ajax())
            return ;

        $parameters = request()->route()->parameters();

        $routeParameters = [];

        foreach($parameters as $name => $parameter)
            if ($parameter instanceof Model)
                $routeParameters[$name] = $parameter->getName();
            else
                $routeParameters[$name] = $parameter;

        $key = 'pageClasses.' . request()->route()->getName();
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

    static function getPageTitle()
    {
        if(request()->ajax())
            return ;

        $parameters = request()->route()->parameters();

        $routeParameters = [];

        foreach($parameters as $name => $parameter)
            if ($parameter instanceof Model)
                $routeParameters[$name] = $parameter->getName();
            else
                $routeParameters[$name] = $parameter;

        $title = [trans('routes.' . request()->route()->getName(), $routeParameters)];

        foreach($parameters as $name => $parameter)
            if(! is_string($parameter))
                if($_title = $parameter->getBrowserTitle())
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