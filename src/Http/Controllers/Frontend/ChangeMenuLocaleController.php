<?php

namespace IlBronza\UikitTemplate\Http\Controllers\Frontend;

use IlBronza\UikitTemplate\Frontend\MenuLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class ChangeMenuLocaleController
{
    public function __invoke(Request $request, MenuLocale $locale): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', Rule::in($locale->availableLanguages())],
            'return_to' => ['nullable', 'string', 'max:4096'],
        ]);

        $request->session()->put(MenuLocale::SESSION_KEY, $data['locale']);

        $target = $this->localReturnPath($request, $data['return_to'] ?? '/');

        return new RedirectResponse($target, 303, ['Cache-Control' => 'private, no-store']);
    }

    private function localReturnPath(Request $request, string $target): string
    {
        if (str_contains($target, '\\') || preg_match('/[\x00-\x20\x7f]/', $target)) {
            return '/';
        }

        $parts = parse_url($target);
        if ($parts === false) {
            return '/';
        }

        // Model getters commonly use route(), which returns an absolute URL.
        // Accept the current origin, then redirect with its exact path/query/fragment.
        if (isset($parts['scheme']) || isset($parts['host'])) {
            $scheme = strtolower($parts['scheme'] ?? '');
            $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
            if ($scheme !== $request->getScheme()
                || strtolower($parts['host'] ?? '') !== strtolower($request->getHost())
                || $port !== $request->getPort() || isset($parts['user']) || isset($parts['pass'])) {
                return '/';
            }

            $target = ($parts['path'] ?? '/')
                .(isset($parts['query']) ? '?'.$parts['query'] : '')
                .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return str_starts_with($target, '/') && ! str_starts_with($target, '//') ? $target : '/';
    }
}
