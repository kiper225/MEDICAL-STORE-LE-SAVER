export const useTechnicians = () => {
  const api = useApi()

  const getTechnicians = () => api('/technicians')
  const createTechnician = (payload) => api('/technicians', { method: 'POST', body: payload })
  const deleteTechnician = (id: number) => api(`/technicians/${id}`, { method: 'DELETE' })

  return { getTechnicians, createTechnician, deleteTechnician }
}