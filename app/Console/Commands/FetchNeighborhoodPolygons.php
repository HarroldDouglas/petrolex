<?php

namespace App\Console\Commands;

use App\Models\Geography\Neighborhood;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchNeighborhoodPolygons extends Command
{
    protected $signature = 'neighborhoods:fetch-polygons
                            {--id= : Fetch polygon for a specific neighborhood ID}
                            {--force : Overwrite existing polygons}';

    protected $description = 'Fetch polygon boundaries from OpenStreetMap Nominatim for neighborhoods';

    public function handle(): int
    {
        $query = Neighborhood::with('municipality.city');

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        } elseif (! $this->option('force')) {
            $query->whereNull('polygon');
        }

        $neighborhoods = $query->get();

        if ($neighborhoods->isEmpty()) {
            $this->info('No neighborhoods to process.');

            return self::SUCCESS;
        }

        $this->info("Processing {$neighborhoods->count()} neighborhood(s)...");
        $bar = $this->output->createProgressBar($neighborhoods->count());
        $bar->start();

        $success = 0;
        $failed = [];

        foreach ($neighborhoods as $neighborhood) {
            $cityName = $neighborhood->municipality?->city?->name ?? '';
            $polygon = $this->fetchPolygon($neighborhood->name, $cityName);

            if ($polygon) {
                $neighborhood->update(['polygon' => $polygon]);
                $success++;
            } else {
                $failed[] = "{$neighborhood->name} (id={$neighborhood->id})";
            }

            $bar->advance();
            usleep(500000); // respect Nominatim rate limit (1 req/sec)
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. {$success}/{$neighborhoods->count()} polygon(s) fetched.");

        if (! empty($failed)) {
            $this->warn('Failed to fetch polygons for:');
            foreach ($failed as $name) {
                $this->line("  - {$name}");
            }
        }

        return self::SUCCESS;
    }

    private function fetchPolygon(string $neighborhoodName, string $cityName): ?array
    {
        $searchQuery = $cityName
            ? "{$neighborhoodName}, {$cityName}, Cameroon"
            : "{$neighborhoodName}, Cameroon";

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Petrolex/1.0 (contact@isogaz.net)',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $searchQuery,
                'format' => 'geojson',
                'polygon_geojson' => 1,
                'limit' => 1,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $features = $response->json('features');

            if (empty($features)) {
                return null;
            }

            $geometry = $features[0]['geometry'] ?? null;

            if (! $geometry) {
                return null;
            }

            // Accept Polygon or MultiPolygon
            if (! in_array($geometry['type'], ['Polygon', 'MultiPolygon'])) {
                return null;
            }

            return $geometry;
        } catch (\Exception $e) {
            $this->warn("Error fetching polygon for '{$neighborhoodName}': {$e->getMessage()}");

            return null;
        }
    }
}
