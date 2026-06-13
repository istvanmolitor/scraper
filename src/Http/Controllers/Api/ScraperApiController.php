<?php

namespace Molitor\Scraper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\Scraper\Http\Requests\StoreScraperRequest;
use Molitor\Scraper\Http\Requests\UpdateScraperRequest;
use Molitor\Scraper\Http\Resources\ScraperResource;
use Molitor\Scraper\Models\Scraper;
use Molitor\Scraper\Repositories\ScraperRepositoryInterface;
use Molitor\Scraper\Services\ScraperService;

class ScraperApiController extends Controller
{
    public function __construct(
        private ScraperRepositoryInterface $scraperRepository,
    ) {}

    private function scraperService(): ScraperService
    {
        return app(ScraperService::class);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Scraper::query()->withCount('scraperUrls');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($innerQuery) use ($search): void {
                $innerQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('base_url', 'like', "%{$search}%");
            });
        }

        $sortField = (string) $request->input('sort', 'id');
        $allowedSortFields = ['id', 'name', 'base_url', 'enabled', 'chunk_size', 'created_at'];
        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'id';
        }

        $sortDirection = strtolower((string) $request->input('direction', 'desc'));
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $scrapers = $query->orderBy($sortField, $sortDirection)->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => ScraperResource::collection($scrapers->items()),
            'meta' => [
                'current_page' => $scrapers->currentPage(),
                'last_page' => $scrapers->lastPage(),
                'per_page' => $scrapers->perPage(),
                'total' => $scrapers->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'defaults' => [
                'enabled' => true,
                'robots_txt' => true,
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
        $scraper = Scraper::query()->withCount('scraperUrls')->findOrFail($id);

        return response()->json([
            'data' => new ScraperResource($scraper),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $scraper = Scraper::query()->withCount('scraperUrls')->findOrFail($id);

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
