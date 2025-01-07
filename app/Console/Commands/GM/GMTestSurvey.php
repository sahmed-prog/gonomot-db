<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
        $baseUrl = 'https://gonomot.anvs.xyz/api/';
        $endpoint = 'survey';
        $apiToken = '9|ZipJpnbit2tF8R9asp097oXaWCx9kH00DNkyfJfcb6771b64';
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
            $surveys = $responseData['data']['surveys'];

            // Arrays to hold data for each table
            $surveysTableData = [];
            $surveyDetailsTableData = [];
            $cabinetMembersTableData = [];
            $qualitiesTableData = [];
            $occupationsTableData = [];
            $electoralAreasTableData = [];
            $leadershipRolesTableData = [];

            foreach ($surveys as $survey) {
                // Extract survey data
                $surveysTableData[] = [
                    'id' => $survey['id'],
                    'user_id' => $survey['user_id'],
                    'referral_code' => $survey['referral_code'],
                    'survey_date' => $survey['survey_date'],
                ];

                // Extract survey details
                foreach ($survey['survey_details'] as $key => $detail) {
                    if (strpos($key, 'cabinet_member') === 0) {
                        // Extract cabinet member data
                        $cabinetMembersTableData[] = [
                            'id' => $detail['id'],
                            'survey_id' => $survey['id'],
                            'name' => $detail['name'],
                            'quality_id' => $detail['quality_1']['id'],
                        ];

                        // Extract qualities data
                        $qualitiesTableData[] = [
                            'id' => $detail['quality_1']['id'],
                            'name' => $detail['quality_1']['name'],
                        ];
                    } elseif ($key === 'surveyor_occupation') {
                        // Extract occupation data
                        $occupationsTableData[] = [
                            'id' => $detail['occupation']['id'],
                            'title_en' => $detail['occupation']['title_en'],
                        ];
                    } elseif ($key === 'surveyor_electoral_area') {
                        // Extract electoral area data
                        $electoralAreasTableData[] = [
                            'id' => $detail['constituency']['id'],
                            'title_en' => $detail['constituency']['title_en'],
                        ];
                    } elseif ($key === 'surveyor_leadership') {
                        // Extract leadership role data
                        $leadershipRolesTableData[] = [
                            'id' => $detail['id'],
                            'name' => $detail['name'],
                        ];
                    } else {
                        // General survey details
                        $surveyDetailsTableData[] = [
                            'id' => $detail['id'],
                            'survey_id' => $survey['id'],
                            'question_key' => $detail['question_key'],
                            'name' => $detail['name'] ?? null,
                            'govt_system_id' => $detail['govt_system_id'] ?? null,
                            'is_other_govt_system' => $detail['is_other_govt_system'] ?? null,
                        ];
                    }
                }
            }

            // Display extracted data for debugging
            $this->info('Surveys Table Data: ' . print_r($surveysTableData, true));
            $this->info('Survey Details Table Data: ' . print_r($surveyDetailsTableData, true));
            $this->info('Cabinet Members Table Data: ' . print_r($cabinetMembersTableData, true));
            $this->info('Qualities Table Data: ' . print_r($qualitiesTableData, true));
            $this->info('Occupations Table Data: ' . print_r($occupationsTableData, true));
            $this->info('Electoral Areas Table Data: ' . print_r($electoralAreasTableData, true));
            $this->info('Leadership Roles Table Data: ' . print_r($leadershipRolesTableData, true));

        } catch (RequestException $e) {
            $this->error('Error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                $this->error('Response: ' . $e->getResponse()->getBody());
            }
        }

        return 0;
    }

}
