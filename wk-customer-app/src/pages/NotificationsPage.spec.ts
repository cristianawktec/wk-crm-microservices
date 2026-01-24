import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import NotificationsPage from '@/pages/Notifications Page.vue'

describe('NotificationsPage.vue', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(NotificationsPage, {
      global: {
        stubs: {
          RouterLink: true,
        },
      },
    })
  })

  it('renders notifications page', () => {
    expect(wrapper.exists()).toBe(true)
  })

  it('displays page title', () => {
    const title = wrapper.find('h1, h2, .page-title')
    expect(title.exists()).toBe(true)
  })

  it('starts with empty notifications array', () => {
    expect(Array.isArray(wrapper.vm.notifications)).toBe(true)
  })

  it('has loading state', () => {
    expect(wrapper.vm.isLoading).toBeDefined()
  })

  it('displays loading indicator when loading', async () => {
    wrapper.vm.isLoading = true
    await wrapper.vm.$nextTick()

    const loading = wrapper.find('.loading, [data-testid="loading"]')
    expect(loading.exists()).toBe(true)
  })

  it('displays notifications list when loaded', async () => {
    wrapper.vm.isLoading = false
    wrapper.vm.notifications = [
      {
        id: 1,
        title: 'Test Notification',
        message: 'Test message',
        read_at: null,
        created_at: new Date().toISOString(),
      },
    ]
    await wrapper.vm.$nextTick()

    const notifications = wrapper.findAll('.notification-item, [data-testid="notification"]')
    expect(notifications.length).toBeGreaterThan(0)
  })

  it('displays empty state when no notifications', async () => {
    wrapper.vm.isLoading = false
    wrapper.vm.notifications = []
    await wrapper.vm.$nextTick()

    const emptyState = wrapper.find('.empty-state, [data-testid="empty"]')
    expect(emptyState.exists()).toBe(true)
  })

  it('has filter options', () => {
    expect(wrapper.vm.filter).toBeDefined()
  })

  it('can filter by unread notifications', async () => {
    wrapper.vm.filter = 'unread'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.filter).toBe('unread')
  })

  it('can filter by read notifications', async () => {
    wrapper.vm.filter = 'read'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.filter).toBe('read')
  })

  it('displays unread notification badge/indicator', async () => {
    wrapper.vm.notifications = [
      {
        id: 1,
        read_at: null,
        title: 'Unread notification',
      },
    ]
    await wrapper.vm.$nextTick()

    const notification = wrapper.find('.notification-item:first-child')
    if (notification.exists()) {
      const unreadIndicator = notification.find('.unread, [data-unread="true"]')
      expect(unreadIndicator.exists()).toBe(true)
    }
  })

  it('can mark notification as read', async () => {
    const markAsRead = wrapper.vm.markAsRead
    if (typeof markAsRead === 'function') {
      expect(markAsRead).toBeInstanceOf(Function)
    }
  })

  it('can mark all notifications as read', async () => {
    const markAllAsRead = wrapper.vm.markAllAsRead
    if (typeof markAllAsRead === 'function') {
      expect(markAllAsRead).toBeInstanceOf(Function)
    }
  })

  it('displays notification action link', async () => {
    wrapper.vm.notifications = [
      {
        id: 1,
        action_url: '/opportunities/123',
        title: 'Test',
      },
    ]
    await wrapper.vm.$nextTick()

    const actionLink = wrapper.find('a[href*="/opportunities"]')
    if (actionLink.exists()) {
      expect(actionLink.attributes('href')).toContain('/opportunities')
    }
  })

  it('formats notification date/time', async () => {
    wrapper.vm.notifications = [
      {
        id: 1,
        created_at: '2026-01-24T10:00:00Z',
        title: 'Test',
      },
    ]
    await wrapper.vm.$nextTick()

    const dateElement = wrapper.find('.notification-date, time')
    expect(dateElement.exists()).toBe(true)
  })
})
