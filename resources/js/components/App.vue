<template>
    <div>
        <h1>Vue Router Demo App</h1>
        <p>
            <a v-on:click="language('ru')">RU</a>
            <a v-on:click="language('en')">EN</a>
        </p>
        <p>
            <router-link :to="{ name: 'home' }">Home</router-link> |
            <router-link :to="{ name: 'hello' }">Hello World</router-link>
        </p>

        <div class="container">
            <router-view></router-view>
        </div>
    </div>
</template>
<script>
    export default {
        mounted() {
            console.log("App mounted!");
            console.log(this.$route.fullPath);
        },
        methods: {
            language(lang) {
                this.$i18n.locale = lang;
                console.log(this.$store.state.auth.getters.user());
            }
        }
    }
</script>
