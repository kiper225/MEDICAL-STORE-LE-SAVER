<script setup>
const props = defineProps({
  modelValue: Boolean,
  orderId: { type: Number, default: null },
  rentalId: { type: Number, default: null },
})
const emit = defineEmits(['update:modelValue', 'success'])

const { initiateMobileMoney, checkStatus } = usePayments()
const toast = useToast()

const operateur = ref('orange')
const telephone = ref('')
const submitting = ref(false)
const waiting = ref(false)
const errorMessage = ref('')

const operateurs = [
  { label: 'Orange Money', value: 'orange' },
  { label: 'MTN Mobile Money', value: 'mtn' },
  { label: 'Moov Money', value: 'moov' },
  { label: 'Wave', value: 'wave' },
]

let pollInterval = null

const handlePay = async () => {
  errorMessage.value = ''
  submitting.value = true
  try {
    const response = await initiateMobileMoney({
      order_id: props.orderId,
      rental_id: props.rentalId,
      operateur: operateur.value,
      telephone: telephone.value,
    })

    waiting.value = true
    submitting.value = false

    pollInterval = setInterval(async () => {
      const status = await checkStatus(response.payment_id)
      if (status.statut === 'reussi') {
        clearInterval(pollInterval)
        waiting.value = false
        toast.add({ title: 'Paiement confirmé', color: 'success' })
        emit('success')
        emit('update:modelValue', false)
      } else if (status.statut === 'echoue') {
        clearInterval(pollInterval)
        waiting.value = false
        errorMessage.value = 'Le paiement a échoué. Réessayez.'
      }
    }, 3000)
  } catch (err) {
    submitting.value = false
    errorMessage.value = err.data?.message || 'Erreur lors du paiement.'
  }
}

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
  <UModal :model-value="modelValue" @update:model-value="(v) => emit('update:modelValue', v)">
    <template #content>
      <UCard>
        <template #header>
          <h3 class="font-semibold">Paiement mobile money</h3>
        </template>

        <div v-if="!waiting" class="space-y-4">
          <UFormField label="Opérateur">
            <USelect v-model="operateur" :items="operateurs" class="w-full" />
          </UFormField>

          <UFormField label="Numéro de téléphone">
            <UInput v-model="telephone" placeholder="07 XX XX XX XX" class="w-full" />
          </UFormField>

          <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

          <UButton block :loading="submitting" @click="handlePay">
            Payer
          </UButton>
        </div>

        <div v-else class="text-center py-6">
          <UIcon name="i-lucide-loader-2" class="w-8 h-8 mx-auto animate-spin text-primary mb-3" />
          <p class="font-medium">En attente de confirmation</p>
          <p class="text-sm text-gray-500 mt-1">
            Vérifiez votre téléphone et validez le paiement avec votre code PIN.
          </p>
        </div>
      </UCard>
    </template>
  </UModal>
</template>