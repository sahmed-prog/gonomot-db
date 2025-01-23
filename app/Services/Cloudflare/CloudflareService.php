<?php 
namespace App\Services\Cloudflare;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 

class CloudflareService
{
    protected $client;
    protected $apiUrl;
    protected $authToken;
    protected $accountId;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://api.cloudflare.com/client/v4/accounts/';
        $this->authToken = env('CLOUDFLARE_WORKER_API');
        $this->accountId = env('CLOUDFLARE_ACCOUNT_ID');
    }

    /**
     * List vectorize indexes
     *
     * @return mixed
     */
    public function listVectorizeIndexes()
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes";
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Create a new vectorize index
     *
     * @param array $data
     * @return mixed
     */
    public function createVectorizeIndex($name, $description, $dimensions = 1536, $metric = 'cosine')
    {
        $data = [
            'config' => [
                'dimensions' => $dimensions,
                'metric' => $metric,
            ],
            'description' => $description,
            'name' => $name,
        ];

        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes";

        $response = $this->client->post($url, [
            'json' => $data,
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Delete a vectorize index
     *
     * @param string $indexName
     * @return mixed
     */
    public function deleteVectorizeIndex(string $indexName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}";
        $response = $this->client->delete($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get details of a vectorize index
     *
     * @param string $indexName
     * @return mixed
     */
    public function getVectorizeIndex(string $indexName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}";
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Delete vectors by identifier
     *
     * @param string $indexName
     * @param array $ids
     * @return mixed
     */
    public function deleteVectorsByIdentifier(string $indexName, array $ids)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/delete_by_ids";
        $response = $this->client->post($url, [
            'json' => ['ids' => $ids],
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get vectors by identifier
     *
     * @param string $indexName
     * @param array $ids
     * @return mixed
     */
    public function getVectorsByIdentifier(string $indexName, array $ids)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/get_by_ids";
        $response = $this->client->post($url, [
            'json' => ['ids' => $ids],
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get vectorize index info
     *
     * @param string $indexName
     * @return mixed
     */
    public function getVectorizeIndexInfo(string $indexName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/info";
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
    public function insertVectors(string $indexName, array $vectors)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/insert";
        
        // Send the object directly as JSON in the request body
        $response = $this->client->post($url, [
            'json' => $vectors, // $vectors is an array that will be serialized into JSON
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/x-ndjson', // Ensure the correct Content-Type for NDJSON
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function createMetadataIndex(string $indexName, string $propertyName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/metadata_index/create";
        $response = $this->client->post($url, [
            'json' => [
                'indexType'   => 'string',
                'propertyName' => $propertyName
            ],
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function deleteMetaDataIndex(string $indexName, string $propertyName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/metadata_index/delete";
        $response = $this->client->post($url, [
            'json' => [
                'propertyName' => $propertyName
            ],
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
    public function listMetaDataIndexes(string $indexName)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/metadata_index/list";
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/json',
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    
    public function queryVectors(string $indexName, int $topK, string $returnMetadata,array $vector)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/query";
        $startTime = microtime(true);
        
        // Prepare the request body with dynamic parameters
        $payload = [
            'topK' => $topK,  // Dynamic topK
            'vector' => $vector,  // The vector you are sending
            'returnMetadata' => $returnMetadata,  // The string value for returnMetadata
            'returnValues' => true,
        ];
    
        // Send the POST request
        $response = $this->client->post($url, [
            'json' => $payload,
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/json',
            ]
        ]);
         // End timing the request
        $endTime = microtime(true);

         // Calculate response time
        $responseTime = $endTime - $startTime;
        $responseData = json_decode($response->getBody()->getContents(), true);
        return [
            'response_time' => $responseTime,
            'results' => $responseData,
        ];
    
        // Return the response content
        return json_decode($response->getBody()->getContents());
    }
    public function queryVectorsWorkers($vector, $top_k)
    {
        // Cloudflare Worker URL
        $worker_url = "https://withered-sea-cbf7.careerone.workers.dev/api/vectors/query";
        $startTime = microtime(true);

        // Prepare the payload
        $payload = [
            'vector' => $vector,  // Vector to query
            'top_k' => $top_k     // Number of results to return
        ];

        try {
            // Create a Guzzle client instance
            $client = new Client();

            // Make the API request to the Cloudflare Worker
            $response = $client->post($worker_url, [
                'json' => $payload,  // Send payload as JSON
                'headers' => [
                    'Authorization' => 'Bearer ' . env('AUTH_TOKEN'),  // Authorization token from .env
                    'Content-Type' => 'application/json',
                ]
            ]);
            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            // Get the response body as an associative array
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Check if results are returned
            return [
                'response_time' => $responseTime,
                'result' => $responseData,
            ];
        
            // Return the response content
            return json_decode($response->getBody()->getContents());

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle Guzzle request exception
            return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle other general exceptions
            return response()->json(['error' => 'Exception occurred: ' . $e->getMessage()], 500);
        }
    }

    public function queryVectorsCompare(string $indexName, int $topK, string $returnMetadata, bool $returnValues ,array $vector)
    {
        $url = $this->apiUrl . "{$this->accountId}/vectorize/v2/indexes/{$indexName}/query";
        $startTime = microtime(true);
        
        // Prepare the request body with dynamic parameters
        $payload = [
            'topK' => $topK,  // Dynamic topK
            'vector' => $vector,  // The vector you are sending
            'returnMetadata' => $returnMetadata,  // The string value for returnMetadata
            'returnValues' => $returnValues,
        ];
    
        // Send the POST request
        $response = $this->client->post($url, [
            'json' => $payload,
            'headers' => [
                'Authorization' => "Bearer {$this->authToken}",
                'Content-Type'  => 'application/json',
            ]
        ]);
         // End timing the request
        $endTime = microtime(true);

         // Calculate response time
        $responseTime = $endTime - $startTime;
        $responseData = json_decode($response->getBody()->getContents(), true);
        return [
            'response_time' => $responseTime,
            'results' => $responseData,
        ];
    
        // Return the response content
        return json_decode($response->getBody()->getContents());
    }
    
    // manually successful
    public function upsertVectors(string $indexName, string $ndjsonData)
    {
        // Path to your NDJSON file
        $filePath = '/home/shakilahmed/c1/admin-api/vector-data.ndjson';  // Update with your actual path
    
        // Read the contents of the NDJSON file
        // $ndjsonData = file_get_contents($filePath);
    
        // Output the contents to the console for verification
        // echo $ndjsonData; 350187_0
    
        $curl = curl_init();
    
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/vectorize/v2/indexes/{$indexName}/upsert",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $ndjsonData,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->authToken}",
                "Content-Type: application/x-ndjson"
            ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }

}
