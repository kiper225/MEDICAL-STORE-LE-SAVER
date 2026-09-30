export const useCategories = () => {
  const api = useApi()

  const getCategories = () => api('/categories')

  return { getCategories }
}