<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Chat Bot - Your Personal Companion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full h-[85vh] glass rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

        <!-- Sidebar: Personas -->
        <div class="w-full md:w-80 bg-white/40 border-r border-white/20 p-6 flex flex-col">
            <h1
                class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-pink-600 bg-clip-text text-transparent mb-8">
                Virtual AI for Elisabeth</h1>

            <div class="space-y-4 flex-1 overflow-y-auto scrollbar-hide">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pilih Persona</p>
                @foreach ($personas as $persona)
                    <button onclick="switchPersona('{{ $persona->slug }}')"
                        class="persona-btn w-full text-left p-4 rounded-2xl transition-all duration-300 {{ $activePersona->id == $persona->id ? 'bg-white shadow-md border-white' : 'hover:bg-white/50' }}"
                        data-slug="{{ $persona->slug }}">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-400 to-pink-400 flex items-center justify-center text-xl">
                                {{ $persona->avatar_url ?? substr($persona->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 text-sm">{{ $persona->name }}</h3>
                                <p class="text-[10px] text-gray-500 line-clamp-1">{{ $persona->description }}</p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-8 p-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <p class="text-xs opacity-80 mb-1">Status Bot</p>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <p class="text-sm font-medium">Andreas AI </p>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col relative">
            <header class="p-6 border-b border-white/20 flex items-center justify-between">
                <div>
                    <h2 id="active-persona-name" class="font-bold text-gray-800">{{ $activePersona->name }}</h2>
                    <p id="active-persona-desc" class="text-xs text-gray-500 italic">{{ $activePersona->description }}
                    </p>
                </div>
            </header>

            <div id="chat-messages" class="flex-1 p-6 overflow-y-auto space-y-6 scrollbar-hide">
                @foreach ($messages as $msg)
                    <!-- User Message -->
                    <div class="flex justify-end">
                        <div class="max-w-[80%] bg-indigo-600 text-white p-4 rounded-2xl rounded-tr-none shadow-lg">
                            <p class="text-sm">{{ $msg->user_message }}</p>
                        </div>
                    </div>
                    <!-- Bot Message -->
                    <div class="flex justify-start">
                        <div
                            class="max-w-[80%] bg-white p-4 rounded-2xl rounded-tl-none shadow-md border border-gray-100">
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $msg->bot_response }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-6 border-t border-white/20">
                <form id="chat-form" onsubmit="sendMessage(event)" class="relative">
                    <input type="hidden" id="persona_id" value="{{ $activePersona->id }}">
                    <input type="text" id="user-input" placeholder="Type your message..."
                        class="w-full bg-white/50 border border-white/40 rounded-2xl py-4 px-6 pr-16 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all placeholder-gray-400">
                    <button type="submit"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-xl transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Loading Indicator -->
            <div id="loading"
                class="hidden absolute inset-0 bg-white/40 backdrop-blur-[2px] flex items-center justify-center">
                <div class="flex space-x-2">
                    <div class="w-3 h-3 bg-indigo-600 rounded-full animate-bounce"></div>
                    <div class="w-3 h-3 bg-purple-600 rounded-full animate-bounce [animation-delay:-.3s]"></div>
                    <div class="w-3 h-3 bg-pink-600 rounded-full animate-bounce [animation-delay:-.5s]"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');
        const loading = document.getElementById('loading');
        const personaIdField = document.getElementById('persona_id');

        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;

        async function sendMessage(e) {
            e.preventDefault();
            const message = userInput.value.trim();
            if (!message) return;

            const personaId = personaIdField.value;

            // Append user message to UI
            appendMessage(message, 'user');
            userInput.value = '';
            loading.classList.remove('hidden');

            try {
                const response = await axios.post('{{ route('chat.send') }}', {
                    message: message,
                    persona_id: personaId,
                    _token: '{{ csrf_token() }}'
                });

                if (response.data.status === 'success') {
                    appendMessage(response.data.message.bot_response, 'bot');
                }
            } catch (error) {
                console.error(error);
                appendMessage('Waduh, ada masalah koneksi nih. Coba lagi ya!', 'bot');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function appendMessage(text, type) {
            const div = document.createElement('div');
            div.className = `flex ${type === 'user' ? 'justify-end' : 'justify-start'}`;

            const innerDiv = document.createElement('div');
            innerDiv.className = `max-w-[80%] p-4 rounded-2xl shadow-lg ${
                type === 'user' 
                ? 'bg-indigo-600 text-white rounded-tr-none' 
                : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
            }`;

            const p = document.createElement('p');
            p.className = 'text-sm whitespace-pre-line';
            p.textContent = text;

            innerDiv.appendChild(p);
            div.appendChild(innerDiv);
            chatContainer.appendChild(div);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        async function switchPersona(slug) {
            loading.classList.remove('hidden');
            try {
                const response = await axios.get(`/persona/${slug}`);
                const data = response.data;

                // Update UI state
                document.getElementById('active-persona-name').textContent = data.persona.name;
                document.getElementById('active-persona-desc').textContent = data.persona.description;
                document.getElementById('persona_id').value = data.persona.id;

                // Update Button active state
                document.querySelectorAll('.persona-btn').forEach(btn => {
                    if (btn.dataset.slug === slug) {
                        btn.classList.add('bg-white', 'shadow-md', 'border-white');
                        btn.classList.remove('hover:bg-white/50');
                    } else {
                        btn.classList.remove('bg-white', 'shadow-md', 'border-white');
                        btn.classList.add('hover:bg-white/50');
                    }
                });

                // Clear and render messages
                chatContainer.innerHTML = '';
                data.messages.forEach(msg => {
                    appendMessage(msg.user_message, 'user');
                    appendMessage(msg.bot_response, 'bot');
                });

            } catch (error) {
                console.error(error);
            } finally {
                loading.classList.add('hidden');
            }
        }
    </script>
</body>

</html>
