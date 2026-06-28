<?php

namespace Molitor\Scraper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Molitor\Scraper\DataTables\ScraperUrlDataTable;
use Molitor\Scraper\Http\Requests\StoreScraperUrlRequest;
use Molitor\Scraper\Http\Requests\UpdateScraperUrlRequest;
use Molitor\Scraper\Http\Resources\ScraperUrlResource;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Models\ScraperUrl;
use Molitor\Scraper\Repositories\ScraperUrlRepositoryInterface;
use Molitor\Scraper\Services\ScraperService;

class ScraperUrlApiController extends Controller
{
    public function __construct(
        private ScraperUrlRepositoryInterface $scraperUrlRepository,
    ) {}

    private function scraperService(): ScraperService
    {
        return app(ScraperService::class);
    }

    public function index(ScraperUrlDataTable $dataTable): AnonymousResourceCollection
    {
        return $dataTable->getResponse();
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'scrapers' => Scraper::query()->orderBy('name')->get(['id', 'name', 'base_url']),
            'defaults' => [
                'priority' => 0,
            ],
        ]);
    }

    public function store(StoreScraperUrlRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $scraper = Scraper::query()->findOrFail((int) $validated['scraper_id']);

        $scraperUrl = $this->scraperUrlRepository->save($scraper, [
            'type' => $validated['type'] ?? 'page',
            'url' => $validated['url'],
            'priority' => $validated['priority'] ?? 0,
            'expiration_at' => $validated['expiration_at'] ?? null,
        ]);

        $scraperUrl->load('scraper');

        return response()->json([
            'data' => new ScraperUrlResource($scraperUrl),
            'message' => 'Scraper URL sikeresen létrehozva.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $scraperUrl = ScraperUrl::query()->with('scraper')->findOrFail($id);

        return response()->json([
            'data' => new ScraperUrlResource($scraperUrl),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $scraperUrl = ScraperUrl::query()->with('scraper')->findOrFail($id);

        return response()->json([
            'data' => new ScraperUrlResource($scraperUrl),
            'scrapers' => Scraper::query()->orderBy('name')->get(['id', 'name', 'base_url']),
        ]);
    }

    public function update(UpdateScraperUrlRequest $request, int $id): JsonResponse
    {
        $scraperUrl = ScraperUrl::query()->with('scraper')->findOrFail($id);
        $validated = $request->validated();

        $scraperUrl->fill([
            'type' => $validated['type'] ?? $scraperUrl->type,
            'priority' => $validated['priority'] ?? $scraperUrl->priority,
            'expiration_at' => $validated['expiration_at'] ?? null,
        ]);
        $scraperUrl->save();

        return response()->json([
            'data' => new ScraperUrlResource($scraperUrl),
            'message' => 'Scraper URL sikeresen frissítve.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $scraperUrl = ScraperUrl::query()->findOrFail($id);
        $scraperUrl->delete();

        return response()->json([
            'message' => 'Scraper URL sikeresen törölve.',
        ]);
    }

    public function download(int $id): JsonResponse
    {
        $scraperUrl = ScraperUrl::query()->findOrFail($id);
        $this->scraperService()->downloadScraperUrl($scraperUrl);

        return response()->json([
            'message' => 'URL letöltése sikeresen elindítva.',
        ]);
    }

    public function bulkDownload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:scraper_urls,id'],
        ]);

        $urls = ScraperUrl::query()->whereIn('id', $validated['ids'])->get();

        foreach ($urls as $url) {
            $this->scraperService()->downloadScraperUrl($url);
        }

        return response()->json([
            'message' => 'A kiválasztott URL-ek letöltése elindítva.',
        ]);
    }
}
