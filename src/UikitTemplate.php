<?php

namespace IlBronza\UikitTemplate;

class UikitTemplate
{
	public function getNavbars()
	{
		return collect();
	}

    static function getPageTitle()
    {
        if(request()->ajax())
            return ;

        $title = [trans('routes.' . request()->route()->getName())];

        $parameters = request()->route()->parameters();

        foreach($parameters as $name => $parameter)
            if(! is_string($parameter))
                if($_title = $parameter->getBrowserTitle())
                    $title[] = $_title;

        return implode(" | ", $title);
    }
}