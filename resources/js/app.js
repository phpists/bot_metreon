import Vue from 'vue'
import VueI18n from 'vue-i18n'
import axios from "axios";
import store from "./store";
import router from "./router";

import messages from "./lang";
import App from './components/App'


Vue.config.productionTip = false;

axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response.status === 422) {
            store.commit("setErrors", error.response.data.errors);
        } else if (error.response.status === 401) {
            store.commit("auth/setUserData", null);
            localStorage.removeItem("authToken");
            router.push({ name: "Login" });
        } else {
            return Promise.reject(error);
        }
    }
);

axios.interceptors.request.use(function(config) {
    config.headers.common = {
        "Authorization": `Bearer ${localStorage.getItem("authToken")}`,
        "Content-Type": "application/json",
        "Accept": "application/json"
    };

    return config;
});

Vue.use(VueI18n);
const i18n = new VueI18n({
    locale: 'ru', // set locale
    messages, // set locale messages
});

const app = new Vue({
    el: '#app',
    components: { App },
    router,
    store,
    i18n,
});

window.$vue = app;
