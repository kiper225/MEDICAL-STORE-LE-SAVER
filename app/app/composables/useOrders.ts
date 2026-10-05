export const useOrders = () => {
  const api = useApi()

  const createOrder = (payload) => api('/orders', { method: 'POST', body: payload })
  const getMyOrders = () => api('/my-orders')
  const getAllOrders = () => api('/orders')
  const updateOrderStatus = (id: number, statut: string) =>
    api(`/orders/${id}`, { method: 'PUT', body: { statut } })

  const getVendorOrders = () => api('/vendor/orders')
  const getTechnicienDashboard = () => api('/technicien/dashboard')
  const updateItemStatus = (itemId, statut) =>
  api(`/order-items/${itemId}/statut`, { method: 'PUT', body: { statut } })

  return { createOrder, getMyOrders, getAllOrders, updateOrderStatus, getVendorOrders, getTechnicienDashboard, updateItemStatus }
}