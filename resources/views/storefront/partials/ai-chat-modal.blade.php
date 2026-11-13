{{-- DEEN Electronics Virtual AI Sales Assistant & WhatsApp Order Desk Popup --}}
<div x-data="deenAiChat()" 
     x-init="initChat()" 
     @open-deen-chat.window="openChat($event.detail)"
     class="relative z-50">

    {{-- Floating Support Desk Launcher Button (Replaces direct raw link) --}}
    <aside aria-label="Support Desk" 
           class="fixed bottom-5 right-4 sm:bottom-6 sm:right-6 z-40 transition-transform duration-200"
           x-show="!isOpen"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 translate-y-4 scale-95"
           x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        <button type="button" 
                @click="toggleChat()" 
                class="group flex items-center space-x-2.5 bg-emerald-600 hover:bg-emerald-500 text-white py-3 px-4 rounded-full shadow-2xl transition-all transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-emerald-500/30"
                aria-label="Open DEEN AI Order Desk and WhatsApp Chat">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
            </span>
            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
            </svg>
            <span class="text-xs font-bold hidden sm:inline-block pr-1">WhatsApp Order Desk</span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-white/20 text-white tracking-wide uppercase">AI</span>
        </button>
    </aside>

    {{-- Interactive Chat Popup Window (Mobile Responsive) --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         @click.away="if(shouldCloseOnOutsideClick) isOpen = false"
         class="fixed bottom-3 right-3 left-3 sm:left-auto sm:right-6 sm:bottom-6 sm:w-[410px] max-w-[410px] h-[580px] max-h-[88vh] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden font-sans"
         style="display: none;">

        {{-- Chat Header --}}
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white px-5 py-4 flex items-center justify-between border-b border-slate-800 shadow-md">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 flex items-center justify-center text-white font-black text-sm shadow-inner border border-white/20">
                        DEEN
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="text-sm font-bold tracking-tight text-white" x-text="botProfile.name || 'DEEN AI Assistant'">DEEN AI Assistant</h3>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-500/30 text-rose-300 border border-rose-400/30">AI Bot</span>
                    </div>
                    <div class="flex items-center space-x-1.5 text-[11px] text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span x-text="botProfile.status || 'Online • Instant Responses'">Online • Instant Responses</span>
                        <span class="text-slate-500">&bull;</span>
                        <span class="text-slate-300" x-text="botProfile.role || 'Virtual Order & Sales Desk'">Virtual Order & Sales Desk</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-1">
                {{-- Quick Redirect to Real WhatsApp --}}
                <a :href="botProfile.whatsapp_url" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   title="Open directly in WhatsApp"
                   class="p-2 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 rounded-xl transition-colors">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                </a>
                {{-- Close Button --}}
                <button type="button" 
                        @click="isOpen = false" 
                        title="Close chat"
                        class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Notice Banner --}}
        <div class="bg-rose-50 border-b border-rose-100 px-4 py-2 flex items-center justify-between text-[11px] text-rose-800">
            <span class="flex items-center space-x-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-rose-500"></span>
                <span>DEEN Order Desk • Ask questions or transfer to WhatsApp</span>
            </span>
            <button type="button" @click="requestHumanHandoff()" class="font-bold underline text-rose-700 hover:text-rose-900 cursor-pointer">
                Talk to Human
            </button>
        </div>

        {{-- Chat Messages Area --}}
        <div x-ref="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3.5 bg-slate-50/70 text-xs">
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    {{-- Bot Message --}}
                    <template x-if="msg.sender === 'bot'">
                        <div class="flex items-start space-x-2.5 max-w-[90%]">
                            <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex-shrink-0 flex items-center justify-center text-[10px] font-bold mt-0.5">
                                AI
                            </div>
                            <div class="space-y-2">
                                <div class="bg-white text-slate-800 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-slate-200/80 leading-relaxed whitespace-pre-line"
                                     x-html="formatMessage(msg.text)">
                                </div>

                                {{-- Human Handoff CTA Card inside bot response --}}
                                <template x-if="msg.action === 'human_handoff'">
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 shadow-sm space-y-2 text-slate-800">
                                        <div class="flex items-center space-x-2 text-emerald-800 font-bold text-xs">
                                            <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                            <span>Connect with Showroom Sales</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600">
                                            Speak with <strong>Muhammad Aslam</strong> directly regarding custom quotes, delivery scheduling, or immediate orders.
                                        </p>
                                        <a :href="getWhatsAppHandoffUrl()" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center justify-center w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all space-x-2">
                                            <span>Continue on WhatsApp (0300-7769932)</span>
                                            <svg class="w-3.5 h-3.5 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    </div>
                                </template>

                                <span class="text-[10px] text-slate-400 pl-1" x-text="msg.time"></span>
                            </div>
                        </div>
                    </template>

                    {{-- User Message --}}
                    <template x-if="msg.sender === 'user'">
                        <div class="flex items-end justify-end space-x-2">
                            <div class="max-w-[85%] space-y-1">
                                <div class="bg-rose-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 shadow-sm leading-relaxed whitespace-pre-line text-right"
                                     x-text="msg.text">
                                </div>
                                <div class="text-[10px] text-slate-400 text-right pr-1" x-text="msg.time"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing Simulation Indicator --}}
            <div x-show="isTyping" class="flex items-center space-x-2 max-w-[80%] pt-1">
                <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex-shrink-0 flex items-center justify-center text-[10px] font-bold">
                    AI
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-3.5 py-2.5 shadow-sm flex items-center space-x-1.5">
                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                    <span class="text-[11px] text-slate-400 pl-1.5">DEEN AI is typing...</span>
                </div>
            </div>
        </div>

        {{-- Quick Suggested Questions Bar (Scrollable chips) --}}
        <div class="bg-white border-t border-slate-100 p-2.5 overflow-x-auto scrollbar-none flex items-center space-x-1.5 whitespace-nowrap">
            <template x-for="(chip, chipIdx) in suggestedQuestions" :key="chipIdx">
                <button type="button" 
                        @click="askQuestion(chip)"
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-medium bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-700 border border-slate-200 hover:border-rose-200 transition-colors cursor-pointer flex-shrink-0">
                    <span x-text="chip"></span>
                </button>
            </template>
        </div>

        {{-- Chat Input Form --}}
        <div class="p-3 bg-white border-t border-slate-200">
            <form @submit.prevent="submitInput()" class="relative flex items-center">
                <input type="text" 
                       x-model="userInput" 
                       x-ref="chatInput"
                       placeholder="Ask about products, warranty, delivery..." 
                       class="w-full pl-3.5 pr-20 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-slate-900 placeholder-slate-400 transition-all shadow-inner"
                       autocomplete="off">
                <div class="absolute right-1.5 flex items-center space-x-1">
                    <button type="submit" 
                            :disabled="!userInput.trim() || isTyping"
                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center">
                        <span>Send</span>
                    </button>
                </div>
            </form>

            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400 px-1">
                <span>Direct Showroom Contact: Muhammad Aslam</span>
                <button type="button" @click="requestHumanHandoff()" class="text-emerald-700 hover:underline font-semibold flex items-center space-x-1">
                    <span>Redirect to WhatsApp</span>
                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.187-2.59-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function deenAiChat() {
        return {
            isOpen: false,
            isTyping: false,
            userInput: '',
            shouldCloseOnOutsideClick: false,
            botProfile: {
                name: 'DEEN AI Assistant',
                role: 'Virtual Order & Sales Desk',
                status: 'Online • Instant Responses',
                showroom_contact: 'Muhammad Aslam',
                showroom_phone: '0300-7769932',
                whatsapp_url: 'https://wa.me/923007769932?text=Assalam%20o%20Alaikum%20DEEN%20Electronics,%20I%20would%20like%20to%20speak%20with%20a%20human%20sales%20representative.'
            },
            suggestedQuestions: [
                'Where is your showroom?',
                'Do you deliver nationwide?',
                'What payment options?',
                'Official brand warranty?',
                'Electric scooters details',
                'Talk to a real human'
            ],
            qaPairs: [],
            messages: [],

            async initChat() {
                // Fetch FAQ JSON knowledge base
                try {
                    const response = await fetch('/data/deen-ai-faq.json');
                    if (response.ok) {
                        const data = await response.json();
                        if (data.bot_profile) this.botProfile = data.bot_profile;
                        if (data.greeting && data.greeting.suggested_questions) {
                            this.suggestedQuestions = data.greeting.suggested_questions;
                        }
                        if (data.qa_pairs) {
                            this.qaPairs = data.qa_pairs;
                        }
                        // Set initial greeting
                        const welcomeText = (data.greeting && data.greeting.message) 
                            ? data.greeting.message 
                            : 'Assalam o Alaikum! Welcome to DEEN Electronics. How can I assist you with our home appliances or order enquiries today?';
                        
                        this.messages = [{
                            sender: 'bot',
                            text: welcomeText,
                            time: this.currentTime(),
                            action: null
                        }];
                    }
                } catch (e) {
                    console.warn('Could not load deen-ai-faq.json, using built-in defaults:', e);
                    this.messages = [{
                        sender: 'bot',
                        text: 'Assalam o Alaikum! Welcome to DEEN Electronics Lahore. I am your Virtual AI Sales Assistant. How can I help you today?',
                        time: this.currentTime(),
                        action: null
                    }];
                }
            },

            openChat(detail) {
                this.isOpen = true;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    if (this.$refs.chatInput) this.$refs.chatInput.focus();
                });

                if (detail && detail.query) {
                    this.userInput = detail.query;
                    this.submitInput();
                }
            },

            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (this.$refs.chatInput) this.$refs.chatInput.focus();
                    });
                }
            },

            askQuestion(questionText) {
                this.userInput = questionText;
                this.submitInput();
            },

            currentTime() {
                const now = new Date();
                let hours = now.getHours();
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                return `${hours}:${minutes} ${ampm}`;
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    if (this.$refs.chatMessages) {
                        this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
                    }
                });
            },

            formatMessage(text) {
                if (!text) return '';
                // Basic markdown formatting: bold (**text**), bullet points (* or •), line breaks
                let html = text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/^\s*[-•]\s+(.*)$/gm, '<li class="ml-3 list-disc">$1</li>')
                    .replace(/\n/g, '<br>');
                return html;
            },

            getWhatsAppHandoffUrl(customText) {
                const text = customText || 'Assalam o Alaikum DEEN Electronics, I was chatting with your AI Assistant and would like to speak directly with a human sales representative.';
                return `https://wa.me/923007769932?text=${encodeURIComponent(text)}`;
            },

            requestHumanHandoff() {
                this.messages.push({
                    sender: 'user',
                    text: 'I would like to talk to a real human representative.',
                    time: this.currentTime()
                });
                this.scrollToBottom();
                this.isTyping = true;

                setTimeout(() => {
                    this.isTyping = false;
                    this.messages.push({
                        sender: 'bot',
                        text: 'Connecting you with **Muhammad Aslam** and our showroom specialists at DEEN Electronics Lahore. Click below to continue on WhatsApp:',
                        time: this.currentTime(),
                        action: 'human_handoff'
                    });
                    this.scrollToBottom();
                }, 600);
            },

            submitInput() {
                const query = this.userInput.trim();
                if (!query || this.isTyping) return;

                // Push user message
                this.messages.push({
                    sender: 'user',
                    text: query,
                    time: this.currentTime()
                });
                this.userInput = '';
                this.scrollToBottom();

                // Simulate AI thinking and typing delay
                this.isTyping = true;
                const typingDelay = Math.min(1100, Math.max(500, query.length * 20));

                setTimeout(() => {
                    const responseObj = this.matchAnswer(query);
                    this.isTyping = false;
                    this.messages.push({
                        sender: 'bot',
                        text: responseObj.answer,
                        time: this.currentTime(),
                        action: responseObj.action || null
                    });
                    this.scrollToBottom();

                    // If explicit human handoff requested, we can also provide quick redirection option
                    if (responseObj.action === 'human_handoff' && responseObj.directRedirect) {
                        setTimeout(() => {
                            window.open(this.getWhatsAppHandoffUrl(`Assalam o Alaikum, I asked: "${query}". Please connect me with human sales.`), '_blank');
                        }, 1200);
                    }
                }, typingDelay);
            },

            matchAnswer(rawQuery) {
                const q = rawQuery.toLowerCase();

                // 1. Check for explicit Human Handoff / Talk to human intents
                const humanKeywords = ['human', 'real human', 'real person', 'person', 'agent', 'representative', 'aslam', 'owner', 'call', 'whatsapp', 'speak to someone', 'talk to someone', 'connect me', 'talk to human'];
                const isHumanIntent = humanKeywords.some(keyword => q.includes(keyword));

                if (isHumanIntent) {
                    return {
                        answer: 'Connecting you directly with **Muhammad Aslam** and our human sales specialists at our Lahore Showroom on WhatsApp...',
                        action: 'human_handoff',
                        directRedirect: false
                    };
                }

                // 2. Check for Greetings
                const greetingKeywords = ['hi', 'hello', 'salam', 'assalam', 'aoa', 'hey', 'good morning', 'good evening'];
                if (greetingKeywords.some(g => q === g || q.startsWith(g + ' ') || q.includes(g))) {
                    return {
                        answer: 'Assalam o Alaikum! How can I assist you with DEEN Electronics products, showroom address, nationwide shipping, or official warranties today?',
                        action: null
                    };
                }

                // 3. Score matching against QA pairs from JSON
                let bestMatch = null;
                let highestScore = 0;

                for (const pair of this.qaPairs) {
                    let score = 0;
                    if (pair.keywords && Array.isArray(pair.keywords)) {
                        for (const kw of pair.keywords) {
                            const kwLower = kw.toLowerCase();
                            if (q === kwLower) {
                                score += 5; // Exact keyword match
                            } else if (q.includes(kwLower)) {
                                score += kwLower.length >= 4 ? 3 : 1; // Substring match
                            }
                        }
                    }

                    if (pair.question && q.includes(pair.question.toLowerCase())) {
                        score += 10;
                    }

                    if (score > highestScore) {
                        highestScore = score;
                        bestMatch = pair;
                    }
                }

                // If threshold reached, return matched answer
                if (bestMatch && highestScore >= 2) {
                    return {
                        answer: bestMatch.answer,
                        action: bestMatch.action || null
                    };
                }

                // Fallback answer with quick guidance and human handoff option
                return {
                    answer: 'Thank you for your enquiry. I am DEEN Electronics’ Virtual AI Assistant. I can help with:\n\n• Showroom location & timings (Main GT Road, Lahore)\n• Nationwide delivery policy & bulky freight\n• Payment methods (COD, Bank, JazzCash)\n• Official brand warranties\n• Product categories & electric scooters\n\nIf your question requires custom quotation or immediate stock checking, I can connect you directly with **Muhammad Aslam** on WhatsApp.',
                    action: 'human_handoff'
                };
            }
        };
    }
</script>
