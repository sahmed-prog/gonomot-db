<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use App\Services\OpenAI\OpenAiRequest;
use App\Services\Cloudflare\CloudflareService;

class QueryVectors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gm:query-vectors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for text and query vector search';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Step 1: Ask for user input
        $textEntry = $this->ask('Please enter the text for which you want to run the Facebook AI Search System');
    
        // Step 2: Validate the input
        if (empty($textEntry)) {
            $this->error('Input cannot be empty. Please provide valid text.');
            return 1; // Exit with error code
        }
    
        // Step 3: Transform the text entry into an array
        $texts = [$textEntry];
    
        // Step 4: Get the OpenAI service instance
        $openAiRequest = app(OpenAiRequest::class);
    
        try {
            // Step 5: Fetch embeddings for the text entry
            $embeddings = $openAiRequest->getEmbeddings($texts);
    
            // Step 6: Extract the vector (assume it's the first embedding)
            $vector = $embeddings[0];
    
            // Step 7: Call the queryVectors function
            $cloudflareService = app(CloudflareService::class);
    
            $indexName = 'gm-names-2';
            $topK = 20;
            $returnMetadata = 'all';
    
            $queryResponse = $cloudflareService->queryVectors(
                $indexName,
                $topK,
                $returnMetadata,
                $vector
            );
    
            $results = $queryResponse['results']['result']['matches'] ?? null;
    
            if ($results) {
                // Extract the required data
                $extractedData = array_map(function ($item) {
                    return [
                        'id' => $item['id'] ?? null,
                        'score' => $item['score'] ?? null,
                        'metadata' => $item['metadata'] ?? null,
                    ];
                }, $results);
    
                // Print the table
                $this->info(str_pad("ID", 10) . str_pad("kNN score", 15) . str_pad("Name", 30) . "Designation");
                $this->info(str_repeat("-", 80));
    
                foreach ($extractedData as $data) {
                    $id = $data['id'] ?? 'N/A';
                    $score = number_format($data['score'] ?? 0, 6);
                    $name = $data['metadata']['name'] ?? 'N/A';
                    $designation = $data['metadata']['designation'] ?? 'N/A';
    
                    $this->line(
                        str_pad($id, 10) .
                        str_pad($score, 15) .
                        str_pad($name, 30) .
                        $designation
                    );
                }
            } else {
                $this->warn('No matches found.');
            }
    
        } catch (\Exception $e) {
            // Handle errors
            $this->error('An error occurred: ' . $e->getMessage());
        }
    
        return 0; // Exit with success code
    }
    
}
