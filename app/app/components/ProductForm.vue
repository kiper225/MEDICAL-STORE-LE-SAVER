<script setup>
const props = defineProps({
  initialProduct: { type: Object, default: null },
})

const isEdit = computed(() => !!props.initialProduct)

const { createProduct, updateProduct, deleteProductImage, uploadImages } = useProducts()
const { getCategories } = useCategories()
const toast = useToast()
const router = useRouter()

const { data: categoriesTree } = await useAsyncData('categories-form', () => getCategories())

const categoryOptions = computed(() => {
  const flat = []
  categoriesTree.value?.forEach((parent) => {
    parent.children?.forEach((child) => {
      flat.push({ label: `${parent.nom} > ${child.nom}`, value: child.id })
    })
  })
  return flat
})

const form = reactive({
  nom: props.initialProduct?.nom || '',
  description: props.initialProduct?.description || '',
  category_id: props.initialProduct?.category_id || null,
  marque: props.initialProduct?.marque || '',
  type_disponibilite: props.initialProduct?.type_disponibilite || 'vente',
  necessite_installation: props.initialProduct?.necessite_installation || false,
  necessite_certification: props.initialProduct?.necessite_certification || false,
  prix_vente: props.initialProduct?.prix_vente || null,
  prix_location_jour: props.initialProduct?.prix_location_jour || null,
  prix_location_semaine: props.initialProduct?.prix_location_semaine || null,
  prix_location_mois: props.initialProduct?.prix_location_mois || null,
  caution_location: props.initialProduct?.caution_location || null,
  poids: props.initialProduct?.poids || null,
  dimensions: props.initialProduct?.dimensions || '',
})

const existingImages = ref(props.initialProduct?.images || [])
const selectedFiles = ref([])
const previews = ref([])
const fileInput = ref(null)

const triggerFileInput = () => fileInput.value?.click()

const handleFileChange = (event) => {
  const files = Array.from(event.target.files)
  selectedFiles.value = [...selectedFiles.value, ...files].slice(0, 5)
  previews.value = selectedFiles.value.map((file) => URL.createObjectURL(file))
}

const removeNewImage = (index) => {
  selectedFiles.value.splice(index, 1)
  previews.value.splice(index, 1)
}

const removeExistingImage = async (image) => {
  try {
    await deleteProductImage(props.initialProduct.slug, image.id)
    existingImages.value = existingImages.value.filter((i) => i.id !== image.id)
    toast.add({ title: 'Photo supprimée', color: 'success' })
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}

const loading = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true
  try {
    let product
    if (isEdit.value) {
      product = await updateProduct(props.initialProduct.slug, form)
    } else {
      product = await createProduct(form)
    }

    if (selectedFiles.value.length > 0) {
      const formData = new FormData()
      selectedFiles.value.forEach((file) => formData.append('images[]', file))
      await uploadImages(product.slug, formData)
    }

    toast.add({ title: isEdit.value ? 'Produit mis à jour' : 'Produit créé avec succès', color: 'success' })
    router.back()
  } catch (err) {
    errorMessage.value = err.data?.message || 'Erreur lors de l\'enregistrement.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UForm :state="form" @submit="handleSubmit" class="space-y-4">
    <UFormField label="Nom du produit" required>
      <UInput v-model="form.nom" class="w-full" />
    </UFormField>

    <UFormField label="Marque">
      <UInput v-model="form.marque" class="w-full" />
    </UFormField>

    <UFormField label="Description">
      <UTextarea v-model="form.description" class="w-full" />
    </UFormField>

    <UFormField label="Catégorie" required>
      <USelect v-model="form.category_id" :items="categoryOptions" class="w-full" />
    </UFormField>

    <UFormField label="Disponibilité" required>
      <USelect
        v-model="form.type_disponibilite"
        :items="[
          { label: 'Vente uniquement', value: 'vente' },
          { label: 'Location uniquement', value: 'location' },
          { label: 'Vente et location', value: 'les_deux' },
        ]"
        class="w-full"
      />
    </UFormField>

    <div class="grid grid-cols-2 gap-4">
      <UFormField v-if="form.type_disponibilite !== 'location'" label="Prix de vente (FCFA)">
        <UInput v-model.number="form.prix_vente" type="number" class="w-full" />
      </UFormField>
      <UFormField v-if="form.type_disponibilite !== 'vente'" label="Prix location / jour (FCFA)">
        <UInput v-model.number="form.prix_location_jour" type="number" class="w-full" />
      </UFormField>
    </div>

    <div v-if="form.type_disponibilite !== 'vente'" class="grid grid-cols-2 gap-4">
      <UFormField label="Prix location / semaine (FCFA)">
        <UInput v-model.number="form.prix_location_semaine" type="number" class="w-full" />
      </UFormField>
      <UFormField label="Prix location / mois (FCFA)">
        <UInput v-model.number="form.prix_location_mois" type="number" class="w-full" />
      </UFormField>
    </div>

    <UFormField v-if="form.type_disponibilite !== 'vente'" label="Caution location (FCFA)">
      <UInput v-model.number="form.caution_location" type="number" class="w-full" />
    </UFormField>

    <div class="grid grid-cols-2 gap-4">
      <UFormField label="Poids (kg)">
        <UInput v-model.number="form.poids" type="number" class="w-full" />
      </UFormField>
      <UFormField label="Dimensions">
        <UInput v-model="form.dimensions" placeholder="ex: 110x65x120 cm" class="w-full" />
      </UFormField>
    </div>

    <div class="flex items-center gap-6">
      <UCheckbox v-model="form.necessite_installation" label="Nécessite une installation" />
      <UCheckbox v-model="form.necessite_certification" label="Nécessite une certification" />
    </div>

    <!-- Photos existantes (mode édition) -->
    <UFormField v-if="existingImages.length" label="Photos actuelles">
      <div class="grid grid-cols-5 gap-3">
        <div
          v-for="image in existingImages"
          :key="image.id"
          class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
        >
          <img :src="image.url" class="w-full h-full object-cover" />
          <button
            type="button"
            class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs"
            @click="removeExistingImage(image)"
          >
            ✕
          </button>
        </div>
      </div>
    </UFormField>

    <!-- Ajout de nouvelles photos -->
    <UFormField :label="isEdit ? 'Ajouter des photos' : 'Photos du produit'" hint="5 maximum">
      <div
        class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors"
        @click="triggerFileInput"
      >
        <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="handleFileChange" />
        <UIcon name="i-lucide-image-plus" class="w-8 h-8 mx-auto text-gray-400 mb-2" />
        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Cliquez pour ajouter des photos</p>
        <p class="text-xs text-gray-400 mt-1">PNG, JPG jusqu'à 4 Mo · 5 photos max</p>
      </div>

      <div v-if="previews.length" class="grid grid-cols-5 gap-3 mt-4">
        <div
          v-for="(preview, index) in previews"
          :key="index"
          class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
        >
          <img :src="preview" class="w-full h-full object-cover" />
          <button
            type="button"
            class="absolute top-1 right-1 bg-black/60 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs"
            @click.stop="removeNewImage(index)"
          >
            ✕
          </button>
        </div>
      </div>
    </UFormField>

    <UAlert v-if="errorMessage" color="error" variant="soft" :title="errorMessage" />

    <UButton type="submit" block :loading="loading">
      {{ isEdit ? 'Enregistrer les modifications' : 'Créer le produit' }}
    </UButton>
  </UForm>
</template>