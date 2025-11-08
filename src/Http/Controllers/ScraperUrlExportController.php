<?php

namespace Molitor\Scraper\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Molitor\Scraper\Models\ScraperUrl;

class ScraperUrlExportController
{
    public function __invoke(Request $request)
    {
        // Authorization: reuse the package gate used by Filament resources
        abort_unless(Gate::allows('acl', 'scraper'), 403);

        $scraperId = $request->integer('scraper_id');

        $query = ScraperUrl::query()
            ->when($scraperId, fn ($q) => $q->where('scraper_id', $scraperId))
            ->orderBy('id');

        $filename = 'scraper_urls' . ($scraperId ? ('_scraper_' . $scraperId) : '') . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ];

        $columns = [
            'type', 'url', 'priority',
        ];

        $callback = function () use ($query, $columns) {
            $output = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fwrite($output, "\xEF\xBB\xBF");

            // Header row
            fputcsv($output, $columns);

            $query->chunk(1000, function ($rows) use ($output, $columns) {
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($columns as $col) {
                        $value = $row->{$col};
                        if ($value instanceof \Carbon\CarbonInterface) {
                            $value = $value->toDateTimeString();
                        }
                        $data[] = $value;
                    }
                    fputcsv($output, $data);
                }
            });

            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }
}
