<?php

namespace App\Application\OrderFulfillment\Port;

interface PdfRendererInterface
{
  /**
   * @param  array<string, mixed>  $viewData
   */
    public function render(string $view, array $viewData): string;
}
