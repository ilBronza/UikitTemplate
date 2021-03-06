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
}