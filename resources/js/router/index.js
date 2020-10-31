import Vue from "vue";
import VueRouter from "vue-router";

Vue.use(VueRouter);

/**
 * Пользователь гость
 *
 * @param to
 * @param from
 * @param next
 * @returns {*}
 */
const guest = (to, from, next) => {
    if (!localStorage.getItem("authToken")) {
        return next();
    } else {
        return next("/");
    }
};

/**
 * Пользователь авторизирован
 *
 * @param to
 * @param from
 * @param next
 * @returns {*}
 */
const auth = (to, from, next) => {
    if (localStorage.getItem("authToken")) {
        return next();
    } else {
        return next("/login");
    }
};

import NotFound from "../components/NotFound";
import Home from "../components/Home";
import Hello from "../components/Hello";

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home
    },
    {
        path: '/hello',
        name: 'hello',
        component: Hello,
        //beforeEnter: auth,
    },
    {
        path: '*',
        component: NotFound,
        name: "not_found"
    }
];

const router = new VueRouter({
    mode: "history",
    routes: routes
});

export default router;
