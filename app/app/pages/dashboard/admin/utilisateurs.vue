<script setup>
definePageMeta({ middleware: 'auth', layout: 'dashboard' })

const { getUsers, updateUser, deleteUser } = useUsers()
const authStore = useAuthStore()
const toast = useToast()

const { data: users, refresh } = await useAsyncData('admin-users', () => getUsers())

const roleColor = (role) => ({
  admin: 'error',
  vendeur: 'info',
  technicien: 'warning',
  client: 'neutral',
}[role] || 'neutral')

const handleRoleChange = async (userId, newRole) => {
  try {
    await updateUser(userId, { role: newRole })
    toast.add({ title: 'Rôle mis à jour', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}

const handleDelete = async (userId) => {
  if (userId === authStore.user.id) {
    toast.add({ title: 'Impossible de supprimer votre propre compte', color: 'error' })
    return
  }
  try {
    await deleteUser(userId)
    toast.add({ title: 'Utilisateur supprimé', color: 'success' })
    refresh()
  } catch (err) {
    toast.add({ title: 'Erreur', description: err.data?.message, color: 'error' })
  }
}

const roleOptions = [
  { label: 'Client', value: 'client' },
  { label: 'Vendeur', value: 'vendeur' },
  { label: 'Technicien', value: 'technicien' },
  { label: 'Admin', value: 'admin' },
]
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Utilisateurs</h1>

    <UCard>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Nom</th>
            <th class="pb-2">Email</th>
            <th class="pb-2">Rôle</th>
            <th class="pb-2">Inscrit le</th>
            <th class="pb-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users?.data" :key="user.id" class="border-b last:border-0">
            <td class="py-3">{{ user.nom }}</td>
            <td class="py-3 text-gray-500">{{ user.email }}</td>
            <td class="py-3">
              <USelect
                :model-value="user.role"
                :items="roleOptions"
                size="xs"
                class="w-36"
                @update:model-value="(val) => handleRoleChange(user.id, val)"
              />
            </td>
            <td class="py-3 text-gray-500">{{ new Date(user.created_at).toLocaleDateString('fr-FR') }}</td>
            <td class="py-3 text-right">
              <UButton
                icon="i-lucide-trash-2" variant="ghost" color="error" size="xs"
                @click="handleDelete(user.id)"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </UCard>
  </div>
</template>