<?php

namespace IlBronza\UikitTemplate;

use IlBronza\Buttons\Button;
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
    public $autoRefreshSeconds;

    public Collection $titleHtmlClasses;

    public $card;

    public $refresh = true;

    public $fields;

    public function __construct(array $parameters = [])
    {
        $uash = $parameters;
        $parameters = $this->manageFields($parameters);

        $this->buttons = collect();

        $this->setParameters($parameters);
    }

    public function setParameters(array $parameters)
    {
        if($parameters['buttons'] ?? false)
        {
            $this->addButtons($parameters['buttons']);

            unset($parameters['buttons']);
        }

        foreach($parameters as $name => $value)
            $this->$name = $value;
    }

    public function setTitle(string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    public function setHtmlClasses(mixed $htmlClasses) : self
    {
        $this->htmlClasses = collect($htmlClasses);

        return $this;
    }

    public function setTitleHtmlClasses(mixed $htmlClasses) : self
    {
        $this->titleHtmlClasses = collect($htmlClasses);

        return $this;
    }

    public function getTitleHtmlClasses() : Collection
    {
        return $this->titleHtmlClasses ?? collect();
    }

    public function getTitleHtmlClassesString() : string
    {
        return $this->getTitleHtmlClasses()->implode(' ');
    }

    public function getHtmlClasses() : Collection
    {
        return $this->htmlClasses ?? collect();
    }

    public function getHtmlClassesString() : string
    {
        return $this->getHtmlClasses()->implode(' ');
    }

    public function hasRefresh()
    {
        return $this->refresh;
    }

    public function setRefresh(bool $refresh)
    {
        return $this->refresh = $refresh;
    }

    public function getButtons() : Collection
    {
        return $this->buttons;
    }

    public function addButtons(Collection|array $buttons)
    {
        foreach($buttons as $button)
            $this->addButton($button);
    }

    public function addButton(Button $button)
    {
        $this->buttons->push(
            $button
        );
    }

    public function setUrl(string $url) : self
    {
        $this->url = $url;

        return $this;
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

    public function mustRenderAsCard() : bool
    {
        return !! $this->card;
    }

    public function setCard($card = true) : static
    {
        $this->card = $card;

        return $this;
    }

    public function render()
    {
        if($this->mustRenderAsCard())
            return $this->renderCard();

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