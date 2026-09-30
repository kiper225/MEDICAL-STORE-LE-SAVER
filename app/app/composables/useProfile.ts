export const useProfile = () => {
  const api = useApi()

  const updateProfile = (formData) => api('/profile', { method: 'POST', body: formData })

  return { updateProfile }
}