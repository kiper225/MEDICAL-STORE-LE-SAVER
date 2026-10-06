export const useNotifications = () => {
  const api = useApi()

  const getNotifications = () => api('/notifications')
  const markRead = (id: string | number) => api(`/notifications/${id}/read`, { method: 'POST' })
  const markAllRead = () => api('/notifications/read-all', { method: 'POST' })

  return { getNotifications, markRead, markAllRead }
}