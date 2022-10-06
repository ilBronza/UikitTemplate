<?php

namespace IlBronza\UikitTemplate;

use IlBronza\FormField\FormField;
use IlBronza\UikitTemplate\Traits\UseTemplateTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Fetcher
{
    use UseTemplateTrait;

    public $url;
    public $title;
    public $id;
    public $canExpand = false;

    public $fields;

    public function __construct(array $parameters = [])
    {
        $uash = $parameters;
        $parameters = $this->manageFields($parameters);

        foreach($parameters as $name => $value)
            $this->$name = $value;            
    }

    private function manageFields(array $parameters) : array
    {
        $this->fields = collect();

        if(! $fields = ($parameters['fields'] ?? false))
            return $parameters;

        foreach($fields as $field)
            $this->addField($field);

        unset($parameters['fields']);

        return $parameters;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getViewComponentName() : string
    {
        return 'uikittemplate';
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        if(! $this->id)
            $this->setId(
                'fetcher' . Str::random(8)
            );

        return $this->id;
    }

    public function renderType(string $type)
    {
        $viewName = $this->getTemplateViewName("fetcher.{$type}");

        return view($viewName, ['fetcher' => $this])->render();        
    }

    public function render()
    {
        return $this->renderType('_fetcher');
    }

    public function renderCard()
    {
        return $this->renderType('_fetcherCard');
    }

    public function renderPageCard()
    {
        return $this->renderType('_fetcherPageCard');
    }

    public function addField(FormField $formField)
    {
        $this->fields->push($formField);
    }

    public function hasFields()
    {
        return count($this->fields);
    }

    public function getFields() : Collection
    {
        return $this->fields;
    }

    public function setCanExpand(bool $canExpand)
    {
        $this->canExpand = $canExpand;
    }

    public function canExpand() : bool
    {
        return !! $this->canExpand;
    }
}