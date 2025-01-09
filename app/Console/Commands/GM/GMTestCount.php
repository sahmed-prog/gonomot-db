<?php

namespace App\Console\Commands\GM;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GMTestCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gm:test-count {--start_date=} {--end_date=}';
    // test push

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a GuzzleHttp request to fetch count from the API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseUrl = 'https://gonomot.org/api/';
        $endpoint = 'count';
        $apiToken = '8|hIDbG3IzClaNje5bYZ28iHouDFCREYXd6BD1eXBQc27157c9';

        $queryParams = [];
        $startDate = $this->option('start_date');
        $endDate = $this->option('end_date');

        if ($startDate) {
            $queryParams['start_date'] = date('d-m-Y', strtotime($startDate));
        }

        if ($endDate) {
            $queryParams['end_date'] = date('d-m-Y', strtotime($endDate));
        }

        $this->info('Generated URL: ' . $baseUrl . $endpoint . '?' . http_build_query($queryParams));


        $client = new Client([
            'base_uri' => $baseUrl,
        ]);

        try {
            $response = $client->request('GET', $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'query' => $queryParams,
            ]);

            $data = json_decode($response->getBody(), true);
            dd($data);
            $this->info('Response Data:');
            print_r($data);
        } catch (RequestException $e) {
            $this->error('Error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                $this->error('Response: ' . $e->getResponse()->getBody());
            }
        }

        return 0;
    }
}
