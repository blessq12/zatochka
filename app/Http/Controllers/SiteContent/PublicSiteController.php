<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\Documents\Query\GetLegalDocumentHandler;
use App\Application\Feedback\Query\ListPublishedReviewsHandler;
use App\Application\SiteContent\Query\GetSiteBootstrapHandler;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\PublicSite\PublicSiteViewModel;
use App\Shared\Domain\DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class PublicSiteController extends Controller
{
    public function __construct(
        private GetSiteBootstrapHandler $getBootstrap,
        private ListPublishedReviewsHandler $listPublishedReviews,
        private GetLegalDocumentHandler $getLegalDocument,
    ) {}

    public function home(Request $request): View
    {
        return $this->page($request, 'public.pages.home', 'Заточка.ТСК — профессиональная заточка инструментов', withReviews: true);
    }

    public function sharpening(Request $request): View
    {
        return $this->page($request, 'public.pages.sharpening', 'Заточка инструментов — Заточка.ТСК');
    }

    public function repair(Request $request): View
    {
        return $this->page($request, 'public.pages.repair', 'Ремонт оборудования — Заточка.ТСК');
    }

    public function delivery(Request $request): View
    {
        return $this->page($request, 'public.pages.delivery', 'Доставка — Заточка.ТСК');
    }

    public function contacts(Request $request): View
    {
        return $this->page($request, 'public.pages.contacts', 'Контакты — Заточка.ТСК');
    }

    public function workSchedule(Request $request): View
    {
        return $this->page($request, 'public.pages.work-schedule', 'График работы — Заточка.ТСК');
    }

    public function prices(Request $request): View
    {
        return $this->page($request, 'public.pages.prices', 'Прайс — Заточка.ТСК');
    }

    public function privacyPolicy(Request $request): View
    {
        return $this->legal($request, 'privacy-policy', 'Политика конфиденциальности — Заточка.ТСК');
    }

    public function userAgreement(Request $request): View
    {
        return $this->legal($request, 'user-agreement', 'Пользовательское соглашение — Заточка.ТСК');
    }

    public function usageRules(Request $request): View
    {
        return $this->legal($request, 'usage-rules', 'Правила использования — Заточка.ТСК');
    }

    public function notFound(Request $request): Response
    {
        $site = new PublicSiteViewModel(
            bootstrap: $this->getBootstrap->handle(),
            title: 'Страница не найдена — Заточка.ТСК',
            currentPath: '/'.ltrim($request->path(), '/'),
        );

        return response()->view('public.pages.not-found', ['site' => $site], 404);
    }

    private function page(
        Request $request,
        string $view,
        string $title,
        bool $withReviews = false,
    ): View {
        $site = new PublicSiteViewModel(
            bootstrap: $this->getBootstrap->handle(),
            title: $title,
            currentPath: '/'.ltrim($request->path(), '/'),
            reviews: $withReviews ? $this->listPublishedReviews->handle() : null,
        );

        return view($view, ['site' => $site]);
    }

    private function legal(Request $request, string $slug, string $fallbackTitle): View
    {
        try {
            $document = $this->getLegalDocument->handle($slug)->toArray();
            $title = ($document['title'] ?? $fallbackTitle).' — Заточка.ТСК';
        } catch (DomainException) {
            abort(404);
        }

        $site = new PublicSiteViewModel(
            bootstrap: $this->getBootstrap->handle(),
            title: $title,
            currentPath: '/'.ltrim($request->path(), '/'),
            document: $document,
        );

        return view('public.pages.legal', ['site' => $site]);
    }
}
