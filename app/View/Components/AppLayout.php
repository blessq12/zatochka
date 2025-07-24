<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $canonical = null,
        public ?string $robots = null,
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
    ) {}

    public function render()
    {
        return view('layouts.app');
    }
}
