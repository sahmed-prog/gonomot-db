<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class StartDateFinder extends Command
{
    protected $signature = 'gm:start-date-finder';
    protected $description = 'Find the first survey response date after 01-10-2023';

    public function handle()
    {
        $baseUrl = 'https://gonomot.org/api/';
        $endpoint = 'count';
        $apiToken = '8|hIDbG3IzClaNje5bYZ28iHouDFCREYXd6BD1eXBQc27157c9';

        $client = new Client(['base_uri' => $baseUrl]);

        // Set initial start date
        $startDate = Carbon::createFromFormat('d-m-Y', '01-10-2023');
        // Initial end date set to 08-10-2024
        $endDate = Carbon::createFromFormat('d-m-Y', '08-10-2024');

        // Loop and increase end_date by one day until a non-zero response is found
        while (true) {
            $this->info("Searching from {$startDate->format('d-m-Y')} to {$endDate->format('d-m-Y')}");

            // Make the API call
            try {
                $response = $client->request('GET', $endpoint, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiToken,
                    ],
                    'query' => [
                        'start_date' => $startDate->format('d-m-Y'),
                        'end_date' => $endDate->format('d-m-Y'),
                    ],
                ]);

                // Decode the response
                $data = json_decode($response->getBody(), true);

                // Print the number of responses for this date range
                if (isset($data['data']['total_surveyed_results'])) {
                    $totalResponses = $data['data']['total_surveyed_results'];
                    $this->info("Number of responses from {$startDate->format('d-m-Y')} to {$endDate->format('d-m-Y')}: {$totalResponses}");

                    // If the responses are not zero, print and exit the loop
                    if ($totalResponses > 0) {
                        $this->info("First non-zero response found for the date range: {$startDate->format('d-m-Y')} to {$endDate->format('d-m-Y')}");
                        break;
                    }
                }

            } catch (RequestException $e) {
                $this->error('Error: ' . $e->getMessage());

                if ($e->hasResponse()) {
                    $this->error('Response: ' . $e->getResponse()->getBody());
                }
            }

            // Increase the end date by one day
            $endDate->addDay();
        }

        $this->info("Found a non-zero response. Ending search.");
        return 0;
    }
}
