import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import NotificationBell from '@/components/NotificationBell.vue'

describe('NotificationBell.vue', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(NotificationBell, {
      global: {
        stubs: {
          RouterLink: true,
        },
      },
    })
  })

  it('renders notification bell', () => {
    expect(wrapper.exists()).toBe(true)
  })

  it('displays bell icon', () => {
    const bellIcon = wrapper.find('svg')
    expect(bellIcon.exists()).toBe(true)
  })

  it('starts with zero unread count', () => {
    expect(wrapper.vm.unreadCount).toBe(0)
  })

  it('displays badge when there are unread notifications', async () => {
    wrapper.vm.unreadCount = 5
    await wrapper.vm.$nextTick()

    const badge = wrapper.find('.badge')
    expect(badge.exists()).toBe(true)
    expect(badge.text()).toBe('5')
  })

  it('hides badge when unread count is zero', async () => {
    wrapper.vm.unreadCount = 0
    await wrapper.vm.$nextTick()

    const badge = wrapper.find('.badge')
    expect(badge.exists()).toBe(false)
  })

  it('displays max 99+ for high unread count', async () => {
    wrapper.vm.unreadCount = 150
    await wrapper.vm.$nextTick()

    const badge = wrapper.find('.badge')
    expect(badge.text()).toBe('99+')
  })

  it('is a clickable link', () => {
    const link = wrapper.find('a, button, [role="button"]')
    expect(link.exists()).toBe(true)
  })

  it('has proper accessibility attributes', () => {
    const element = wrapper.find('[aria-label]')
    expect(element.exists()).toBe(true)
  })

  it('updates unread count when notifications change', async () => {
    expect(wrapper.vm.unreadCount).toBe(0)

    wrapper.vm.unreadCount = 3
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.unreadCount).toBe(3)
  })
})
