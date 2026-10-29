<?php

namespace IlBronza\UikitTemplate\Frontend;

final class MenuLocale
{
    public const SESSION_KEY = 'uikittemplate.menu_locale';

    public function availableLanguages(): array
    {
        return array_values(array_unique(array_filter(
            (array) config('uikittemplate.availableLanguages', []),
            fn ($locale) => is_string($locale) && $locale !== ''
        )));
    }

    public function current(): string
    {
        $request = request();
        $selected = $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null;

        return is_string($selected) && in_array($selected, $this->availableLanguages(), true)
            ? $selected
            : config('app.locale');
    }
}
