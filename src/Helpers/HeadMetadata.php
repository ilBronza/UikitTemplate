<?php

namespace IlBronza\UikitTemplate\Helpers;

use IlBronza\UikitTemplate\Frontend\FrontendContext;
use InvalidArgumentException;

/** Metadata supplied by controllers, with configuration and URL fallbacks. */
final class HeadMetadata
{
    private array $overrides = [];
    private array $meta = [];
    private array $openGraph = [];
    private array $jsonLd = [];
    private array $hrefLinkOverrides = [];
    private ?FrontendContext $context = null;
    private ?object $seoModel = null;
    private ?string $seoLocale = null;
    private bool $hasSeoModel = false;
    private ?array $modelMeta = null;

    public function forContext(?FrontendContext $context): self
    {
        $head = clone $this;
        $head->context = $context;
        $head->modelMeta = null;

        return $head;
    }

    /** Models supply defaults; explicit metadata and controller setters still win. */
    public function setSeoModel(?object $model, ?string $locale = null): self
    {
        if ($model !== null && ! method_exists($model, 'getMetaTags')) {
            throw new InvalidArgumentException('The SEO model must expose getMetaTags().');
        }

        $this->seoModel = $model;
        $this->seoLocale = $locale;
        $this->hasSeoModel = true;
        $this->modelMeta = null;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->overrides['title'] = $title;

        return $this;
    }

    public function getTitle(?string $fallback = null): ?string
    {
        return array_key_exists('title', $this->overrides)
            ? $this->overrides['title'] : ($this->metadata()['title'] ?? $fallback);
    }

    public function setDescription(?string $description): self
    {
        $this->meta['description'] = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->metadata()['description'] ?? null;
    }

    public function setRobots(?string $robots): self
    {
        $this->meta['robots'] = $robots;

        return $this;
    }

    public function getRobots(): ?string
    {
        return $this->metadata()['robots'] ?? null;
    }

    public function setFavicon(array|string|null $icon): self
    {
        $this->overrides['favicon'] = $icon;

        return $this;
    }

    public function getFavicon(): ?array
    {
        return $this->getFavicons()[0] ?? null;
    }

    /** @return list<array{path: string, type?: string|null, sizes?: string}> */
    public function getFavicons(): array
    {
        $configured = $this->value('favicon', [
            'path' => config('uikittemplate.favicon.path', '/favicon.png'),
        ]);

        if ($configured === null) {
            return [];
        }

        $items = [];
        foreach ($this->faviconList($configured) as $icon) {
            $normalized = $this->icon($icon);
            if ($normalized !== null) {
                $items[] = $normalized;
            }
        }

        return $items;
    }

    public function setTouchIcon(array|string|null $icon): self
    {
        $this->overrides['touchIcon'] = $icon;

        return $this;
    }

    public function getTouchIcon(): ?array
    {
        return $this->icon($this->value('touchIcon'));
    }

    public function setThemeColor(?string $color): self
    {
        $this->overrides['themeColor'] = $color;

        return $this;
    }

    public function getThemeColor(): ?string
    {
        return $this->value('themeColor');
    }

    public function setCanonical(?string $url): self
    {
        $this->overrides['canonical'] = $url;

        return $this;
    }

    public function getCanonical(): string
    {
        $url = $this->value('canonical');

        // Keep the path (including IDs and trailing slash), removing only the query.
        return $url ? url($url) : request()->getSchemeAndHttpHost().request()->getBaseUrl().request()->getPathInfo();
    }

    /** Null restores automatic links; an empty array explicitly suppresses them. */
    public function setHrefLinks(?array $links): self
    {
        $this->overrides['hrefLinks'] = $links;
        $this->hrefLinkOverrides = [];

        return $this;
    }

    /** Override one language at render time; null omits only that language. */
    public function setHrefLink(string $locale, ?string $url): self
    {
        $this->hrefLinkOverrides[str_replace('_', '-', $locale)] = $url;

        return $this;
    }

