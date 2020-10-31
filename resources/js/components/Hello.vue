<template>
    <p v-if="user">Hello World!</p>
    <p v-else>You not authorized!</p>
</template>
<script>
    import { mapGetters, mapActions } from "vuex";
    import {eventBus} from '../app'
    export default {
        name: "Hello",
        data() {
            return {
                success: null,
                error: null
            };
        },
        computed: {
            ...mapGetters("auth", ["user"])
        },
        methods: {
            ...mapActions("auth", ["sendVerifyResendRequest"]),
            verifyResend() {
                this.sendVerifyResendRequest().then(() => {
                    this.success = "Message";
                }).catch(error => {
                    this.error = "Error";
                    console.log(error);
                })
            }
        }
    }
</script>
