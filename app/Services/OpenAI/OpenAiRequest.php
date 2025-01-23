<?php 
namespace App\Services\OpenAI;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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

    public function createAIAgent($rawText)
    {
        $prompt = "
            You are an AI agent whose job is to determine a list of public personalities and their designations (if available) from a jumbled text field I will provide you.
            The text field may contain episode names, names of people (may or may not be comma-separated), their designations, and a URL to a YouTube video.
            You will ignore the episode names, timings of those episodes, and the video links. You will only focus on extracting the names of the people and their designations, and you will provide them in a strict JSON structure.

            Please ensure your response follows this exact JSON structure:
            {
                \"people\": [
                    {
                        \"name\": \"<name of the person>\",
                        \"designation\": \"<designation of the person or null if not found>\"
                    },
                    ...
                ]
            }

            Process the data below...

            Raw text: {$rawText}
            Now, process the data and respond in the exact JSON format as specified above, ensuring all extracted names and designations are included under the 'people' array.
        ";

        $response = $this->fetchChatResponseFromOpenAI($prompt, 'gpt-4o-mini'); 
        return $response;
    }


    private function fetchChatResponseFromOpenAI($prompt, $model)
    {
        $client = new Client();
        try {
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_SECRET_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => [
                        'type' => 'json_object'
                    ]
                ],
            ]);
            // dd($response);
            
            $body = $response->getBody()->getContents();
            $decodedResponse = json_decode($body, true);
            // dd($decodedResponse);
            if (isset($decodedResponse['choices'][0]['message'])) {
            $messageArray = $decodedResponse['choices'][0]['message'];
            if (isset($messageArray['content'])) {
                $content = $messageArray['content'];
                return $content;
            }
        } else {
            Log::error('OpenAI structuring error');
        }
    
        } catch (\Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            return null;
        }
    }
}