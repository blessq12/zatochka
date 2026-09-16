<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Query\GetLegalDocumentHandler;
use App\Application\SiteContent\Query\GetLegalDocumentQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class PublicSiteController extends Controller
{
    public function __construct(
        private GetLegalDocumentHandler $getLegalDocument,
    ) {}

    public function home(): View
    {
        return view('public.pages.home', [
            'title' => 'Заточка.ТСК — профессиональная заточка инструментов',
        ]);
    }

    public function sharpening(): View
    {
        return view('public.pages.sharpening', [
            'title' => 'Заточка инструментов — Заточка.ТСК',
            'toolTypes' => [],
        ]);
    }

    public function repair(): View
    {
        return view('public.pages.repair', [
            'title' => 'Ремонт оборудования — Заточка.ТСК',
            'equipmentTypes' => [],
        ]);
    }

    public function delivery(): View
    {
        return view('public.pages.delivery', [
            'title' => 'Доставка — Заточка.ТСК',
        ]);
    }

    public function contacts(): View
    {
        return view('public.pages.contacts', [
            'title' => 'Контакты — Заточка.ТСК',
        ]);
    }

    public function workSchedule(): View
    {
        return view('public.pages.work-schedule', [
            'title' => 'График работы — Заточка.ТСК',
        ]);
    }

    public function prices(): View
    {
        return view('public.pages.prices', [
            'title' => 'Прайс — Заточка.ТСК',
        ]);
    }

    public function privacyPolicy(): View|Response
    {
        return $this->legal('privacy-policy', 'Политика конфиденциальности — Заточка.ТСК');
    }

    public function userAgreement(): View|Response
    {
        return $this->legal('user-agreement', 'Пользовательское соглашение — Заточка.ТСК');
    }

    public function usageRules(): View|Response
    {
        return $this->legal('usage-rules', 'Правила использования — Заточка.ТСК');
    }

    public function notFound(): Response
    {
        return response()->view('public.pages.not-found', [
            'title' => 'Страница не найдена — Заточка.ТСК',
        ], 404);
    }

    private function legal(string $slug, string $fallbackTitle): View|Response
    {
        $document = $this->getLegalDocument->handle(new GetLegalDocumentQuery($slug));

        if ($document === null) {
            return $this->notFound();
        }

        return view('public.pages.legal', [
            'title' => ($document['title'] ?? $fallbackTitle).' — Заточка.ТСК',
            'document' => $document,
        ]);
    }
}
