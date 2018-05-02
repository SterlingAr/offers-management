<template>
    <div class="container">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="closeCreateSale" dark>
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
                            <!-- SALE FIELDS -->
                            <v-flex md6 xs12 >
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>perm_identity</v-icon>
                                        <v-toolbar-title> Detalii client</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                    </v-toolbar>
                                    <v-card-text >
                                        <v-form v-model="saleModel.options.valid">
                                            <v-text-field
                                                    label="Nume"
                                                    v-model="saleModel.first_name"
                                                    :rules="validationRules.nameRules"
                                                    :counter="10"
                                                    required
                                            ></v-text-field>
                                            <v-text-field
                                                    label="Prenume"
                                                    v-model="saleModel.last_name"
                                                    :rules="validationRules.nameRules"
                                                    :counter="10"
                                                    required
                                            ></v-text-field>
                                            <v-text-field
                                                    label="E-mail"
                                                    v-model="saleModel.email"
                                                    :rules="validationRules.emailRules"
                                                    required
                                            ></v-text-field>
                                            <v-text-field
                                                    label="Telefon"
                                                    v-model="saleModel.phone"
                                                    :rules="validationRules.phoneRules"
                                                    required
                                            ></v-text-field>
                                        </v-form>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <!-- STEPPER CONTAINER -->
                            <v-flex md6 xs12>
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title>Selectioneaza oferta, data si amenajarile respective</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                    </v-toolbar>
                                </v-card>

                                <v-stepper v-model="currentStep">
                                    <v-stepper-header>
                                        <v-stepper-step step="1" :complete="currentStep > 1">Alege oferta</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="2" :complete="currentStep > 2">Alege o data</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="3" :complete="currentStep > 3">Alege o locatie</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="4" :complete="currentStep > 4">Alege o camera</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="5">Name of step 3</v-stepper-step>
                                        <v-divider></v-divider>
                                    </v-stepper-header>
                                </v-stepper>

                                <!-- Offers table -->
                                <v-flex md12 xs12 v-if="!this.selectingDates">
                                    <v-card light>
                                        <v-card-text>
                                            <v-card-title>
                                                <v-spacer></v-spacer>
                                                <v-text-field
                                                        append-icon="search"
                                                        label="Cauta oferta"
                                                        single-line
                                                        hide-details
                                                        @input="indexTable()"
                                                        v-model.lazy="search"
                                                ></v-text-field>
                                            </v-card-title>
                                            <v-data-table
                                                    :headers="offersHeaders"
                                                    :items="offers"
                                                    :search="search"
                                            >
                                                <template slot="items" slot-scope="props">

                                                    <td>{{ props.item.id }}</td>
                                                    <td class="text-xs-left">{{ props.item.title }}</td>
                                                    <td class="text-xs-left">{{ props.item.description }}</td>
                                                    <td class="text-xs-left">
                                                        <v-btn color="indigo" dark @click="fetchDates(props.item.id)">
                                                         Alege date
                                                        </v-btn>
                                                    </td>
                                                </template>
                                                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                                                    Cautarea pentru "{{ search }}" nu are rezultate.
                                                </v-alert>
                                            </v-data-table>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                                <!-- Dates -->
                                <v-flex md12 xs12 v-if="!this.selectingLocations && this.selectingDates">
                                    <v-card light>
                                        <v-toolbar color="indigo" dark>
                                            <v-icon>event</v-icon>
                                            <v-toolbar-title> Listare date</v-toolbar-title>
                                            <v-spacer></v-spacer>
                                            <v-btn icon dark right>
                                                <v-icon>add</v-icon>
                                            </v-btn>
                                        </v-toolbar>
                                        <v-card-text>
                                            <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                            <v-data-table
                                                    :headers="datesTableHeaders"
                                                    :items="dates"
                                                    class="elevation-1"
                                            >
                                                <template slot="items" slot-scope="props">
                                                    <td class="text-xs-left">{{ props.item.id }}</td>
                                                    <td class="text-xs-left">{{friendlyDateFormat(props.item.start_date)}}</td>
                                                    <td class="text-xs-left">{{friendlyDateFormat(props.item.end_date)}}</td>
                                                </template>
                                            </v-data-table>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                                <!-- OfferDates table -->
                                <!-- Locations table -->
                                <!-- Rooms table -->
                                <!-- Individual table -->
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
    import axios from 'axios'

    export default{
      name:"Vanzari",
      metaInfo () {
        return { title: "Vanzari" }
      },

      data(){
        return{
          offers: [],
          dates:[],
          search: '',
          busy: false,
          busyOffers: false,
          selectingDates: false,
          selectingLocations: false,
          selectingRooms: false,
          selectingIndividualRooms:false,
          currentStep: 0,

          saleModel:{
            first_name: "",
            last_name: "",
            email: "",
            phone: "",
            offer_id: "",
            location_id: "",
            offer_date_id: "",
            total_person_number: 0,
            payment_status: "notpaid",
            coupon_code: "",
            total_amount:0.00,
            options: {
              valid: false,
            }
          },
          validationRules: {
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
          },
          offersHeaders: [
            { text: 'ID', align: 'left', value: 'id'},
            { text: 'Titlu oferta', value: 'title'},
            { text: 'Descriere', value: 'description' },
          ],
          datesTableHeaders: [
            {text:'ID', value:'id'},
            {text:'Data inceput', value:'start_date'},
            {text:'Data sfarsit', value:'end_date'}
          ],
          locationsTableHeaders: [
            {text:'ID', value:'id'},
            {text:'Nume', value:'name'},
          ],
          roomsTableHeaders: [
            {text:'ID', value:'id'},
            {text:'Tip de camera', value:'type'},
          ],
          individualRoomHeaders: [
            {text: 'ID', value: 'id'},
            {text: 'Pret pe persoana', value:'price_person'},
            {text: 'Persoane pe camera', value:'person_number'}
          ],
        }
      },

      methods: {

        async indexTable(){ //query locations using the search
          this.busyOffers = true;
          try {

            const {data} = await axios.post('/api/offers/search', { query: this.search})
            this.offers = data.offers;
            // this.reindex = false;

          } catch(e) {
            console.log(e);
          }
          this.busyOffers = false;

        },



        //
        closeCreateSale(){
          this.$emit('update:dialog',false);
        }
      },

      props: ['dialog']

    }

</script>