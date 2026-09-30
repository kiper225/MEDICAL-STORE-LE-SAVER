export const useUsers = () => {
  const api = useApi()

  const getUsers = (params = {}) => api('/users', { params })
  const updateUser = (id: number, payload) => api(`/users/${id}`, { method: 'PUT', body: payload })
  const deleteUser = (id: number) => api(`/users/${id}`, { method: 'DELETE' })

  return { getUsers, updateUser, deleteUser }
}