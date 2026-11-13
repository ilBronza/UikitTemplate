@props([
	'element',
	'label' => null,
	'ariaLabel' => 'Toggle content',
	'ariaControls' => null,
	'expandedIcon' => 'chevron-up',
	'collapsedIcon' => 'chevron-down'
])

@if($element->isCollapsible())
	<button
		id="{{ $element->getCollapseTargetId() }}-toggle"
		{{ $attributes->class(['uk-button', 'uk-button-link', 'ib-collapsible-toggle'])->merge([
			'type' => 'button',
			'uk-toggle' => 'target: ' . $element->getCollapseToggleTargetSelector() . '; cls: uk-hidden',
			'aria-controls' => $ariaControls ?? $element->getCollapseTargetId(),
			'aria-label' => $ariaLabel,
			'aria-expanded' => $element->isCollapsedInitially() ? 'false' : 'true',
			'data-collapse-state-key' => 'ilbronza:collapse:' . $element->getCollapseStateKey(),
			'data-collapse-target-selector' => $element->getCollapseTargetSelector()
		]) }}
	>
		@if($label)
			<span class="ib-collapsible-toggle-label">{{ $label }}</span>
		@endif

		<span
			data-collapse-expanded-icon
			class="{{ $element->getCollapseToggleStateClass() }} @if($element->isCollapsedInitially()) uk-hidden @endif uk-icon-button"
			uk-icon="{{ $expandedIcon }}"
		></span>
		<span
			data-collapse-collapsed-icon
			class="{{ $element->getCollapseToggleStateClass() }} @unless($element->isCollapsedInitially()) uk-hidden @endunless uk-icon-button"
			uk-icon="{{ $collapsedIcon }}"
		></span>
	</button>

	<script data-ilbronza-component="collapse-toggle">
		(() => {
			const initialize = () => {
				const button = document.getElementById(@json($element->getCollapseTargetId() . '-toggle'));

				if (! button)
					return;

				const targetSelector = button.dataset.collapseTargetSelector;
				const targets = () => document.querySelectorAll(targetSelector);
				const expandedIcon = button.querySelector('[data-collapse-expanded-icon]');
				const collapsedIcon = button.querySelector('[data-collapse-collapsed-icon]');

				const setCollapsed = (collapsed) => {
					targets().forEach((target) => target.classList.toggle('uk-hidden', collapsed));
					expandedIcon?.classList.toggle('uk-hidden', collapsed);
					collapsedIcon?.classList.toggle('uk-hidden', ! collapsed);
					button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
				};

				try {
					const storedState = window.sessionStorage.getItem(button.dataset.collapseStateKey);

					if (storedState === 'collapsed')
						setCollapsed(true);
					else if (storedState === 'expanded')
						setCollapsed(false);
				}
				catch (error) {
					return;
				}

                // UIkit may finish toggling after the next animation frame.
                // Persist the completed toggle, not the state observed during click.
                targets().forEach((target) => target.addEventListener('toggled', (event) => {
                    if (event.target !== target)
                        return;
                    const primary = targets()[0];
                    if (!primary)
                        return;
                    try {
                        window.sessionStorage.setItem(
                            button.dataset.collapseStateKey,
                            primary.classList.contains('uk-hidden') ? 'collapsed' : 'expanded'
                        );
                    } catch (error) {
                        return;
                    }
                }));
			};

			if (document.readyState === 'loading')
				document.addEventListener('DOMContentLoaded', initialize, { once: true });
			else
				initialize();
		})();
	</script>
@endif
