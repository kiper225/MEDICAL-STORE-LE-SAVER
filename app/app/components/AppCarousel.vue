<script setup>
const props = defineProps({
  slides: { type: Array, required: true },
  interval: { type: Number, default: 4000 },
})

const current = ref(0)
const isHovered = ref(false)
let timer = null

const next = () => { current.value = (current.value + 1) % props.slides.length }
const prev = () => { current.value = (current.value - 1 + props.slides.length) % props.slides.length }
const goTo = (i) => { current.value = i }

const startAutoplay = () => {
  timer = setInterval(next, props.interval)
}
const stopAutoplay = () => clearInterval(timer)

const handleMouseEnter = () => {
  isHovered.value = true
  stopAutoplay()
}
const handleMouseLeave = () => {
  isHovered.value = false
  startAutoplay()
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
  <div
    class="relative rounded-lg overflow-hidden h-full"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <div
      class="flex transition-transform duration-500 ease-out h-full"
      :style="{ transform: `translateX(-${current * 100}%)` }"
    >
      <NuxtLink
        v-for="(slide, i) in slides"
        :key="i"
        :to="slide.link"
        class="w-full shrink-0 relative h-full bg-cover bg-center"
        :class="slide.backgroundImage"
      >
        <!-- Overlay dégradé pour la lisibilité du texte -->
        <div class="absolute inset-0 opacity-20" :class="slide.bgClass" />

        <div class="relative z-10 flex flex-col justify-center h-full p-8 text-white">
          <p class="text-sm font-medium opacity-80 mb-2">{{ slide.eyebrow }}</p>
          <h2 class="text-2xl md:text-3xl font-bold leading-tight mb-3">{{ slide.title }}</h2>
          <p class="opacity-90 mb-4 text-sm md:text-base">{{ slide.subtitle }}</p>
          <span class="inline-flex items-center gap-1 text-sm font-semibold w-fit bg-white/20 px-3 py-1.5 rounded">
            {{ slide.cta }} <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
          </span>
        </div>
      </NuxtLink>
    </div>

    <Transition name="fade-arrow">
      <button
        v-if="isHovered"
        class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm z-20"
        @click="prev"
      >
        <UIcon name="i-lucide-chevron-left" class="w-5 h-5" />
      </button>
    </Transition>
    <Transition name="fade-arrow">
      <button
        v-if="isHovered"
        class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm z-20"
        @click="next"
      >
        <UIcon name="i-lucide-chevron-right" class="w-5 h-5" />
      </button>
    </Transition>

    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-20">
      <button
        v-for="(_, i) in slides"
        :key="i"
        class="w-2 h-2 rounded-full transition-colors"
        :class="i === current ? 'bg-white' : 'bg-white/40'"
        @click="goTo(i)"
      />
    </div>
  </div>
</template>