document.addEventListener('DOMContentLoaded', () => {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chat-window');
    const chatClose = document.getElementById('chat-close');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    let chatHistory = [];

    // --- Functions for History ---
    function saveHistory() {
        localStorage.setItem('learnhubChatHistory', JSON.stringify(chatHistory));
    }

    function loadHistory() {
        const savedHistory = localStorage.getItem('learnhubChatHistory');
        if (savedHistory) {
            chatHistory = JSON.parse(savedHistory);
            chatMessages.innerHTML = ''; // Clear the area before loading
            chatHistory.forEach(msg => addMessage(msg.sender, msg.text, false)); // false = don't save again
            return true;
        }
        return false;
    }

    // --- Event Listeners ---
    chatbotToggle.addEventListener('click', () => toggleChatWindow(true));
    chatClose.addEventListener('click', () => toggleChatWindow(false));
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // --- Core Functions ---
    function toggleChatWindow(open) {
        if (open) {
            chatWindow.classList.remove('hidden');
            const historyLoaded = loadHistory();
            if (!historyLoaded || chatHistory.length === 0) {
                // If no history or history is empty, add welcome message
                addMessage('bot', 'Hello! How can I help you with LearnHub?');
            }
        } else {
            chatWindow.classList.add('hidden');
        }
    }

    async function sendMessage() {
        const userMessage = chatInput.value.trim();
        if (userMessage === '') return;

        addMessage('user', userMessage);
        chatInput.value = '';

        const thinkingMessage = addMessage('bot', '...');

        try {
            const botResponse = await generateBotResponse(userMessage);
            thinkingMessage.querySelector('p').textContent = botResponse;
            // Update the history with the real response
            const lastMessage = chatHistory[chatHistory.length - 1];
            if(lastMessage.sender === 'bot') {
                lastMessage.text = botResponse;
                saveHistory();
            }
        } catch (error) {
            let errorMessage = 'Sorry, something went wrong. Please try again.';
            if (error.message.includes('503')) {
                errorMessage = 'The AI assistant is currently experiencing high demand. Please try again in a few moments.';
            }
            thinkingMessage.querySelector('p').textContent = errorMessage;
            // Update the history with the error message
            const lastMessage = chatHistory[chatHistory.length - 1];
            if(lastMessage.sender === 'bot') {
                lastMessage.text = errorMessage;
                saveHistory();
            }
        }
    }

    function addMessage(sender, text, shouldSave = true) {
        if (shouldSave) {
            chatHistory.push({ sender, text });
            saveHistory();
        }

        const messageElement = document.createElement('div');
        messageElement.classList.add('mb-4');

        let content = '';
        if (sender === 'bot') {
            content = `
                <div class="flex items-start gap-2">
                    <div class="w-8 h-8 bg-primary dark:bg-white text-white dark:text-primary rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-lg">smart_toy</span>
                    </div>
                    <div class="bg-primary/5 dark:bg-white/5 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-primary dark:text-white">${text}</p>
                    </div>
                </div>
            `;
        } else { // user
            content = `
                <div class="flex items-start gap-2 justify-end">
                    <div class="bg-primary/10 dark:bg-white/10 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-primary dark:text-white">${text}</p>
                    </div>
                </div>
            `;
        }
        messageElement.innerHTML = content;
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return messageElement;
    }

    async function generateBotResponse(userMessage) {
        try {
            const response = await fetch('api/chatbot_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ question: userMessage })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.error) {
                console.error('API Error:', data.details || data.error);
                return 'Sorry, I encountered an error. Please check the console for details.';
            }

            return data.answer;
        } catch (error) {
            console.error('Fetch Error:', error);
            throw error; // Re-throw the error to be caught by sendMessage
        }
    }
});
