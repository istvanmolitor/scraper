<?php

namespace Molitor\Scraper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

    public function index(Request $request): JsonResponse
    {
        $query = ScraperUrl::query()->with('scraper');

        $scraperId = $request->input('scraper_id');
        if (is_numeric($scraperId)) {
            $query->where('scraper_id', (int) $scraperId);
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($innerQuery) use ($search): void {
                $innerQuery->where('url', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $sortField = (string) $request->input('sort', 'id');
        $allowedSortFields = ['id', 'type', 'url', 'priority', 'downloaded_at', 'expiration_at', 'created_at'];
        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'id';
        }

        $sortDirection = strtolower((string) $request->input('direction', 'desc'));
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $scraperUrls = $query->orderBy($sortField, $sortDirection)->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => ScraperUrlResource::collection($scraperUrls->items()),
            'meta' => [
                'current_page' => $scraperUrls->currentPage(),
                'last_page' => $scraperUrls->lastPage(),
                'per_page' => $scraperUrls->perPage(),
                'total' => $scraperUrls->total(),
            ],
            'filters' => [
                'scraper_id' => is_numeric($scraperId) ? (int) $scraperId : null,
                'search' => $search,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
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
