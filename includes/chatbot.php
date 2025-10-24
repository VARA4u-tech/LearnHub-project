<!-- Chatbot -->
<div id="chatbot-container" class="fixed bottom-4 right-4 z-50">
    <!-- Chatbot Toggle Button -->
    <button id="chatbot-toggle" class="bg-primary dark:bg-white text-white dark:text-primary w-16 h-16 rounded-full shadow-lg flex items-center justify-center focus:outline-none transform transition-transform hover:scale-110">
        <span class="material-symbols-outlined text-3xl">
            chat
        </span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="hidden absolute bottom-20 right-0 w-80 sm:w-96 bg-background-light dark:bg-background-dark rounded-lg shadow-2xl border border-primary/10 dark:border-white/10 flex flex-col" style="height: 500px;">
        <!-- Chat Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/10 dark:border-white/10">
            <h3 class="text-lg font-bold text-primary dark:text-white">LearnHub AI-Assistant</h3>
            <button id="chat-close" class="text-primary/70 dark:text-white/70 hover:text-primary dark:hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto"></div>

        <!-- Message Input -->
        <div class="p-4 border-t border-primary/10 dark:border-white/10">
            <div class="flex gap-2">
                <input type="text" id="chat-input" class="w-full px-3 py-2 border border-primary/20 dark:border-white/20 rounded-lg bg-transparent text-primary dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 dark:focus:ring-white/50" placeholder="Ask a question...">
                <button id="chat-send" class="bg-primary dark:bg-white text-white dark:text-primary px-4 py-2 rounded-lg font-semibold hover:bg-primary/80 dark:hover:bg-white/80 transition-colors">
                    Send
                </button>
            </div>
        </div>
    </div>
</div>