    public function getHrefLink(string $locale): ?string
    {
        return $this->getHrefLinks()[str_replace('_', '-', $locale)] ?? null;
    }

    public function getHrefLinks(): array
    {
        $links = $this->resolvedHrefLinks();
        $xDefaultRemoved = false;

        foreach ($this->hrefLinkOverrides as $locale => $url) {
            if ($url === null) {
                $xDefaultRemoved = $xDefaultRemoved || $locale === 'x-default';
                unset($links[$locale]);
            } else {
                $links[$locale] = url($url);
            }
        }

        return $this->withXDefaultHrefLink($links, $xDefaultRemoved);
    }

    /** Adds hreflang="x-default" for the site default language when alternates exist. */
    private function withXDefaultHrefLink(array $links, bool $removed = false): array
    {
        if ($removed || $links === [] || array_key_exists('x-default', $links)) {
            return $links;
        }

        $defaultLocale = str_replace('_', '-', (string) (
            config('uikittemplate.head.xDefaultLocale')
            ?? config('app.base_locale')
            ?? config('app.locale')
        ));

        if (isset($links[$defaultLocale])) {
            $links['x-default'] = $links[$defaultLocale];

            return $links;
        }

        foreach ($this->languages() as $language) {
            $locale = str_replace('_', '-', $language);
            if (isset($links[$locale])) {
                $links['x-default'] = $links[$locale];

                return $links;
            }
        }

        return $links;
    }

    private function resolvedHrefLinks(): array
    {
        $links = $this->value('hrefLinks');

        if ($links !== null) {
            $result = [];

            foreach ($links as $locale => $url) {
                $result[str_replace('_', '-', $locale)] = url($url);
            }

            return $result;
        }

        $languages = $this->languages();

        if (count($languages) < 2) {
            return [];
        }

        $canonical = $this->getCanonical();
        $parts = parse_url($canonical);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('The canonical URL must be an absolute URL.');
        }

        $path = $parts['path'] ?? '/';
        $base = request()->getBaseUrl();
        $base = $base !== '' && ($path === $base || str_starts_with($path, $base.'/')) ? $base : '';
        $path = substr($path, strlen($base));
        $segments = explode('/', ltrim($path, '/'), 2);
        $normalized = static fn ($locale) => strtolower(str_replace('_', '-', $locale));

        // A language is a complete first path segment, never a substring of a slug.
        if (in_array($normalized(rawurldecode($segments[0])), array_map($normalized, $languages), true)) {
            $path = isset($segments[1]) ? '/'.$segments[1] : '';
        }

        $authority = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        $suffix = (isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        $result = [];

        foreach ($languages as $language) {
            $result[str_replace('_', '-', $language)] = $authority.$base.'/'.$language.$path.$suffix;
        }

        return $result;
    }

    /** Accept the flat metadata array already returned by CRUD's HasSeo trait. */
    public function setMeta(array $meta): self
    {
        if (array_key_exists('canonical', $meta)) {
            $this->setCanonical($meta['canonical']);
        }

        $this->meta = $meta;

        return $this;
    }

