<?php

namespace IlBronza\UikitTemplate;

use Illuminate\Support\Str;

class UikitTemplate
{
	public function getNavbars()
	{
		return collect();
	}

    static function getBodyClass()
    {
        return Str::slug(static::getPageTitle());
    }

    static function getPageTitle()
    {
        if(request()->ajax())
            return ;

        $parameters = request()->route()->parameters();

        $title = [trans('routes.' . request()->route()->getName(), $parameters)];

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