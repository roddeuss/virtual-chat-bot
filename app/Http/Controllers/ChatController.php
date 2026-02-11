<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Message;
use App\Models\Memory;
use App\Services\GroqService;

class ChatController extends Controller
{
    protected $groqService;

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    public function index()
    {
        $personas = Persona::all();
        $activePersona = Persona::where('slug', 'romantic')->first();
        if (!$activePersona && $personas->isNotEmpty()) {
            $activePersona = $personas->first();
        }
        
        $messages = $activePersona ? Message::where('persona_id', $activePersona->id)->orderBy('created_at', 'asc')->get() : collect();
        
        return view('chat', compact('personas', 'activePersona', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'persona_id' => 'required|exists:personas,id',
        ]);

        $persona = Persona::findOrFail($request->persona_id);
        $userMessage = $request->message;

        // Simple memory extraction logic
        if (str_contains(strtolower($userMessage), 'favoritku adalah ')) {
            $parts = explode('favoritku adalah ', strtolower($userMessage));
            if (isset($parts[1])) {
                $thing = trim($parts[1]);
                Memory::updateOrCreate(
                    ['key' => 'hal_favorit'],
                    ['value' => $thing, 'context' => 'Diberi tahu user dalam chat']
                );
            }
        }

        // 2. Generate AI Response
        $botResponse = $this->groqService->generateResponse($persona, $userMessage);

        $message = Message::create([
            'persona_id' => $persona->id,
            'user_message' => $userMessage,
            'bot_response' => $botResponse,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function switchPersona($slug)
    {
        $persona = Persona::where('slug', $slug)->firstOrFail();
        $messages = Message::where('persona_id', $persona->id)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'persona' => $persona,
            'messages' => $messages
        ]);
    }
}
