<?php

namespace App\Agents\Core;

use App\Agents\Contracts\AgentInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent implements AgentInterface
{
    protected string $model = 'gemini-1.5-flash'; // Default model, can be overridden
    protected float $temperature = 0.5;

    /**
     * Utility method to communicate with the LLM API.
     * Uses Laravel's HTTP Client. Configured for Google Gemini.
     *
     * @param string $systemPrompt
     * @param string $userMessage
     * @return string|null
     */
    protected function callLLM(string $systemPrompt, string $userMessage): ?string
    {
        // For a production ERP, these keys should be in .env and config/services.php
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::warning("Agent attempt blocked: GEMINI_API_KEY is missing in .env");
            // Placeholder return for demonstration/testing without an API key
            return json_encode([
                'status' => 'mocked',
                'recommendation' => 'This is a mocked response because no API key is set.',
                'raw_context' => $userMessage
            ]);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}";

            $payload = [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature
                ]
            ];

            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error("LLM API Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("LLM Request Failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Default implementation of act method. Subclasses can override this.
     */
    public function act($analysisResult): bool
    {
        // By default, a passive agent doesn't act, it only analyzes.
        return true;
    }
}
