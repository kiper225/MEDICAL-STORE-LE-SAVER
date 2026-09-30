export const usePayments = () => {
  const api = useApi()

  const initiateMobileMoney = (payload: Record<string, unknown>) =>
    api('/payments/mobile-money', { method: 'POST', body: payload })

  const checkStatus = (paymentId: number) =>
    api(`/payments/${paymentId}/status`)

  return { initiateMobileMoney, checkStatus }
}