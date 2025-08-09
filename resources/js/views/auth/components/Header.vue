<template>
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="flex justify-center">
      <div class="text-6xl mb-4">{{ icon }}</div>
    </div>
    <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
      {{ title }}
    </h2>
    <p class="mt-2 text-center text-sm text-gray-600">
      {{ subtitle }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: '📄'
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    required: true
  }
})
</script>

<style scoped>
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.text-6xl {
  animation: fadeInDown 0.6s ease-out;
}

h2 {
  animation: fadeInDown 0.6s ease-out 0.1s both;
}

p {
  animation: fadeInDown 0.6s ease-out 0.2s both;
}
</style>