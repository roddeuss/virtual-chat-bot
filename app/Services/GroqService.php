<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Persona;
use App\Models\Memory;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
        $this->client = new Client();
    }

    public function generateResponse(Persona $persona, string $userMessage): string
    {
        // Fetch relevant memories to include in context
        $memories = Memory::all();
        $memoryContext = "";
        if ($memories->isNotEmpty()) {
            $memoryContext = "\n\nHal-hal yang kamu ingat tentang user:\n";
            foreach ($memories as $memory) {
                $memoryContext .= "- {$memory->key}: {$memory->value} (" . ($memory->context ?? '') . ")\n";
            }
        }

        $systemPrompt = $persona->system_prompt . $memoryContext;

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'verify' => false, // Fix for local dev SSL issues
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $userMessage
                        ]
                    ],
                    'temperature' => 0.7,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? 'Duh, aku lagi blank nih. Coba lagi ya?';
        } catch (\Exception $e) {
            Log::error('Groq API Error: ' . $e->getMessage());
            return 'Gagal konek ke Groq nih. Cek API Key atau koneksi internet kamu ya!';
        }
    }
}
