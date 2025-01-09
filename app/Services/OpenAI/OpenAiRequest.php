<?php 
namespace App\Services\OpenAI;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class OpenAiRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    
    public static function getEmbeddings(array $texts)
    {
        try {
            $client = new Client();
            $response = $client->post('https://api.openai.com/v1/embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_SECRET_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => env('OPENAI_EMBEDDING_MODEL'),
                    'input' => $texts,
                ]
            ]);
            if ($response->getStatusCode() !== 200) {
                $errorMessage = "Unexpected response code: " . $response->getStatusCode();
                throw new \Exception($errorMessage);
            }
            $responseBody = json_decode($response->getBody(), true);
            if (!isset($responseBody['data'])) {
                throw new \Exception("Response does not contain 'data'. Response: " . json_encode($responseBody));
            }

            return array_column($responseBody['data'], 'embedding');
            
        } catch (GuzzleException $e) {
            error_log('Guzzle error: ' . $e->getMessage() . ' | Request Data: ' . json_encode($texts));
            throw new \Exception('Error communicating with OpenAI API: ' . $e->getMessage());

        } catch (\Exception $e) {
            error_log('Error in fetchEmbeddingsFromOpenAI: ' . $e->getMessage());
            throw new \Exception('An error occurred while fetching embeddings: ' . $e->getMessage());
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public static function sendChat(string $role, string $content): array
    {
        $client = new Client();

        $response = $client->request('POST', 'open-ai/send-chat', [
            'json' => [
                'role'    => $role,
                'content' => $content,
            ]
        ]);

        if($response->getStatusCode() !== 200) {
            throw new Exception('client call failed');
        }

        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }
}