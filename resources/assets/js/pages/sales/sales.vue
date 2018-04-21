<template>
    <div class="container">


        <v-btn fab small dark color="indigo" @click="addModalInit">
            <v-icon dark>add</v-icon>
        </v-btn>


    </div>
</template>


<script>
    import { mapState } from 'vuex'
    export default {
        name:"Vanzari",
        data(){
            return {
                addModal:false

            }
        },
        computed:{
            ...mapState({
                offers: state => state.offers.offers
            })
        },

        mounted() {
            console.log('Component mounted.')
            this.$store.dispatch('getOffers')
        },

        methods: {

            addModalInit(){

            }


        }

        // props: ['roomTypes']
    }
</script>
