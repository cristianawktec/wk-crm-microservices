import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatbotWidget from '@/components/ChatbotWidget.vue'

describe('ChatbotWidget.vue', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(ChatbotWidget, {
      global: {
        stubs: {
          teleport: true,
        },
      },
    })
  })

  it('renders chatbot widget', () => {
    expect(wrapper.exists()).toBe(true)
  })

  it('starts with chat minimized', () => {
    expect(wrapper.vm.isOpen).toBe(false)
  })

  it('toggles chat window on button click', async () => {
    const button = wrapper.find('button')
    expect(button.exists()).toBe(true)

    await button.trigger('click')
    expect(wrapper.vm.isOpen).toBe(true)

    await button.trigger('click')
    expect(wrapper.vm.isOpen).toBe(false)
  })

  it('initializes with empty messages array', () => {
    expect(wrapper.vm.messages).toEqual([])
  })

  it('has user input field when open', async () => {
    wrapper.vm.isOpen = true
    await wrapper.vm.$nextTick()

    const input = wrapper.find('input[type="text"]')
    expect(input.exists()).toBe(true)
  })

  it('has send button when open', async () => {
    wrapper.vm.isOpen = true
    await wrapper.vm.$nextTick()

    const sendButton = wrapper.find('button[type="submit"]')
    expect(sendButton.exists()).toBe(true)
  })

  it('displays loading state when sending message', async () => {
    wrapper.vm.isOpen = true
    wrapper.vm.isLoading = true
    await wrapper.vm.$nextTick()

    const loadingIndicator = wrapper.find('.loading')
    expect(loadingIndicator.exists()).toBe(true)
  })

  it('displays suggestion prompts', async () => {
    wrapper.vm.isOpen = true
    await wrapper.vm.$nextTick()

    const suggestions = wrapper.findAll('.suggestion')
    expect(suggestions.length).toBeGreaterThan(0)
  })

  it('adds message to messages array when sending', async () => {
    wrapper.vm.userInput = 'Test message'
    await wrapper.vm.sendMessage()

    expect(wrapper.vm.messages.length).toBeGreaterThan(0)
    expect(wrapper.vm.messages[0].content).toBe('Test message')
    expect(wrapper.vm.messages[0].sender).toBe('user')
  })

  it('clears input after sending message', async () => {
    wrapper.vm.userInput = 'Test message'
    await wrapper.vm.sendMessage()

    expect(wrapper.vm.userInput).toBe('')
  })
})
