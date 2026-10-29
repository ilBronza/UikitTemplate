@if($languageSwitcher = $frontendMenu->languageSwitcher($frontendContext->languageUrls))
<div data-menu-language-switcher role="group" aria-label="{{ $frontendMenu->translate('uikit::frontend.language') }}">
    <ul class="uk-subnav">
        @foreach($languageSwitcher['items'] as $language)
            <li @if($language['active']) class="uk-active" @endif>
                <form method="POST" action="{{ $languageSwitcher['action'] }}">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $language['url'] }}">
                    <button type="submit" name="locale" value="{{ $language['locale'] }}"
                            class="uk-button uk-button-text" lang="{{ str_replace('_', '-', $language['locale']) }}"
                            aria-pressed="{{ $language['active'] ? 'true' : 'false' }}" @disabled($language['active'])>{{ $language['label'] }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
@endif
