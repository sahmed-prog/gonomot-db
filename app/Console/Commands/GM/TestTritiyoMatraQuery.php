<?php

namespace App\Console\Commands\GM;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\OpenAI\OpenAiRequest;

class TestTritiyoMatraQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tritiyo-matra-query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test query for tmp_tritiyo_matra table';

    protected $openAiService;

    public function __construct(OpenAiRequest $openAiService)
    {
        parent::__construct();
        $this->openAiService = $openAiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Fetch all guest_details from the database
            $results = DB::select("SELECT tm.`id`, tm.`guest_details` FROM `tmp_tritiyo_matra` tm where tm.`id` between 6201 and 6742");

            // Initialize the progress bar
            $this->output->progressStart(count($results));

            // Iterate through results
            foreach ($results as $result) {
                $rawText = $result->guest_details;

                // Send the guest_details to the AI agent
                $response = $this->openAiService->createAIAgent($rawText);

                // Decode the response
                $decodedResponse = json_decode($response, true);
                // dd($decodedResponse);

                // Insert each name and designation into the tmp_persona_taxonomy table
                if (is_array($decodedResponse) && isset($decodedResponse['people']) && is_array($decodedResponse['people'])) {
                    foreach ($decodedResponse['people'] as $person) {
                        DB::table('tmp_persona_taxonomy')->insert([
                            'persona_name' => $person['name'],  // Allow null names
                            'designation' => $person['designation'] ?? null,
                        ]);
                    }
                } else {
                    $this->error('Unexpected response format: ' . json_encode($decodedResponse));
                }                

                // Advance the progress bar
                $this->output->progressAdvance();
            }

            // Finish the progress bar
            $this->output->progressFinish();

            $this->info('All data processed successfully.');

        } catch (\Exception $e) {
            // Log and display errors
            $this->error('Error executing query or processing AI: ' . $e->getMessage());
        }
    }
}
