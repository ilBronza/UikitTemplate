<?php

namespace IlBronza\UikitTemplate\Frontend;

final class FrontendContext
{
    public function __construct(
        public readonly string $locale,
        public readonly string $title = '',
        public readonly array $meta = [],
        public readonly array $links = [],
        public readonly mixed $page = null,
        // Public URLs already localized by the page/model, keyed by locale.
        public readonly array $languageUrls = [],
    ) {}
}
