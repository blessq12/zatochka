<?php

namespace App\Http\Controllers\SiteContent;

use App\Application\SiteContent\Command\DeleteLegalDocumentHandler;
use App\Application\SiteContent\Command\SaveLegalDocumentHandler;
use App\Application\SiteContent\Command\UpdateSiteContentSectionHandler;
use App\Application\SiteContent\Query\GetSiteContentAdminHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SiteContentController extends Controller
{
    public function __construct(
        private GetSiteContentAdminHandler $getAdmin,
        private UpdateSiteContentSectionHandler $updateSection,
        private SaveLegalDocumentHandler $saveLegal,
        private DeleteLegalDocumentHandler $deleteLegal,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->getAdmin->handle());
    }

    public function updateSection(Request $request, string $section): JsonResponse
    {
        $payload = match ($section) {
            'company' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'tagline' => ['nullable', 'string', 'max:255'],
                'owner_name' => ['nullable', 'string', 'max:255'],
                'inn' => ['nullable', 'string', 'max:64'],
                'ogrn' => ['nullable', 'string', 'max:64'],
                'legal_address' => ['nullable', 'string', 'max:255'],
                'actual_address' => ['nullable', 'string', 'max:255'],
            ]),
            'contacts' => $request->validate([
                'phone' => ['nullable', 'string', 'max:64'],
                'email' => ['nullable', 'email', 'max:255'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'array'],
                'address.main' => ['nullable', 'string', 'max:255'],
                'address.directions' => ['nullable', 'string'],
                'social' => ['nullable', 'array'],
                'social.email' => ['nullable', 'email', 'max:255'],
                'social.links' => ['nullable', 'array'],
                'social.links.*.name' => ['required_with:social.links', 'string', 'max:255'],
                'social.links.*.url' => ['required_with:social.links', 'string', 'max:255'],
            ]),
            'schedule' => $request->validate([
                'days' => ['required', 'array'],
                'days.*.label' => ['required', 'string', 'max:255'],
                'days.*.hours' => ['required', 'string', 'max:255'],
            ]),
            'faq' => $request->validate([
                'items' => ['required', 'array'],
                'items.*.question' => ['required', 'string', 'max:255'],
                'items.*.answer_lines' => ['required'],
            ]),
            'delivery' => $request->validate([
                'free_conditions' => ['nullable', 'array'],
                'free_conditions.*' => ['string'],
                'advantages' => ['nullable', 'array'],
                'advantages.*.title' => ['required_with:advantages', 'string', 'max:255'],
                'advantages.*.text' => ['required_with:advantages', 'string'],
            ]),
            'prices' => $request->validate([
                'items' => ['required', 'array'],
                'items.*.category' => ['required', 'string', 'in:sharpening,repair'],
                'items.*.name' => ['required', 'string', 'max:255'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'items.*.price' => ['required', 'string', 'max:64'],
                'items.*.prefix' => ['nullable', 'string', 'in:from,to'],
            ]),
            default => $request->all(),
        };

        $data = $this->updateSection->handle($section, $payload);

        return response()->json(['data' => $data]);
    }

    public function saveLegal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9\-]+$/'],
            'type' => ['nullable', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        return response()->json([
            'data' => $this->saveLegal->handle($data),
        ]);
    }

    public function destroyLegal(string $slug): JsonResponse|Response
    {
        if (! $this->deleteLegal->handle($slug)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->noContent();
    }
}
