<style scoped>

    .stepper__content{
        padding: 0 !important;
    }

</style>

<template>
    <div class="container">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <progress-bar :show="busy"></progress-bar>
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="closeCreateSale" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Adauaga Vanzare</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createSale">Adauga</v-btn>
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
                                        <v-form
                                                v-model="saleModel.options.valid"
                                                ref="saleFields"
                                        >
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
                            <!-- Listare camere adaugate -->
                            <v-flex md6 xs12 v-if="!addingRoomsToSale">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title>
                                            <span v-if="Object.keys(this.selectedOffer).length === 0">
                                                Nu ati ales nici o oferta
                                            </span>
                                            <span v-else>
                                                Camere pentru oferta {{selectedOffer.title}}
                                            </span>
                                        </v-toolbar-title>
                                        <v-btn  color="blue" dark  @click="selectOffer" v-if="Object.keys(this.selectedOffer).length === 0">
                                            Alege oferta.
                                        </v-btn>
                                        <v-btn color="red lighten-2" dark @click="changeOffer" v-else>
                                            Schimbati oferta
                                        </v-btn>
                                        <v-spacer></v-spacer>
                                        <v-btn color="blue" right @click="showStepper" v-if="Object.keys(this.selectedOffer).length">
                                           Adauga camera
                                        </v-btn>
                                    </v-toolbar>
                                </v-card>
                                <v-card-text>
                                    <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                    <v-data-table
                                            :headers="roomsInSaleHeaders"
                                            :items="allocatedRooms"
                                            class="elevation-1"
                                    >
                                        <template slot="items" slot-scope="props">
                                            <td class="text-xs-center">{{ props.item.offer_dates_location_room_id }}</td>
                                            <td class="text-xs-center">{{dateConcat(props.item.date)}}</td>
                                            <td class="text-xs-center">{{props.item.location.name}}</td>
                                            <td class="text-xs-center">{{props.item.type}}</td>
                                            <td class="text-xs-center">{{props.item.persons_going}}</td>
                                            <td class="text-xs-center">
                                                <span v-for="name in props.item.persons_names">
                                                    {{ name }},
                                                </span>
                                            </td>
                                            <td class="text-xs-center">{{props.item.price_person}}</td>
                                            <td class="text-xs-center">{{ totalPrice(props.item.price_person, props.item.persons_going)}}</td>
                                            <td class="text-xs-center">
                                                <v-btn icon class="mx-0" @click="editRoom(props.item)">
                                                    <v-icon color="teal">edit</v-icon>
                                                </v-btn>
                                                <v-btn icon class="mx-0" @click="removeRoomFromTableDialog(props.item)">
                                                    <v-icon color="pink">delete</v-icon>
                                                </v-btn>
                                            </td>
                                        </template>
                                    </v-data-table>
                                </v-card-text>
                            </v-flex>
                            <!-- STEPPER CONTAINER -->
                            <v-flex md6 xs12 v-if="addingRoomsToSale">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title>Selectioneaza data, locatia si amenajarile respective</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn class="left" color="white" large flat  light @click="closeStepper">
                                            Terminare
                                        </v-btn>
                                    </v-toolbar>
                                </v-card>
                                <v-stepper v-model="currentStep">
                                    <v-stepper-header>
                                        <v-stepper-step step="1" :complete="currentStep > 1">Alege o data</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="2" :complete="currentStep > 2">Alege o locatie</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="3" :complete="currentStep > 3"> Alege tip de camera</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="4">Camere/locuri pentru vanzare</v-stepper-step>
                                    </v-stepper-header>
                                    <v-stepper-items>
                                        <!-- Dates -->
                                        <v-stepper-content step="1" transition="fade-transition">
                                            <v-flex md12 xs12>
                                                <p class="headline">Date pentru oferta {{ selectedOffer.title}}</p>
                                                <v-card-text>
                                                    <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                                    <v-data-table
                                                            :headers="datesTableHeaders"
                                                            :items="dates"
                                                            class="elevation-1"
                                                            no-data-text="Nu exista date pentru oferta aleasa"
                                                    >
                                                        <template slot="items" slot-scope="props">
                                                            <td  @click="selectedDate = props.item">
                                                                <v-radio-group
                                                                        v-model="selectedDate"
                                                                        name="rowSelector">
                                                                    <v-radio :value="props.item"/>
                                                                </v-radio-group>
                                                            </td>
                                                            <td class="text-xs-left">{{ props.item.id }}</td>
                                                            <td class="text-xs-left">{{friendlyDateFormat(props.item.start_date)}}</td>
                                                            <td class="text-xs-left">{{friendlyDateFormat(props.item.end_date)}}</td>
                                                        </template>
                                                    </v-data-table>
                                                </v-card-text>
                                            </v-flex>
                                            <v-btn  class="left" color="red" large flat  light right @click="closeStepper">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedDate).length === 0"  class="left" color="green" large flat  @click="locationsStep">
                                                Inainte
                                            </v-btn>
                                        </v-stepper-content>
                                        <!-- Locations -->
                                        <v-stepper-content step="2">
                                            <!--<v-flex md6 xs12 v-if="selectedDate.locations.length > 0">-->
                                            <v-flex md12 xs12 lg12>
                                                    <p class="headline">Locatii pentru Data {{dateConcat(selectedDate)}}</p>
                                                    <v-card-text>
                                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                                        <v-data-table
                                                                :headers="locationsTableHeaders"
                                                                :items="selectedDate.locations"
                                                                class="elevation-1"
                                                                no-data-text="Nu exista nici o locatie disponibila pentru aceasta data"
                                                        >
                                                            <template slot="items" slot-scope="props">
                                                                <td  @click="selectedLocation = props.item">
                                                                    <v-radio-group
                                                                            v-model="selectedLocation"
                                                                            name="rowSelector">
                                                                        <v-radio :value="props.item"/>
                                                                    </v-radio-group>
                                                                </td>
                                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                                <td class="text-xs-left">{{ props.item.name }}</td>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                            </v-flex>
                                            <v-btn  class="left" color="red" large flat  light right @click="datesStep">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedLocation).length === 0"  class="left" color="green" large flat  @click="roomsStep">
                                                Inainte
                                            </v-btn>
                                        </v-stepper-content>
                                        <!-- Rooms -->
                                        <v-stepper-content step="3">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Tipuri de camere disponibile pentru locatia {{selectedLocation.name}}</p>
                                                <v-card-text>
                                                    <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                                    <v-data-table
                                                            :headers="roomsTableHeaders"
                                                            :items="selectedLocation.rooms"
                                                            class="elevation-1"
                                                            no-data-text="Nu exista nici un tip de camera disponibil la aceasta locatie"
                                                    >
                                                        <template slot="items" slot-scope="props">
                                                            <td  @click="selectedRoom = props.item">
                                                                <v-radio-group
                                                                        v-model = "selectedRoom"
                                                                        name = "rowSelector">
                                                                    <v-radio :value = "props.item"/>
                                                                </v-radio-group>
                                                            </td>
                                                            <td class="text-xs-left">{{ props.item.id }}</td>
                                                            <td class="text-xs-left">{{ props.item.type }}</td>
                                                        </template>
                                                    </v-data-table>
                                                </v-card-text>
                                            </v-flex>
                                            <v-btn  class="left" color="red" large flat  light right @click="locationsStep">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedRoom).length === 0"  class="left" color="green" large flat  @click="individualRoomsStep">
                                                Inainte
                                            </v-btn>

                                        </v-stepper-content>
                                        <!-- individual rooms -->
                                        <v-stepper-content step="4">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Camere individuale de tip {{selectedRoom.type}} pentru locatia {{selectedLocation.name}} in data de {{dateConcat(selectedDate)}}</p>
                                                <v-card-text>
                                                    <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                                    <v-data-table
                                                            :headers="individualRoomHeaders"
                                                            :items="selectedRoom.individualRooms"
                                                            class="elevation-1"
                                                            no-data-text="Nu exista nici o camera disponibila pentru acest tip de camera"
                                                    >
                                                        <template slot="items" slot-scope="props">
                                                            <td class="text-xs-left">{{ props.item.id }}</td>
                                                            <td class="text-xs-left">{{ props.item.price_person }}</td>
                                                            <td class="text-xs-left">{{ props.item.person_number }}</td>
                                                            <td class="text-xs-left">{{ props.item.vacant_places }}</td>
                                                            <td class="text-xs-right">
                                                                <v-btn icon class="mx-0"  @click="addRoomToSaleDialog(props.item)" v-if="!roomAlreadyAdded(props.item.id)">
                                                                    <v-icon color="teal">add</v-icon>
                                                                </v-btn>
                                                                <div v-if="roomAlreadyAdded(props.item.id)">
                                                                    <v-btn icon class="mx-0" @click="editRoomFromStepper(props.item)" >
                                                                        <v-icon color="teal">edit</v-icon>
                                                                    </v-btn>
                                                                    <v-btn icon class="mx-0" @click="removeRoomDialog(props.item)">
                                                                        <v-icon color="pink">delete</v-icon>
                                                                    </v-btn>
                                                                </div>
                                                            </td>
                                                        </template>
                                                    </v-data-table>
                                                </v-card-text>
                                            </v-flex>
                                            <v-btn  class="left" color="red" large flat  light @click="roomsStep">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>

                                        </v-stepper-content>
                                    </v-stepper-items>
                                </v-stepper>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- Select offer -->
        <v-dialog
            max-width="700px"
            v-model="offerModel.options.changingOffer || offerModel.options.selectingOffer "
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="headline" >
                        {{offerDialogTitle}}
                    </span>
                </v-card-title>

                <v-card-text>
                    <v-flex md12 xs12 lg12 >
                        <!--<v-card light>-->
                        <v-card-text>
                                <v-text-field
                                        append-icon="search"
                                        label="Cauta oferta"
                                        single-line
                                        hide-details
                                        @input="indexTable()"
                                        v-model.lazy="search"
                                ></v-text-field>
                            <v-alert class="left" :value="true" v-if="offerModel.options.changingOffer" type="error">
                                Daca schimbati oferta, toate camerele adaugate vor fi sterse din vanzare.
                            </v-alert>
                            <v-data-table
                                    :headers="offersHeaders"
                                    :items="offers"
                                    no-data-text="Nu exista nici o oferta disponibila"

                            >
                                <template slot="items" slot-scope="props">
                                    <td  @click="temporalOffer = props.item">
                                        <v-radio-group
                                                v-model="temporalOffer"
                                                name="rowSelector">
                                            <v-radio :value="props.item"/>
                                        </v-radio-group>
                                    </td>
                                    <td>{{ props.item.id }}</td>
                                    <td class="text-xs-left">{{ props.item.title }}</td>
                                    <td class="text-xs-left">{{ props.item.description }}</td>
                                </template>
                                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                                    Cautarea pentru "{{ search }}" nu are rezultate.
                                </v-alert>
                            </v-data-table>
                        </v-card-text>
                    </v-flex>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="closeOfferDialog">Close</v-btn>
                    <v-btn color="blue darken-1" @click="updateOffer" v-if="offerModel.options.selectingOffer">Alege</v-btn>
                    <v-btn color="blue darken-1" @click="updateOffer" v-else>Actualizeaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Add room to sale dialog -->
        <v-dialog
                max-width="500px"
                persistent
                v-model="roomForSaleModel.options.add || roomForSaleModel.options.edit"
        >
            <v-card>
                <v-card-title>
                    <span class="headline">{{ roomDialogTitle }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form
                            v-model="roomForSaleModel.options.valid"
                            ref="roomSaleFields"
                            lazy-validation
                    >

                        <v-flex xs12>
                            <v-text-field
                                    label="Numar de persoane"
                                    v-model.number="roomForSaleModel.persons_going"
                                    :rules="validationRules.personsGoing"
                                    required
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-select
                                    v-model="roomForSaleModel.persons_names"
                                    label="Numele persoanelor care merg"
                                    :rules="validationRules.personsNames"
                                    chips
                                    tags
                                    clearable
                                    required
                            >
                                <template slot="selection" slot-scope="data">
                                    <v-chip
                                            :selected="data.selected"
                                            close
                                            @input="removePerson(data.item)"
                                    >
                                        <strong>{{ data.item }}</strong>&nbsp;
                                    </v-chip>
                                </template>
                            </v-select>
                        </v-flex>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="clearRoomForSaleModel">Close</v-btn>
                    <v-btn  :disabled="!roomForSaleModel.options.valid" color="blue darken-1" flat @click="addRoomToSale" v-if="roomForSaleModel.options.add">Salveaza</v-btn>
                    <v-btn  :disabled="!roomForSaleModel.options.valid" color="blue darken-1" flat @click="updateRoomFromSale" v-else>Actualizeaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Confirm remove room from sale dialog -->
        <v-dialog v-model="roomForSaleModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere camera?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearRoomForSaleModel">Inchide</v-btn>
                    <v-btn flat large color="error" @click="removeRoom">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
    import axios from 'axios'
    import moment from 'moment'
    import {mapActions, mapGetters} from 'vuex'

    export default{
      name:"SalesNew",
      metaInfo () {
        return { title: "Vanzari" }
      },

      mounted(){

      },

      data(){
        return{

          addingRoomsToSale: false,
          addRoomDialog: false,
          editRoomDialog: false,

          temporalOffer: {},

          //at the end, should choose between object or just id.
          selectedOffer: {},
          selectedDate: {},
          selectedLocation: {},
          selectedRoom: {},
          selectedIndividualRoom: {},

          allocatedRooms: [],

          // offers:[],
          dates:[],
          search: '',
          busy: false,
          busyOffers: false,
          selectingDates: false,
          selectingLocations: false,
          selectingRooms: false,
          selectingIndividualRooms:false,
          currentStep: 0,

          offerModelDefault: {
            options:{
              selectingOffer: false,
              changingOffer: false,
            }
          },
          offerModel: {
            options:{
              selectingOffer: false,
              changingOffer: false,
            }
          },

          saleModelDefault: {
            first_name: "",
            last_name: "",
            email: "",
            phone: "",
            offer_id: "",
            offer_date_id: "",
            location_id: "",
            total_person_number: 0,
            payment_status: "notpaid",
            coupon_code: "",
            total_amount:0.00,
            options: {
              valid: false,
            }
          },

          roomForSaleModelDefault: {
            offer_dates_location_room_id: '',
            price_person : '',
            person_number: '',
            persons_names: [],
            vacant_places: '',
            persons_going: '',
            options: {
              valid: false,
              add: false,
              edit: false,
              delete:false,
              editedIndex: '',
              deletedIndex : ''
            }
          },

          saleModel: {
            first_name: "",
            last_name: "",
            email: "",
            phone: "",
            offer_id: "",
            total_person_number: this.totalPersonNumber,
            payment_status: "notpaid",
            coupon_code: "",
            total_amount:0.00,
            options: {
              valid: false,
            }
          },
          roomForSaleModel: {
            offer_dates_location_room_id: '',
            price_person : '',
            person_number: '',
            persons_names: [],
            vacant_places: '',
            persons_going: '',
            options: {
              valid: false,
              add: false,
              edit: false,
              delete:false,
              editedIndex: 0,
              deletedIndex : 0
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
            ],

            personsGoing: [
              v => !!v || 'Numarul de persoane pe camera este obligatoriu',
              v => /^\d+$/.test(v) || 'Este necesara o valoare numerica',
              v => v <= this.roomForSaleModel.vacant_places || 'Nu puteti aloca mai multe persoana decate locuri sunt disponibile',
              v => v <= this.roomForSaleModel.person_number || 'Nu puteti specifica mai multe locuri disponibile decate persoane incap in camera',

            ],
            personsNames: [
              v => this.roomForSaleModel.persons_names.length === this.roomForSaleModel.persons_going || 'Introduceti numele celor ' + this.roomForSaleModel.persons_going + ' persoane ',
              v => this.roomForSaleModel.persons_names.length <= this.roomForSaleModel.persons_going || 'Nu puteti adauga mai multe nume decate locuri sunt disponibile'
            ]
          },
          offersHeaders: [
            { text: '', value: ''},
            { text: 'ID', value: 'id'},
            { text: 'Titlu oferta', value: 'title'},
            { text: 'Descriere', value: 'description' },
          ],
          datesTableHeaders: [
            { text: '', value: ''},
            {text:'ID', value:'id'},
            {text:'Data inceput', value:'start_date'},
            {text:'Data sfarsit', value:'end_date'}
          ],
          locationsTableHeaders: [
            { text: '', value: ''},
            {text:'ID', value:'id'},
            {text:'Nume', value:'name'},
          ],
          roomsTableHeaders: [
            { text: '', value: ''},
            {text:'ID', value:'id'},
            {text:'Tip de camera', value:'type'},
          ],
          individualRoomHeaders: [
            // { text: '', value: ''},
            {text: 'ID', value: 'id'},
            {text: 'Pret pe persoana', value:'price_person'},
            {text: 'Persoane pe camera', value:'person_number'},
            {text: 'Locuri disponibile', value:'vacant_places'}
          ],
          roomsInSaleHeaders: [
            {text: 'ID', value:'id'},
            {text: 'Data', value:'date'},
            {text: 'Locatie', value:'location'},
            {text: 'Tip de camera', value:'type'},
            {text: 'Numar de persoane', value:'persons_going'},
            {text: 'Nume persoane', value:'persons_names'},
            {text: 'Pret pe persoana', value:'price_person'},
            {text: 'Pret total', value:'total_price'},
            {text: '', value:''},

          ]
        }
      },

      methods: {

        async createSale(){

            this.busy = true;
            try {

              const { data }  = await axios.post('/api/sales/add', {
                saleFields : this.saleModel,
                allocatedRooms: this.allocatedRooms
              });

              this.clearAllData();
              this.$emit('update:dialog',false);
              this.$emit('update:reindex', true);

              this.$store.dispatch('responseMessage', {
                type: 'success',
                text: 'Vanzare adaugata'
              });



              // this.$emit('update:reindex', true);
            } catch (e) {
              console.log(e);
              for(let error of e.response.data.errors) {
                this.$store.dispatch('responseMessage', {
                  type: 'error',
                  text: error
                })
              }
            }
          this.busy = false;
        },

        indexTable(){ //query locations using the search
          this.searchOffers(this.search)
        },

        //get all dates belonging to this offer + locations.
        async fetchDates(offerId){
          this.busyDatesTable = true;
          try {
            const { data } = await axios.get('/api/offers/'+ offerId + '/dates')
            console.log(data);
            this.dates = data.dates;
          } catch(e) {
            console.log(e);
          }
        },

        selectOffer(){
          this.$store.dispatch('getOffers');
          this.offerModel.options.selectingOffer = true;
        },

        changeOffer(){
          this.$store.dispatch('getOffers');
          this.offerModel.options.changingOffer = true;
        },

        closeOfferDialog(){
          this.clearOfferModel();
        },

        datesStep(){
          this.fetchDates(this.selectedOffer.id);
          this.currentStep = 1;
          this.selectedDate = {};
        },

        locationsStep(){
          this.currentStep = 2;
          this.selectedRoom = {};
        },

        roomsStep(){
          this.currentStep = 3;
        },

        individualRoomsStep(){
          this.currentStep = 4;
          this.selectedIndividualRoom = {};
        },

        showStepper(){
          this.clearRoomForSaleModel();
          this.addingRoomsToSale = true;
          this.datesStep();
        },

        closeStepper(){
          this.clearRoomForSaleModel();
          this.currentStep = 0;
          this.addingRoomsToSale = false;
        },


        updateOffer(){

          if(this.offerModel.options.changingOffer){
            this.allocatedRooms = [];
          }
          this.selectedOffer = this.temporalOffer;
          this.$emit('update:selectedOffer', this.temporalOffer);
          this.saleModel.offer_id = this.selectedOffer.id;
          this.clearOfferModel();
        },


        addRoomToSaleDialog(room){
          this.clearRoomForSaleModel();
          this.selectedIndividualRoom = room;
          this.roomForSaleModel. offer_dates_location_room_id = room.id;
          this.roomForSaleModel.price_person = room.price_person;
          this.roomForSaleModel.person_number = room.person_number;
          this.roomForSaleModel.vacant_places = room.vacant_places;
          this.roomForSaleModel.options.add = true;
        },

        removeRoomDialog(room){
          let r = this.allocatedRooms.find(r => r.offer_dates_location_room_id === room.id);
          this.roomForSaleModel.options.deletedIndex = this.allocatedRooms.indexOf(r);
          this.roomForSaleModel.options.delete = true;
        },
        //
        removeRoomFromTableDialog(room){
          this.roomForSaleModel.options.deletedIndex = this.allocatedRooms.indexOf(room);
          this.roomForSaleModel.options.delete = true;
        },

        removeRoom(){
          let index = this.roomForSaleModel.options.deletedIndex;

          let allocatedRoom = this.allocatedRooms[index];

          // let indivRoom = this.selectedRoom.individualRooms.find(r => r.id === allocatedRoom.offer_dates_location_room_id);
          let indivRoom = this.findIndivRoom(allocatedRoom.offer_dates_location_room_id);
          console.log(indivRoom);
          indivRoom.vacant_places += allocatedRoom.persons_going;

          this.allocatedRooms.splice(index,1);
          this.clearRoomForSaleModel();

        },

        findIndivRoom(id){
          for(let date of this.dates){
            for(let location of date.locations){
              for(let room of location.rooms){
                for(let indRoom of room.individualRooms){
                  if(indRoom.id === id){
                    return indRoom;
                  }
                }
              }
            }
          }
        },

        // the room objects in the stepper (num 5)  table are not the same as the ones in selectedRoomForSale use the room's id to
        // find a room in allocatedRooms that matches this room's id
        editRoomFromStepper(room){
          let r = this.allocatedRooms.find(r => r.offer_dates_location_room_id === room.id);
          this.editRoom(r);
        },

        editRoom(room){
          this.clearRoomForSaleModel();
          //sale room stored in allocatedRooms
          this.roomForSaleModel.offer_dates_location_room_id = room.id;
          this.roomForSaleModel.persons_going = room.persons_going;
          this.roomForSaleModel.persons_names = JSON.parse(JSON.stringify(room.persons_names));
          //normal room from the dates array., used to retrieve aditional data
          this.roomForSaleModel.person_number = room.person_number;
          this.roomForSaleModel.vacant_places = room.vacant_places;
          this.roomForSaleModel.options.edit = true;
          this.roomForSaleModel.options.editedIndex = this.allocatedRooms.indexOf(room); //find room and save the index

          console.log(this.allocatedRooms.indexOf(room));

        },

        addRoomToSale(){
            let room = {}
            room.offer_dates_location_room_id = this.roomForSaleModel.offer_dates_location_room_id;
            room.persons_going = this.roomForSaleModel.persons_going;
            room.type = this.selectedRoom.type;
            room.price_person = this.roomForSaleModel.price_person;
            room.vacant_places = this.roomForSaleModel.vacant_places;
            room.person_number = this.roomForSaleModel.person_number;
            room.persons_names = this.roomForSaleModel.persons_names;

            //respective date and location, assigning only the necessary data for display.
            room.date = {};
            room.date.id = this.selectedDate.id;
            room.date.start_date = this.selectedDate.start_date;
            room.date.end_date = this.selectedDate.end_date;

            room.location = {};
            room.location.id = this.selectedLocation.id;
            room.location.name = this.selectedLocation.name;
            this.allocatedRooms.push(room);

            let indivRoom = this.selectedRoom.individualRooms.find(r => r.id === room.offer_dates_location_room_id);
            indivRoom.vacant_places -= room.persons_going;

            this.selectedIndividualRoom = {};


            this.clearRoomForSaleModel();
        },



        updateRoomFromSale(){

            let room = this.allocatedRooms[this.roomForSaleModel.options.editedIndex];

            let oldPersonsGoing = room.persons_going;

            room.persons_going = this.roomForSaleModel.persons_going;
            room.persons_names = this.roomForSaleModel.persons_names;

            let places = room.persons_going;

            let indivRoom = this.selectedRoom.individualRooms.find(r => r.id === room.offer_dates_location_room_id);


            if(room.persons_going > oldPersonsGoing){
               places = room.persons_going - oldPersonsGoing;
               indivRoom.vacant_places -= places;
            }


            if(room.persons_going < oldPersonsGoing){
              places = oldPersonsGoing - room.persons_going;
              indivRoom.vacant_places += places;
            }


            this.clearRoomForSaleModel();
        },

        //remove person from roomForSaleModel.persons_names
        removePerson(person){
          this.roomForSaleModel.persons_names.splice(this.roomForSaleModel.persons_names.indexOf(person), 1)
          this.roomForSaleModel.persons_names = JSON.parse(JSON.stringify([...this.roomForSaleModel.persons_names]));
        },
        
        //FORMAT, HELPERS AND OTHER METHODS.


        dateConcat(date){
          let start =  this.friendlyDateFormat(new Date(date.start_date));
          let end = this.friendlyDateFormat(new Date(date.end_date));
          return start + ' - ' + end
        },

        //return formated date for display
        friendlyDateFormat(date) {
          let dateObj = new Date(date);
          return moment(dateObj).format('DD-MM-YYYY');
        },

        totalPrice(price,numPersons) {
            return price * numPersons;
        },

        roomAlreadyAdded(roomId){
          return this.allocatedRooms.find(r => r.offer_dates_location_room_id === roomId );
        },
        //
        findRoom(roomId){
          return this.allocatedRooms.find(r => r.offer_dates_location_room_id = roomId);
        },

        clearOfferModel(){
          this.offerModel = JSON.parse(JSON.stringify(this.offerModelDefault));
        },
        clearSaleModel(){
          this.$refs.saleFields.reset();
          this.saleModel = JSON.parse(JSON.stringify(this.saleModelDefault));
        },

        clearRoomForSaleModel(){
          this.$refs.roomSaleFields.reset();
          this.roomForSaleModel = this.roomForSaleModelDefault;
        },

        clearAllData(){

          this.dates = [];
          this.selectedDate = [];
          this.allocatedRooms = [];
          this.selectedOffer = {};
          this.selectedRoom = {};
          this.temporalOffer = {};
          this.currentStep = 0;
          this.addingRoomsToSale = false;

          this.clearOfferModel();
          this.clearSaleModel();
          this.clearRoomForSaleModel();

        },


         closeCreateSale(){

          this.clearAllData();
          this.$emit('update:dialog',false);

         },

        ...mapActions({
          searchOffers: 'searchOffers'
        })
      },

      computed: {

        totalPersonNumber(){
          // this.saleModel.total_person_number = this.getTotalPersonNumber();
          let total = 0;
          for(let room of this.allocatedRooms){
            total += room.persons_going;
          }
          return total;
        },
        roomDialogTitle(){
          return this.roomForSaleModel.options.edit ? 'Editeaza camera' : 'Adauga camera la vanzare';
        },
        offerDialogTitle(){
          return this.selectingOffer ? 'Selectioneaza o oferta pentru vanzare' : 'Schimba o oferta pentru vanzare';
        },
        ...mapGetters({
          offers: 'GET_OFFERS'
        })
      },

      props: ['dialog', 'initialOffers']

    }

</script>