<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Query\GetLegalDocumentHandler;
use App\Application\SiteContent\Query\GetLegalDocumentQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewResponse;

final class PublicSiteController extends Controller
{
    public function __construct(
        private GetLegalDocumentHandler $getLegalDocument,
    ) {}

    public function home(): ViewResponse
    {
        return view('public.pages.home', [
            'title' => $this->brandTitle(),
        ]);
    }

    public function sharpening(): ViewResponse
    {
        return view('public.pages.sharpening', [
            'title' => 'Заточка инструментов — '.$this->brandName(),
            'toolTypes' => [],
        ]);
    }

    public function repair(): ViewResponse
    {
        return view('public.pages.repair', [
            'title' => 'Ремонт оборудования — '.$this->brandName(),
            'equipmentTypes' => [],
        ]);
    }

    public function delivery(): ViewResponse
    {
        return view('public.pages.delivery', [
            'title' => 'Доставка — '.$this->brandName(),
        ]);
    }

    public function contacts(): ViewResponse
    {
        return view('public.pages.contacts', [
            'title' => 'Контакты — '.$this->brandName(),
        ]);
    }

    public function workSchedule(): ViewResponse
    {
        return view('public.pages.work-schedule', [
            'title' => 'График работы — '.$this->brandName(),
        ]);
    }

    public function prices(): ViewResponse
    {
        return view('public.pages.prices', [
            'title' => 'Прайс — '.$this->brandName(),
        ]);
    }

    public function privacyPolicy(): ViewResponse|Response
    {
        return $this->legal('privacy-policy', 'Политика конфиденциальности');
    }

    public function userAgreement(): ViewResponse|Response
    {
        return $this->legal('user-agreement', 'Пользовательское соглашение');
    }

    public function usageRules(): ViewResponse|Response
    {
        return $this->legal('usage-rules', 'Правила использования');
    }

    public function notFound(): Response
    {
        return response()->view('public.pages.not-found', [
            'title' => 'Страница не найдена — '.$this->brandName(),
        ], 404);
    }

    private function legal(string $slug, string $fallbackTitle): ViewResponse|Response
    {
        $document = $this->getLegalDocument->handle(new GetLegalDocumentQuery($slug));

        if ($document === null) {
            return $this->notFound();
        }

        return view('public.pages.legal', [
            'title' => ($document['title'] ?? $fallbackTitle).' — '.$this->brandName(),
            'document' => $document,
        ]);
    }

    private function brandName(): string
    {
        $company = View::shared('company', []);

        return (string) (($company['name'] ?? null) ?: 'Заточка.ТСК');
    }

    private function brandTitle(): string
    {
        $company = View::shared('company', []);
        $name = (string) (($company['name'] ?? null) ?: 'Заточка.ТСК');
        $tagline = (string) (($company['tagline'] ?? null) ?: 'профессиональная заточка инструментов');

        return $name.' — '.$tagline;
    }
}
