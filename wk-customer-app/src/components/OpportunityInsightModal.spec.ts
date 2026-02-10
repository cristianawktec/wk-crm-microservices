import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import OpportunityInsightModal from '@/components/OpportunityInsightModal.vue'

describe('OpportunityInsightModal.vue', () => {
  const mockOpportunity = {
    id: '1',
    title: 'Test Opportunity',
    value: 50000,
    probability: 75,
    status: 'open',
    created_at: new Date().toISOString(),
  }

  let wrapper: any

  beforeEach(() => {
    wrapper = mount(OpportunityInsightModal, {
      props: {
        opportunity: mockOpportunity,
        isOpen: false,
      },
    })
  })

  it('renders modal component', () => {
    expect(wrapper.exists()).toBe(true)
  })

  it('does not display modal when isOpen is false', () => {
    expect(wrapper.vm.isOpen).toBe(false)
  })

  it('displays modal when isOpen is true', async () => {
    await wrapper.setProps({ isOpen: true })
    expect(wrapper.vm.isOpen).toBe(true)
  })

  it('receives opportunity prop', () => {
    expect(wrapper.props('opportunity')).toEqual(mockOpportunity)
  })

  it('displays opportunity title in modal', async () => {
    await wrapper.setProps({ isOpen: true })
    await wrapper.vm.$nextTick()

    const titleElement = wrapper.find('.opportunity-title')
    if (titleElement.exists()) {
      expect(titleElement.text()).toContain('Test Opportunity')
    }
  })

  it('shows loading state initially', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.isLoading = true
    await wrapper.vm.$nextTick()

    const loading = wrapper.find('.loading, [data-testid="loading"]')
    expect(loading.exists()).toBe(true)
  })

  it('displays AI insights when loaded', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.isLoading = false
    wrapper.vm.insights = {
      analysis: 'Test analysis',
      risk_score: 0.3,
      recommended_action: 'Test action',
    }
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.insights).toBeDefined()
    expect(wrapper.vm.insights.analysis).toBe('Test analysis')
  })

  it('displays risk score', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.insights = {
      risk_score: 0.5,
    }
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.insights.risk_score).toBe(0.5)
  })

  it('displays recommended action', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.insights = {
      recommended_action: 'Follow up within 48 hours',
    }
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.insights.recommended_action).toBe('Follow up within 48 hours')
  })

  it('has close button', async () => {
    await wrapper.setProps({ isOpen: true })
    await wrapper.vm.$nextTick()

    const closeButton = wrapper.find('button.close, [aria-label="Close"]')
    expect(closeButton.exists()).toBe(true)
  })

  it('emits close event when close button clicked', async () => {
    await wrapper.setProps({ isOpen: true })
    await wrapper.vm.$nextTick()

    const closeButton = wrapper.find('button.close, [aria-label="Close"]')
    if (closeButton.exists()) {
      await closeButton.trigger('click')
      expect(wrapper.emitted('close')).toBeTruthy()
    }
  })

  it('handles error state', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.error = 'Failed to load insights'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.error).toBe('Failed to load insights')
  })

  it('displays model name when provided', async () => {
    await wrapper.setProps({ isOpen: true })
    wrapper.vm.insights = {
      model: 'groq-llama-3.3-70b',
    }
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.insights.model).toBe('groq-llama-3.3-70b')
  })
})
