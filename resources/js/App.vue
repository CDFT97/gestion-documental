<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { ref } from "vue";

const count = ref(0);

const increment = () => count.value++;
</script>