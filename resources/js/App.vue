<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <template v-if="isAuthenticated">
      <Navbar />
      <main class="pt-16">
        <router-view />
      </main>
    </template>

    <template v-else>
      <router-view />
    </template>

    <LoadingSpinner v-if="globalLoading" :fullscreen="true" text="Cargando..." />
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useAuth } from './composables/useAuth'
import Navbar from './components/layout/Navbar.vue'
import LoadingSpinner from './components/ui/LoadingSpinner.vue'

const { checkAuth, isAuthenticated, loading } = useAuth()

const globalLoading = computed(() => loading.value)

onMounted(async () => {
  await checkAuth()
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  line-height: 1.6;
  color: #374151;
}

#app {
  min-height: 100vh;
}

::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>