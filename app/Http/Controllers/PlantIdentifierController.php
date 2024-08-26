<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantIdentifierController extends Controller
{
    public function identify(Request $request)
    {
        Log::info('Plant identification request received');

        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $image = $request->file('image');
        $imageData = base64_encode(file_get_contents($image));

        Log::info('Image processed successfully');

        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            Log::error('Gemini API key is missing');
            return response()->json(['error' => 'API configuration error'], 500);
        }

        try {
            Log::info('Sending request to Gemini API');
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => 'Identify this plant and provide important information about it, including its name, scientific name, family, description, care instructions, sunlight needs, plant health, and watering needs.',
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'safety_settings' => [
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_NONE',
                    ],
                ],
                'generation_config' => [
                    'temperature' => 0.4,
                    'top_p' => 1,
                    'top_k' => 32,
                    'max_output_tokens' => 2048,
                ],
            ]);

            Log::info('Received response from Gemini API', ['status' => $response->status()]);

            if (!$response->successful()) {
                Log::error('Gemini API request failed', ['status' => $response->status(), 'body' => $response->json()]);
                return response()->json(['error' => 'Failed to identify plant. API response: ' . $response->body()], 500);
            }

            $result = $response->json();
            Log::info('Processing Gemini API response', ['response' => $result]);
            $plantInfo = $this->processGeminiResponse($result);
            // $plantInfo = $result;

            return response()->json($plantInfo);

        } catch (\Exception $e) {
            Log::error('Error in plant identification', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function processGeminiResponse($response)
{
    // Extract the text from the Gemini API response
    $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Initialize an array to hold plant information
    $plantInfo = [
        'name' => '',
        'scientificName' => '',
        'family' => '',
        'descript' => '',
        'careInstructions' => '',
        'sunlightNeeds' => '',
        'wateringNeeds' => '',
        'plantHealth' => '',
        'additionalInfo' => '',
    ];

    // Parse the text to extract plant information
    $lines = explode("\n", $text);
    $currentSection = '';
    foreach ($lines as $line) {
        $line = trim($line);

        // Identify and set the current section
        if (stripos($line, 'Scientific Name:') !== false) {
            $plantInfo['scientificName'] = trim(str_replace('Scientific Name:', '', $line));
        } elseif (stripos($line, 'Family:') !== false) {
            $plantInfo['family'] = trim(str_replace('Family:', '', $line));
        } elseif (stripos($line, 'Description:') !== false) {
            $currentSection = 'descript';
        } elseif (stripos($line, 'Care Instructions:') !== false) {
            $currentSection = 'careInstructions';
        } elseif (stripos($line, 'Sunlight:') !== false) {
            $currentSection = 'sunlightNeeds';
        } elseif (stripos($line, 'Watering Needs:') !== false) {
            $currentSection = 'wateringNeeds';
        } elseif (stripos($line, 'Plant Health:') !== false) {
            $currentSection = 'plantHealth';
        } elseif (stripos($line, 'Additional Information:') !== false) {
            $currentSection = 'additionalInfo';
        } elseif ($currentSection) {
            // Append the content to the current section
            $plantInfo[$currentSection] .= $line . "\n";
        } else {
            // Assume the first non-empty line is the name of the plant
            if (empty($plantInfo['name']) && !empty($line)) {
                $plantInfo['name'] = $line;
            }
        }
    }

    // Trim any extra whitespace from each field
    foreach ($plantInfo as &$value) {
        $value = trim($value);
    }

    // Return the formatted plant information
    return $plantInfo;
}

}
