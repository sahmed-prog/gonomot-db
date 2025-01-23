<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Cloudflare\CloudflareService;
use App\Services\OpenAI\OpenAiRequest;

class CloudflareTest extends Command
{
    protected $signature = 'cloudflare:test';
    protected $description = 'Test Cloudflare vectorize indexes and query persona data';

    protected $cloudflareService;
    protected $openAiRequest;

    // Inject dependencies
    public function __construct(CloudflareService $cloudflareService, OpenAiRequest $openAiRequest)
    {
        parent::__construct();
        $this->cloudflareService = $cloudflareService;
        $this->openAiRequest = $openAiRequest;
    }

    public function handle()
    {
        $this->info('Fetching persona data from the database...');

        // Execute the SQL query to get persona data
        $personas = DB::select('
            SELECT
                tpt.`id`,
                CONCAT(tpt.`persona_name`, ", ", tpt.`designation`) AS persona_name_designation,
                JSON_OBJECT(
                    \'data_id\', tpt.`id`,
                    \'name\', tpt.`persona_name`,
                    \'designation\', tpt.`designation`
                ) AS metadata
            FROM 
                `tmp_persona_taxonomy` tpt
            WHERE tpt.`id` BETWEEN 1 AND 1000
        ');

        // Extract persona_name_designation values
        $texts = [];
        foreach ($personas as $persona) {
            $texts[] = $persona->persona_name_designation;
        }

        // Get embeddings for all persona_name_designation values
        $embeddings = $this->openAiRequest->getEmbeddings($texts);

        // Create JSON objects with id, values (embeddings), and metadata
        $jsonObjects = [];
        foreach ($personas as $index => $persona) {
            if (!isset($embeddings[$index]) || !$embeddings[$index]) {
                continue; // Skip invalid embeddings
            }

            $jsonObjects[] = [
                'id' => $persona->id,
                'values' => $embeddings[$index], // Directly use the embedding value
                'metadata' => json_decode($persona->metadata, true), // Decode metadata JSON object
            ];
        }

        // Chunk the data and upload to Cloudflare in batches
        $batchSize = 1000;
        $chunks = collect($jsonObjects)->chunk($batchSize);
        $totalChunks = $chunks->count();
        $progressBar = $this->output->createProgressBar($totalChunks);

        $this->info("Uploading embeddings to Cloudflare in $totalChunks chunks...");

        foreach ($chunks as $chunk) {
            // Transform the chunk to NDJSON format
            $resultToUpload = $this->transformToJson($chunk->toArray());
            // dd($resultToUpload);

            // Upsert data to Cloudflare
            $response = $this->cloudflareService->upsertVectors('gm-names-2', $resultToUpload);
            $this->info($response);

            // Advance progress bar
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('All embeddings have been indexed successfully!');
    }

    /**
     * Transform chunk data into NDJSON format.
     *
     * @param array $chunk
     * @return string
     */
    private function transformToJson(array $chunk): string
    {
        return collect($chunk)->map(function ($item) {
            // Ensure the values are properly encoded and are not null
            return json_encode([
                'id' => $item['id'],
                'values' => $item['values'],
            ]);
        })->implode("\n");
    }
}
