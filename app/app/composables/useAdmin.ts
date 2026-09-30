export const useAdmin = () => {
  const api = useApi()

  const getDashboard = () => api('/admin/dashboard')

  return { getDashboard }
}