    public function getMeta(): array
    {
        return array_filter($this->metadata(), static fn ($value, $name) =>
            $value !== null && $value !== '' && (in_array($name, ['description', 'robots'], true) || str_starts_with($name, 'twitter:')),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /** Keys can be og:title, article:tag, etc.; shorthand title becomes og:title. */
    public function setOpenGraph(array $properties): self
    {
        $this->openGraph = [];

        foreach ($properties as $property => $value) {
            $this->setOpenGraphProperty($property, $value);
        }

        return $this;
    }

    /** Set only one property, preserving other explicit and inherited values. */
    public function setOpenGraphProperty(string $property, array|string|int|float|bool|null $value): self
    {
        $this->openGraph[str_contains($property, ':') ? $property : 'og:'.$property] = $value;

        return $this;
    }

    public function getOpenGraphProperty(string $property): array|string|int|float|bool|null
    {
        return $this->getOpenGraph()[str_contains($property, ':') ? $property : 'og:'.$property] ?? null;
    }

    public function setOpenGraphTitle(?string $title): self
    {
        return $this->setOpenGraphProperty('og:title', $title);
    }

    public function setOpenGraphDescription(?string $description): self
    {
        return $this->setOpenGraphProperty('og:description', $description);
    }

    public function setOpenGraphUrl(?string $url): self
    {
        return $this->setOpenGraphProperty('og:url', $url);
    }

    public function setOpenGraphType(?string $type): self
    {
        return $this->setOpenGraphProperty('og:type', $type);
    }

    public function setOpenGraphImage(array|string|null $image): self
    {
        return $this->setOpenGraphProperty('og:image', $image);
    }

    public function setOpenGraphImageAlt(?string $alt): self
    {
        return $this->setOpenGraphProperty('og:image:alt', $alt);
    }

    public function setOpenGraphLocale(?string $locale): self
    {
        return $this->setOpenGraphProperty('og:locale', $locale);
    }

    public function setOpenGraphSiteName(?string $name): self
    {
        return $this->setOpenGraphProperty('og:site_name', $name);
    }

    public function getOpenGraph(): array
    {
        $metadata = $this->metadata();
        $properties = array_filter(array_replace($metadata, $this->openGraph), static fn ($value, $property) =>
            str_contains($property, ':') && ! str_starts_with($property, 'twitter:') && $value !== null && $value !== '',
            ARRAY_FILTER_USE_BOTH
        );

        // HasSeo derives og:url from canonical. Keep that relationship after overrides,
        // while preserving an independently supplied Open Graph URL.
        $derivedUrl = true;

        foreach ([$this->meta, $this->context?->meta ?? [], $this->modelMetadata()] as $source) {
            if (array_key_exists('og:url', $source)) {
                $derivedUrl = $source['og:url'] !== null && $source['og:url'] === ($source['canonical'] ?? null);
                break;
            }
        }

        if ($properties !== [] && ! array_key_exists('og:url', $this->openGraph) && $derivedUrl) {
            $properties['og:url'] = $this->getCanonical();
        }

        return $properties;
    }

    /** Flatten repeated properties, keeping each image next to its attributes. */
    public function getOpenGraphTags(): array
    {
        $tags = [];

        foreach ($this->getOpenGraph() as $property => $values) {
            foreach (is_array($values) ? $values : [$values] as $value) {
                if ($property === 'og:image' && is_array($value)) {
                    $tags[] = ['property' => $property, 'content' => $value['url']];

                    foreach ($value as $attribute => $content) {
                        if ($attribute !== 'url' && $content !== null) {
                            $tags[] = ['property' => 'og:image:'.$attribute, 'content' => $content];
                        }
                    }
                } elseif (is_scalar($value)) {
                    $tags[] = ['property' => $property, 'content' => $value];
                } else {
                    throw new InvalidArgumentException("Invalid value for Open Graph property [{$property}].");
                }
            }
        }

        return $tags;
    }

    public function setTwitterProperty(string $property, ?string $value): self
    {
        $this->meta[str_starts_with($property, 'twitter:') ? $property : 'twitter:'.$property] = $value;

        return $this;
    }

    public function getTwitterProperty(string $property): ?string
    {
        return $this->getMeta()[str_starts_with($property, 'twitter:') ? $property : 'twitter:'.$property] ?? null;
    }

    public function setTwitterCard(?string $card): self
    {
        return $this->setTwitterProperty('card', $card);
    }

    public function setTwitterTitle(?string $title): self
    {
        return $this->setTwitterProperty('title', $title);
    }

    public function setTwitterDescription(?string $description): self
    {
        return $this->setTwitterProperty('description', $description);
    }

    public function setTwitterImage(?string $url): self
    {
        return $this->setTwitterProperty('image', $url);
    }

    public function setTwitterImageAlt(?string $alt): self
    {
        return $this->setTwitterProperty('image:alt', $alt);
    }

    public function setTwitterSite(?string $site): self
    {
        return $this->setTwitterProperty('site', $site);
    }

    public function setTwitterCreator(?string $creator): self
    {
        return $this->setTwitterProperty('creator', $creator);
    }

    public function addJsonLd(array $object): self
    {
        $this->jsonLd[] = $object;

        return $this;
    }

    public function setJsonLd(array $objects): self
    {
        $this->jsonLd = [];

        foreach ($objects as $object) {
            $this->addJsonLd($object);
        }

        return $this;
    }

    public function getJsonLd(): array
    {
        return $this->jsonLd;
    }

    /** Update a top-level property of one object; null removes that property. */
    public function setJsonLdProperty(string $property, mixed $value, int $objectIndex = 0): self
    {
        if ($this->jsonLd === [] && $objectIndex === 0) {
            if ($value === null) {
                return $this;
            }

            $this->jsonLd[] = [];
        }

        if (! array_key_exists($objectIndex, $this->jsonLd)) {
            throw new InvalidArgumentException("JSON-LD object [{$objectIndex}] does not exist.");
        }

        if ($value === null) {
            unset($this->jsonLd[$objectIndex][$property]);
        } else {
            $this->jsonLd[$objectIndex][$property] = $value;
        }

        return $this;
    }

    public function getJsonLdProperty(string $property, int $objectIndex = 0): mixed
    {
        return $this->jsonLd[$objectIndex][$property] ?? null;
    }

    public function getJsonLdScripts(): array
    {
        return array_map(static fn ($object) => json_encode($object,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ), $this->jsonLd);
    }

    private function value(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->overrides)) {
            return $this->overrides[$key];
        }

        if ($key === 'canonical' && ! empty($this->context?->meta['canonical'])) {
            return $this->context->meta['canonical'];
        }

        if ($key === 'canonical' && ! empty($this->modelMetadata()['canonical'])) {
            return $this->modelMetadata()['canonical'];
        }

        if ($key === 'hrefLinks' && $this->context?->languageUrls) {
            return $this->context->languageUrls;
        }

        return config('uikittemplate.head.'.$key) ?? $default;
    }

