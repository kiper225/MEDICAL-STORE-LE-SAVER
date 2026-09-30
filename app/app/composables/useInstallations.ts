export const useInstallationsAdmin = () => {
  const api = useApi()

  const getInstallations = () => api('/installations')
  const assignTechnician = (id: number, technicien_id: number) =>
    api(`/installations/${id}`, { method: 'PUT', body: { technicien_id } })

  return { getInstallations, assignTechnician }
}