export const useRentals = () => {
  const api = useApi()

  const createRental = (payload) => api('/rentals', { method: 'POST', body: payload })
  const getMyRentals = () => api('/my-rentals')

  return { createRental, getMyRentals }
}