    private function metadata(): array
    {
        return array_replace($this->modelMetadata(), $this->context?->meta ?? [], $this->meta);
    }

    private function modelMetadata(): array
    {
        if ($this->modelMeta !== null) {
            return $this->modelMeta;
        }

        $model = $this->hasSeoModel ? $this->seoModel : $this->context?->page;

        if (! is_object($model) || ! method_exists($model, 'getMetaTags')) {
            return $this->modelMeta = [];
        }

        $locale = $this->seoLocale ?? $this->context?->locale
            ?? request()->attributes->get('frontend.locale', app()->getLocale());

        return $this->modelMeta = $model->getMetaTags($locale);
    }

    private function faviconList(array|string $configured): array
    {
        if (is_string($configured)) {
            return [$configured];
        }

        if (array_is_list($configured)) {
            return $configured;
        }

        return [$configured];
    }

    private function icon(array|string|null $icon): ?array
    {
        $icon = is_string($icon) ? ['path' => $icon] : $icon;

        if (! is_array($icon) || empty($icon['path'])) {
            return null;
        }

        $icon['type'] ??= match (strtolower(pathinfo(parse_url($icon['path'], PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'ico' => 'image/x-icon',
            default => null,
        };

        return $icon;
    }

    private function languages(): array
    {
        $languages = [];

        foreach (config('uikittemplate.availableLanguages', []) as $key => $label) {
            $locale = is_int($key) ? $label : $key;

            if (! is_string($locale) || ! preg_match('/^[a-zA-Z]{2,3}(?:[-_][a-zA-Z0-9]{2,8})*$/D', $locale)) {
                throw new InvalidArgumentException('Invalid locale in uikittemplate.availableLanguages.');
            }

            $languages[] = $locale;
        }

        return array_values(array_unique($languages));
    }
}
