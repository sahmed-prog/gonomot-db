<?php

namespace App\Console\Commands\GM;

use App\Services\OpenAI\OpenAiRequest;
use Illuminate\Console\Command;
use App\Models\Surveys\PrimarySurveyEmbedding;
use Illuminate\Support\Facades\DB;
use Exception;

class GMGenerateResponseEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gm:generate-response-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch fields from primary_survey_details table and generate embeddings for non-null and non-empty values';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Fetch the required fields from the database
        $results = DB::select('
            SELECT ps.`user_id`, ps.`pm_candidate`, ps.`cm_1`, ps.`cm_2`, ps.`cm_3`, 
                   ps.`cm_4`, ps.`cm_5`, ps.`government`, ps.`surveyor_leadership`
            FROM `primary_survey_details` ps
        ');

        // List of fields to embed and their corresponding embedding column names
        $fieldsToEmbed = [
            'pm_candidate' => 'pm_candidate_embeddings',
            'cm_1' => 'cm_1_embeddings',
            'cm_2' => 'cm_2_embeddings',
            'cm_3' => 'cm_3_embeddings',
            'cm_4' => 'cm_4_embeddings',
            'cm_5' => 'cm_5_embeddings',
            'government' => 'government_embeddings',
            'surveyor_leadership' => 'surveyor_leadership_embeddings',
        ];

        // Process each user record
        foreach ($results as $result) {
            $embeddingsToUpdate = [];
            $textsToEmbed = [];
            $columnsToUpdate = [];

            foreach ($fieldsToEmbed as $field => $embeddingColumn) {
                $value = $result->$field;

                // Check if the value is non-null and non-empty
                if (!empty(trim($value))) {
                    $textsToEmbed[] = $value;
                    $columnsToUpdate[] = $embeddingColumn;
                }
            }

            if (!empty($textsToEmbed)) {
                try {
                    // Get embeddings for all valid fields
                    $embeddings = OpenAiRequest::getEmbeddings($textsToEmbed);

                    foreach ($embeddings as $index => $embedding) {
                        $embeddingsToUpdate[$columnsToUpdate[$index]] = json_encode($embedding);
                    }

                    // Update or insert the embeddings for this user
                    PrimarySurveyEmbedding::updateOrInsert(
                        ['user_id' => $result->user_id], // Match by user_id
                        $embeddingsToUpdate // Update all embeddings
                    );

                    $this->info("Successfully updated embeddings for user_id: {$result->user_id}");
                } catch (Exception $e) {
                    $this->error("Error while fetching embeddings for user_id {$result->user_id}: " . $e->getMessage());
                }
            } else {
                $this->info("Skipping user_id {$result->user_id} as no valid fields to embed.");
            }
        }

        return 0;
    }
}
