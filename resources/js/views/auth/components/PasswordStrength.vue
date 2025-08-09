<template>
  <div v-if="password.length > 0" class="mt-2">
    <div class="flex space-x-1 mb-2">
      <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-colors duration-300"
        :class="getBarColor(i)"></div>
    </div>

    <div class="flex justify-between items-center">
      <span class="text-sm" :class="strengthTextClass">
        {{ strengthText }}
      </span>
      <span v-if="score >= 3" class="text-green-600 text-sm">
        ✓ Segura
      </span>
    </div>

    <div class="mt-3 space-y-1">
      <div class="flex items-center text-xs">
        <CheckIcon v-if="checks.length" class="h-2.5 w-2.5 text-green-500 mr-1.5" />
        <XMarkIcon v-else class="h-2.5 w-2.5 text-gray-400 mr-1.5" />
        <span :class="checks.length ? 'text-green-600' : 'text-gray-500'">
          Al menos 8 caracteres
        </span>
      </div>

      <div class="flex items-center text-xs">
        <CheckIcon v-if="checks.uppercase" class="h-2.5 w-2.5 text-green-500 mr-1.5" />
        <XMarkIcon v-else class="h-2.5 w-2.5 text-gray-400 mr-1.5" />
        <span :class="checks.uppercase ? 'text-green-600' : 'text-gray-500'">
          Una letra mayúscula
        </span>
      </div>

      <div class="flex items-center text-xs">
        <CheckIcon v-if="checks.lowercase" class="h-2.5 w-2.5 text-green-500 mr-1.5" />
        <XMarkIcon v-else class="h-2.5 w-2.5 text-gray-400 mr-1.5" />
        <span :class="checks.lowercase ? 'text-green-600' : 'text-gray-500'">
          Una letra minúscula
        </span>
      </div>

      <div class="flex items-center text-xs">
        <CheckIcon v-if="checks.number" class="h-2.5 w-2.5 text-green-500 mr-1.5" />
        <XMarkIcon v-else class="h-2.5 w-2.5 text-gray-400 mr-1.5" />
        <span :class="checks.number ? 'text-green-600' : 'text-gray-500'">
          Un número
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  password: {
    type: String,
    required: true
  }
})

const checks = computed(() => ({
  length: props.password.length >= 8,
  uppercase: /[A-Z]/.test(props.password),
  lowercase: /[a-z]/.test(props.password),
  number: /\d/.test(props.password),
  special: /[!@#$%^&*(),.?":{}|<>]/.test(props.password)
}))

const score = computed(() => {
  const checkValues = Object.values(checks.value)
  return checkValues.filter(Boolean).length
})

const strengthText = computed(() => {
  if (score.value <= 1) return 'Muy débil'
  if (score.value === 2) return 'Débil'
  if (score.value === 3) return 'Media'
  if (score.value === 4) return 'Fuerte'
  return 'Muy fuerte'
})

const strengthTextClass = computed(() => {
  if (score.value <= 1) return 'text-red-600'
  if (score.value === 2) return 'text-orange-600'
  if (score.value === 3) return 'text-yellow-600'
  return 'text-green-600'
})

const getBarColor = (index) => {
  if (index <= score.value) {
    if (score.value <= 1) return 'bg-red-500'
    if (score.value === 2) return 'bg-orange-500'
    if (score.value === 3) return 'bg-yellow-500'
    return 'bg-green-500'
  }
  return 'bg-gray-200'
}
</script>