<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Surveys\PrimarySurvey;
use Carbon\Carbon;

class GMTestSurvey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gm:test-survey {--page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a GuzzleHttp request to fetch survey data from the API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseUrl = 'https://gonomot.org/api/';
        $endpoint = 'survey';
        $apiToken = '8|hIDbG3IzClaNje5bYZ28iHouDFCREYXd6BD1eXBQc27157c9';
        $page = $this->option('page');
        $queryParams = ['page' => $page];

        $this->info('Generated URL: ' . $baseUrl . $endpoint . '?' . http_build_query($queryParams));

        $client = new Client(['base_uri' => $baseUrl]);

        try {
            $response = $client->request('GET', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'query' => $queryParams,
            ]);

            $responseData = json_decode($response->getBody(), true);
            $totalPages = $responseData['data']['total_page'];  // Total pages available
            $currentPage = $responseData['data']['current_page'];  // Current page number

            // Extracting surveys from the response
            $surveys = $responseData['data']['surveys'];

            // Array to hold the extracted survey objects
            $extractedSurveyObjects = [];

            foreach ($surveys as $survey) {
                // Extract the user_id
                $userId = $survey['user_id'];

                // Extract the government name from survey_details
                $governmentName = $survey['survey_details']['government']['name'] ?? null;
                $cm_1 = $survey['survey_details']['cabinet_member_1']['name'] ?? null;
                $cm_2 = $survey['survey_details']['cabinet_member_2']['name'] ?? null;
                $cm_3 = $survey['survey_details']['cabinet_member_3']['name'] ?? null;
                $cm_4 = $survey['survey_details']['cabinet_member_4']['name'] ?? null;
                $cm_5 = $survey['survey_details']['cabinet_member_5']['name'] ?? null;
                $pm_candidate = $survey['survey_details']['pm_candidate']['name'] ?? null;
                $surveyor_electoral_area = $survey['survey_details']['surveyor_electoral_area']['constituency']['title_en'] ?? null;
                $surveyor_administrative = $survey['survey_details']['surveyor_administrative']['name'] ?? null;
                $surveyor_occupation = $survey['survey_details']['surveyor_occupation']['occupation']['title_en'] ?? null;
                $surveyor_leadership = $survey['survey_details']['surveyor_leadership']['name'] ?? null;
                $referral_code = $survey['referral_code'] ?? null;
                $survey_date = $survey['survey_date'] ?? null;
                if ($survey_date) {
                    $survey_date = Carbon::createFromFormat('d-m-Y H:i:s', $survey_date)->format('Y-m-d H:i:s');
                }
                $extractedSurveyObject = [
                    'user_id' => $userId,
                    'government' => $governmentName,
                    'cm_1' => $cm_1,
                    'cm_2' => $cm_2,
                    'cm_3' => $cm_3,
                    'cm_4' => $cm_4,
                    'cm_5' => $cm_5,
                    'pm_candidate' => $pm_candidate,
                    'surveyor_electoral_area' => $surveyor_electoral_area,
                    'surveyor_administrative' => $surveyor_administrative,
                    'surveyor_occupation' => $surveyor_occupation,
                    'surveyor_leadership' => $surveyor_leadership,
                    'referral_code' => $referral_code,
                    'survey_date' => $survey_date,
                    'ingested_at' => Carbon::now(),
                ];
                PrimarySurvey::updateOrInsert(
                    ['user_id' => $userId],
                    $extractedSurveyObject 
                );
            }

            // Check if we have more pages to fetch
            if ($currentPage < $totalPages) {
                $this->info("Fetching page {$currentPage} of {$totalPages}...");
                $this->call('gm:test-survey', ['--page' => $currentPage + 1]);
            } else {
                $this->info("Reached the last page: {$currentPage} of {$totalPages}");
            }

        } catch (RequestException $e) {
            $this->error('Error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                $this->error('Response: ' . $e->getResponse()->getBody());
            }
        }

        return 0;
    }
}
