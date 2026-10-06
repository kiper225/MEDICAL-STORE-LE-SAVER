<script setup>
const props = defineProps({ target: { type: String, required: true } })

const remaining = ref({ h: 0, m: 0, s: 0 })
let timer = null

const update = () => {
  const diff = new Date(props.target).getTime() - Date.now()
  if (diff <= 0) {
    remaining.value = { h: 0, m: 0, s: 0 }
    return
  }
  remaining.value = {
    h: Math.floor(diff / 3600000),
    m: Math.floor((diff % 3600000) / 60000),
    s: Math.floor((diff % 60000) / 1000),
  }
}

onMounted(() => {
  update()
  timer = setInterval(update, 1000)
})
onUnmounted(() => clearInterval(timer))

const pad = (n) => String(n).padStart(2, '0')
</script>

<template>
  <div class="flex items-center gap-1 font-mono text-sm font-bold">
    <span class="bg-white/20 rounded px-1.5 py-0.5">{{ pad(remaining.h) }}</span>:
    <span class="bg-white/20 rounded px-1.5 py-0.5">{{ pad(remaining.m) }}</span>:
    <span class="bg-white/20 rounded px-1.5 py-0.5">{{ pad(remaining.s) }}</span>
  </div>
</template>