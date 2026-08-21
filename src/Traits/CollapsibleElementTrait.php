<?php

namespace IlBronza\UikitTemplate\Traits;

use InvalidArgumentException;

trait CollapsibleElementTrait
{
	public bool $collapsible = false;
	public ?bool $collapsedInitially = null;
	public ?string $collapseStateKey = null;

	protected ?string $collapseTargetId = null;

	public function setCollapsible(bool $collapsible = true) : static
	{
		$this->collapsible = $collapsible;

		return $this;
	}

	public function isCollapsible() : bool
	{
		return $this->collapsible;
	}

	public function setCollapsedInitially(?bool $collapsedInitially = true) : static
	{
		$this->collapsedInitially = $collapsedInitially;

		return $this;
	}

	public function isCollapsedInitially() : bool
	{
		if(! $this->isCollapsible())
			return false;

		return $this->collapsedInitially ?? $this->isCollapsedInitiallyByDefault();
	}

	protected function isCollapsedInitiallyByDefault() : bool
	{
		return false;
	}

	public function setCollapseStateKey(?string $collapseStateKey) : static
	{
		$this->collapseStateKey = $collapseStateKey ?: null;

		return $this;
	}

	public function getCollapseStateKey() : string
	{
		return $this->collapseStateKey ?? $this->getDefaultCollapseStateKey();
	}

	protected function getDefaultCollapseStateKey() : string
	{
		return $this->getCollapseTargetId();
	}

	public function setCollapseTargetId(string $collapseTargetId) : static
	{
		if(! preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $collapseTargetId))
			throw new InvalidArgumentException("Invalid collapse target id [{$collapseTargetId}].");

		$this->collapseTargetId = $collapseTargetId;

		return $this;
	}

	public function getCollapseTargetId() : string
	{
		if($this->collapseTargetId)
			return $this->collapseTargetId;

		$className = trim(
			preg_replace('/[^a-z0-9_-]+/', '-', strtolower(static::class)),
			'-'
		);

		if($className === '')
			$className = 'element';

		return $this->collapseTargetId = "collapsible-{$className}-" . spl_object_id($this);
	}

	public function getCollapseTargetSelector() : string
	{
		return '.' . $this->getCollapseTargetHtmlClass();
	}

	public function getCollapseTargetHtmlClass() : string
	{
		return $this->getCollapseTargetId() . '-target';
	}

	public function getCollapseToggleStateClass() : string
	{
		return $this->getCollapseTargetId() . '-toggle-state';
	}

	public function getCollapseToggleTargetSelector() : string
	{
		return implode(', ', [
			$this->getCollapseTargetSelector(),
			'.' . $this->getCollapseToggleStateClass()
		]);
	}

	public function getCollapseTargetHtmlClasses() : array
	{
		return array_filter([
			'ib-collapsible-content',
			$this->getCollapseTargetHtmlClass(),
			$this->isCollapsedInitially() ? 'uk-hidden' : null
		]);
	}

	public function getCollapseTargetHtmlClassesString() : string
	{
		return implode(' ', $this->getCollapseTargetHtmlClasses());
	}
}
