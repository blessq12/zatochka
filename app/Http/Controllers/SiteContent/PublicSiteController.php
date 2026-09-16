<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\Documents\Query\GetLegalDocumentHandler;
use App\Application\Feedback\Query\ListPublishedReviewsHandler;
use App\Application\SiteContent\Query\GetSiteBootstrapHandler;
use App\Domain\Equipment\VO\EquipmentType;
use App\Domain\Order\VO\SharpeningToolType;
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
        return view('public.pages.sharpening', [
            'site' => $this->makeSiteViewModel(
                $request,
                'Заточка инструментов — Заточка.ТСК',
            ),
            'toolTypes' => $this->sharpeningToolTypeOptions(),
        ]);
    }

    public function repair(Request $request): View
    {
        return view('public.pages.repair', [
            'site' => $this->makeSiteViewModel(
                $request,
                'Ремонт оборудования — Заточка.ТСК',
            ),
            'equipmentTypes' => $this->equipmentTypeOptions(),
        ]);
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
        return view($view, [
            'site' => $this->makeSiteViewModel($request, $title, $withReviews),
        ]);
    }

    private function makeSiteViewModel(
        Request $request,
        string $title,
        bool $withReviews = false,
    ): PublicSiteViewModel {
        return new PublicSiteViewModel(
            bootstrap: $this->getBootstrap->handle(),
            title: $title,
            currentPath: '/'.ltrim($request->path(), '/'),
            reviews: $withReviews ? $this->listPublishedReviews->handle() : null,
        );
    }

    /** @return list<array{value: string, label: string}> */
    private function sharpeningToolTypeOptions(): array
    {
        $items = [];

        foreach (SharpeningToolType::cases() as $type) {
            $items[] = [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        }

        return $items;
    }

    /** @return list<array{value: string, label: string}> */
    private function equipmentTypeOptions(): array
    {
        $items = [];

        foreach (EquipmentType::cases() as $type) {
            $items[] = [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        }

        return $items;
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
