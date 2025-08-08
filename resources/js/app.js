import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";

// axios
window.axios = axios;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.withCredentials = true;

// main component
import App from "./App.vue";

// import routes
import routes from "./router/index.js";

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// create and mount the app
const app = createApp(App);
app.use(router);
app.mount("#app");
