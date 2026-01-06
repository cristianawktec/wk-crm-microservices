<template>
  <div class="chatbot-container">
    <!-- Floating Button -->
    <button
      v-if="!isOpen"
      class="chatbot-toggle-btn"
      @click="isOpen = true"
      title="Abrir Assistente IA"
    >
      <span class="icon">💬</span>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
    </button>

    <!-- Chat Window -->
    <transition name="slide-up">
      <div v-if="isOpen" class="chatbot-window">
        <!-- Header -->
        <div class="chatbot-header">
          <h3>Assistente IA 🤖</h3>
          <button class="close-btn" @click="isOpen = false">✕</button>
        </div>

        <!-- Messages Container -->
        <div ref="messagesContainer" class="chatbot-messages">
          <div
            v-for="(msg, idx) in messages"
            :key="idx"
            :class="['message', msg.role]"
          >
            <div class="message-content">{{ msg.content }}</div>
            <small class="message-time">{{ formatTime(msg.timestamp) }}</small>
          </div>

          <!-- Loading Indicator -->
          <div v-if="isLoading" class="message assistant">
            <div class="message-content">
              <span class="loader">●●●</span>
            </div>
          </div>

          <!-- Suggested Prompts (initial state) -->
          <div v-if="messages.length === 0" class="suggested-prompts">
            <p class="prompt-label">Sugestões de perguntas:</p>
            <button
              v-for="(prompt, idx) in suggestedPrompts"
              :key="idx"
              class="prompt-btn"
              @click="sendMessage(prompt)"
            >
              {{ prompt }}
            </button>
          </div>
        </div>

        <!-- Input Area -->
        <div class="chatbot-input-area">
          <input
            v-model="userInput"
            type="text"
            placeholder="Escreva sua pergunta..."
            @keyup.enter="sendMessage(userInput)"
            :disabled="isLoading"
          />
          <button
            class="send-btn"
            @click="sendMessage(userInput)"
            :disabled="!userInput.trim() || isLoading"
          >
            ➤
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { useToast } from 'vue-toastification'
import { api } from '@/services/api'

interface Message {
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
}

const toast = useToast()
const isOpen = ref(false)
const messages = ref<Message[]>([])
const userInput = ref('')
const isLoading = ref(false)
const messagesContainer = ref<HTMLElement>()
const unreadCount = ref(0)

const suggestedPrompts = [
  'Qual é o risco desta oportunidade?',
  'Qual é o meu ticket médio?',
  'Quais são minhas melhores oportunidades?',
  'Como é a distribuição do meu pipeline?'
]

// Auto-scroll to latest message
watch(messages, async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
  // Update unread count when window is closed
  if (!isOpen.value) {
    unreadCount.value = 0
  }
}, { deep: true })

// Track unread messages when window is closed
watch(isOpen, (newVal) => {
  if (newVal) {
    unreadCount.value = 0
  }
})

const formatTime = (date: Date): string => {
  return new Date(date).toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const sendMessage = async (message: string) => {
  if (!message.trim()) return

  // Add user message
  messages.value.push({
    role: 'user',
    content: message,
    timestamp: new Date()
  })
  userInput.value = ''
  isLoading.value = true

  try {
    const response = await api.post('api/chat/ask', {
      question: message,
      context: {
        // Include opportunity context if available
        timestamp: new Date().toISOString()
      }
    })

    // Add assistant response
    messages.value.push({
      role: 'assistant',
      content: response.data.answer,
      timestamp: new Date()
    })
  } catch (error: any) {
    const errorMsg = error.response?.data?.message || 
                     'Desculpe, houve um erro ao processar sua pergunta.'
    
    messages.value.push({
      role: 'assistant',
      content: errorMsg,
      timestamp: new Date()
    })
    
    toast.error('Erro ao comunicar com o assistente IA')
  } finally {
    isLoading.value = false
  }
}

// Add unread indicator when closed and new message arrives
const addUnreadMessage = () => {
  if (!isOpen.value) {
    unreadCount.value++
  }
}
</script>

<style scoped lang="css">
.chatbot-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  z-index: 1000;
}

.chatbot-toggle-btn {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  font-size: 24px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chatbot-toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.6);
}

.chatbot-toggle-btn .badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.chatbot-window {
  position: absolute;
  bottom: 70px;
  right: 0;
  width: 400px;
  height: 600px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  max-height: 90vh;
}

@media (max-width: 480px) {
  .chatbot-window {
    width: calc(100vw - 40px);
    height: calc(100vh - 140px);
    right: 20px;
    bottom: 70px;
  }
}

.chatbot-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.chatbot-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.close-btn:hover {
  transform: scale(1.2);
}

.chatbot-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #f8f9fa;
}

.message {
  display: flex;
  flex-direction: column;
  gap: 4px;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message.user {
  align-items: flex-end;
}

.message.assistant {
  align-items: flex-start;
}

.message-content {
  padding: 10px 14px;
  border-radius: 12px;
  line-height: 1.4;
  word-wrap: break-word;
  max-width: 85%;
  font-size: 14px;
}

.message.user .message-content {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.message.assistant .message-content {
  background: white;
  color: #333;
  border: 1px solid #e5e7eb;
  border-bottom-left-radius: 4px;
}

.message-time {
  font-size: 12px;
  color: #999;
  padding: 0 4px;
}

.suggested-prompts {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 12px 0;
}

.prompt-label {
  font-size: 12px;
  color: #666;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.prompt-btn {
  background: white;
  border: 1px solid #e5e7eb;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  color: #555;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.prompt-btn:hover {
  background: #f0f0f0;
  border-color: #667eea;
  color: #667eea;
}

.loader {
  display: inline-flex;
  gap: 4px;
  animation: pulse 1.4s infinite;
}

@keyframes pulse {
  0%, 60%, 100% { opacity: 0.3; }
  30% { opacity: 1; }
}

.chatbot-input-area {
  display: flex;
  gap: 8px;
  padding: 12px;
  background: white;
  border-top: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.chatbot-input-area input {
  flex: 1;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  padding: 10px 16px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.chatbot-input-area input:focus {
  border-color: #667eea;
}

.chatbot-input-area input:disabled {
  background: #f5f5f5;
  cursor: not-allowed;
}

.send-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Animations */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
