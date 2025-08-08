<template>
  <div id="app">
    DESDE VUEEEE
    <button @click="increment">Clicks: {{ count }}</button>
    <!-- <router-view /> -->
  </div>
</template>

<script setup>
import { ref } from "vue";

const count = ref(0);

const increment = () => count.value++;
</script>