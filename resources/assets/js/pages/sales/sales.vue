<template>
    <div class="container">


        <v-btn fab small dark color="indigo" @click="addModalInit">
            <v-icon dark>add</v-icon>
        </v-btn>

        <v-dialog
                v-model="addModal"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
        <v-card tile>
            <v-toolbar card dark color="primary">
                <v-btn icon @click.native="addModal = false" dark>
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>Adauaga Vanzare</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-toolbar-items>
                    <v-btn dark flat @click.native="dialog = false">Adauga</v-btn>
                </v-toolbar-items>

            </v-toolbar>
            <v-card-text>
                <v-container fluid  grid-list-md>
                <v-layout row wrap>
                    <v-flex md6 xs12 >
                        <v-card light>
                            <v-toolbar color="indigo" dark>
                                <v-icon>perm_identity</v-icon>
                                <v-toolbar-title> Detalii client</v-toolbar-title>
                                <v-spacer></v-spacer>

                            </v-toolbar>
                            <v-card-text >
                                <v-form v-model="sale.valid">
                                    <v-text-field
                                            label="Nume"
                                            v-model="sale.first_name"
                                            :rules="validationRules.nameRules"
                                            :counter="10"
                                            required
                                    ></v-text-field>
                                    <v-text-field
                                            label="Prenume"
                                            v-model="sale.last_name"
                                            :rules="validationRules.nameRules"
                                            :counter="10"
                                            required
                                    ></v-text-field>
                                    <v-text-field
                                            label="E-mail"
                                            v-model="sale.email"
                                            :rules="validationRules.emailRules"
                                            required
                                    ></v-text-field>
                                    <v-text-field
                                            label="Telefon"
                                            v-model="sale.phone"
                                            :rules="validationRules.phoneRules"
                                            required
                                    ></v-text-field>
                                </v-form>

                            </v-card-text>
                        </v-card>
                    </v-flex>

                    <v-flex md6 xs12 >
                        <v-card light>
                            <progress-bar :show="busy"></progress-bar>
                            <v-toolbar color="indigo" dark>
                                <v-icon>card_travel</v-icon>
                                <v-toolbar-title> Detalii oferta</v-toolbar-title>
                                <v-spacer></v-spacer>

                            </v-toolbar>
                            <v-card-text >
                                <v-form v-model="sale.valid">
                                <v-select
                                        :items="offers"
                                        item-text="title"
                                        item-value="id"
                                        v-model="sale.offer_id"
                                        label="Selecteaza oferta"
                                        autocomplete
                                        @change="getOfferDetails"
                                ></v-select>

                                    <v-select v-if="selectedOfferDates.length>0"
                                            :items="selectedOfferDates"
                                            item-text="range_date"
                                            v-model="sale.offer_date_id"
                                            label="Selecteaza perioda excursiei"
                                            autocomplete
                                            @change="showLocations"
                                    ></v-select>

                                    <v-select v-if="selectedOfferLocations.length>0"
                                              :items="selectedOfferLocations"
                                              item-text="name"
                                              v-model="sale.location_id"
                                              label="Selecteaza hotelul"
                                              autocomplete
                                              @change="getAvailableRooms"
                                    ></v-select>





                                </v-form>

                            </v-card-text>
                        </v-card>
                    </v-flex>







                </v-layout>
                </v-container>
            </v-card-text>

        </v-card>

        </v-dialog>


    </div>
</template>


<script>
    import { mapState } from 'vuex'
    import axios from 'axios'
    export default {
        name:"Vanzari",
        data(){
            return {
                addModal:false,
                busy:false,
                sale:{
                    valid:false,
                    first_name:"",
                    last_name:"",
                    email:"",
                    phone:"",
                    offer_id:"",
                    location_id:"",
                    offer_date_id:"",
                    total_person_number:0,
                    payment_status:"notpaid",
                    coupon_code:"",
                    total_amount:0.00

                },
                selectedOfferDetails:{

                },
                selectedOfferDates:[],
                selectedOfferLocations:[],

                validationRules:{
                    nameRules: [
                        v => !!v || 'Numele e obligatoriu',
                        v => v.length <= 10 || 'Name must be less than 10 characters'
                    ],
                    phoneRules: [
                        v => !!v || 'Numarul de telefon e obligatoriu',
                    ],
                    emailRules: [
                        v => !!v || 'Adresa de e-mail e obligatorie',
                        v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'Adresa de e-mail trebuie a fie valida'
                    ]
                }

            }
        },
        computed:{
            ...mapState({
                offers: state => state.offers.offers
            })
        },

        mounted() {
            console.log('Component mounted.');
            this.$store.dispatch('getOffers')
        },

        methods: {

            addModalInit(){
                this.addModal=true;

            },
            async  getOfferDetails(offer_id){

                    this.selectedOfferDates=[];
                    this.selectedOfferLocations=[];
                    this.busy=true;

                try {
                    const { data } = await axios.get('/api/offers/'+offer_id)

                    this.selectedOfferDetails=data.offer
                    this.selectedOfferDates=data.offer.dates
                    this.busy=false;

                } catch (e) {
                    this.busy=false;
                }

            },

            showLocations(selectedDateRange){

               this.selectedOfferLocations=selectedDateRange.locations

           },
           async getAvailableRooms(selectedLocation){
               this.busy=true;

               try {
                   const { data } = await axios.get('/api/available-rooms',{params:{
                           offer_id:this.sale.offer_id,
                           location_id:selectedLocation.id,
                           offer_date_id:this.sale.offer_date_id.id,

                       }})

                   this.busy=false;

               } catch (e) {

                   console.log(e.message);
                   this.busy=false;
               }


            }

        }

        // props: ['roomTypes']
    }
</script>
