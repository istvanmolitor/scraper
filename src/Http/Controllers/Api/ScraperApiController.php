<?php

namespace Molitor\Scraper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Molitor\Scraper\DataTables\ScraperDataTable;
use Molitor\Scraper\Http\Requests\StoreScraperRequest;
use Molitor\Scraper\Http\Requests\UpdateScraperRequest;
use Molitor\Scraper\Http\Resources\ScraperResource;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Services\ScraperService;
use Molitor\Scraper\Services\ScraperWorkerService;

class ScraperApiController extends Controller
{
    public function __construct(
        private ScraperRepositoryInterface $scraperRepository,
    ) {}

    private function scraperService(): ScraperService
    {
        return app(ScraperService::class);
    }

    public function index(ScraperDataTable $dataTable): AnonymousResourceCollection
    {
        return $dataTable->getResponse();
    }

    public function dashboard(): JsonResponse
    {
        $scrapers = Scraper::query()
            ->withCount([
                'scraperUrls',
                'scraperUrls as downloaded_urls_count' => static fn ($innerQuery) => $innerQuery->whereNotNull('downloaded_at'),
            ])
            ->orderBy('name')
            ->get();

        $workerService = app(ScraperWorkerService::class);
        $blockedScrapers = $scrapers->filter(static fn (Scraper $scraper): bool => $scraper->blocked !== null && $scraper->blocked->isFuture());
        $activeScrapers = $scrapers->filter(static fn (Scraper $scraper): bool => $scraper->enabled && ($scraper->blocked === null || $scraper->blocked->isPast()));
        $inactiveScrapers = $scrapers->filter(static fn (Scraper $scraper): bool => ! $scraper->enabled);

        return response()->json([
            'summary' => [
                'total_scrapers' => $scrapers->count(),
                'active_scrapers' => $activeScrapers->count(),
                'inactive_scrapers' => $inactiveScrapers->count(),
                'blocked_scrapers' => $blockedScrapers->count(),
                'total_urls' => $scrapers->sum('scraper_urls_count'),
                'worker_enabled' => (bool) $workerService->isEnabled(),
                'worker_limit' => (int) $workerService->getLimit(),
            ],
            'data' => ScraperResource::collection($scrapers),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'defaults' => [
                'enabled' => false,
                'robots_txt' => false,
                'follow_links' => false,
                'chunk_size' => 1000,
            ],
        ]);
    }

    public function store(StoreScraperRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $scraper = $this->scraperService()->createScraper(
            $validated['name'],
            $validated['base_url'],
            (bool) ($validated['robots_txt'] ?? true),
            (bool) ($validated['follow_links'] ?? false),
            (bool) ($validated['enabled'] ?? true),
        );

        if (isset($validated['chunk_size'])) {
            $scraper->chunk_size = (int) $validated['chunk_size'];
            $scraper->save();
        }

        $scraper->loadCount('scraperUrls');

        return response()->json([
            'data' => new ScraperResource($scraper),
            'message' => 'Scraper sikeresen létrehozva.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $scraper = Scraper::query()
            ->withCount([
                'scraperUrls',
                'scraperUrls as downloaded_urls_count' => static fn ($innerQuery) => $innerQuery->whereNotNull('downloaded_at'),
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new ScraperResource($scraper),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $scraper = Scraper::query()
            ->withCount([
                'scraperUrls',
                'scraperUrls as downloaded_urls_count' => static fn ($innerQuery) => $innerQuery->whereNotNull('downloaded_at'),
            ])
            ->findOrFail($id);

        return response()->json([
            'data' => new ScraperResource($scraper),
        ]);
    }

    public function update(UpdateScraperRequest $request, int $id): JsonResponse
    {
        $scraper = Scraper::query()->findOrFail($id);
        $validated = $request->validated();

        $scraper->fill([
            'name' => $validated['name'],
            'enabled' => (bool) ($validated['enabled'] ?? true),
            'robots_txt' => (bool) ($validated['robots_txt'] ?? true),
            'follow_links' => (bool) ($validated['follow_links'] ?? false),
            'chunk_size' => (int) ($validated['chunk_size'] ?? 1000),
        ]);
        $scraper->save();

        $scraper->loadCount('scraperUrls');

        return response()->json([
            'data' => new ScraperResource($scraper),
            'message' => 'Scraper sikeresen frissítve.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $scraper = Scraper::query()->findOrFail($id);
        $this->scraperRepository->delete($scraper);

        return response()->json([
            'message' => 'Scraper sikeresen törölve.',
        ]);
    }
}
