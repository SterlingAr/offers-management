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
                                        <v-stepper-step step="4" :complete="currentStep > 4"> Alege tip de camera</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="5">Camere/locuri pentru vanzare</v-stepper-step>
                                    </v-stepper-header>
                                    <v-stepper-items>
                                        <!-- Offers -->
                                        <v-stepper-content step="1">
                                            <v-flex md12 xs12 lg12 >
                                                <!--<v-card light>-->
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
                                                            no-data-text="Nu exista nici o oferta disponibila"

                                                    >
                                                        <template slot="items" slot-scope="props">
                                                            <td  @click="selectedOffer = props.item">
                                                                <v-radio-group
                                                                        v-model="selectedOffer"
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
                                            <v-btn :disabled="Object.keys(this.selectedOffer).length === 0" class="left" color="green" large flat @click="datesStep">
                                                Inainte
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                        </v-stepper-content>
                                        <!-- Dates -->
                                        <v-stepper-content step="2" transition="fade-transition">
                                            <v-flex md12 xs12>
                                                <p class="headline">Date pentru oferta {{selectedOffer.title}}</p>
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
                                            <v-btn  class="left" color="red" large flat  light right @click="offersStep">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedDate).length === 0"  class="left" color="green" large flat  @click="locationsStep">
                                                Inainte
                                            </v-btn>
                                        </v-stepper-content>
                                        <!-- Locations -->
                                        <v-stepper-content step="3">
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
                                        <v-stepper-content step="4">
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
                                                                        v-model="selectedRoom"
                                                                        name="rowSelector">
                                                                    <v-radio :value="props.item"/>
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
                                        <v-stepper-content step="5">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Camere individuale pentru tipul de camera {{selectedRoom.type}}</p>
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
                                                                <v-btn icon class="mx-0"  @click="addRoomToSaleDialog(props.item)" v-if="roomAlreadyAdded(props.item.id)">
                                                                    <v-icon color="teal">add</v-icon>
                                                                </v-btn>
                                                                <v-btn icon class="mx-0" @click="editRoomFromSaleDialog(props.item)"v-if="!roomAlreadyAdded(props.item.id)">
                                                                    <v-icon color="teal">edit</v-icon>
                                                                </v-btn>
                                                            </td>
                                                        </template>
                                                    </v-data-table>
                                                </v-card-text>
                                            </v-flex>
                                            <v-btn  class="left" color="red" large flat  light @click="roomsStep">
                                                Inapoi
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn class="left" color="green" large flat  light @click="addRoomToSale">
                                                Terminare
                                            </v-btn>
                                        </v-stepper-content>
                                    </v-stepper-items>
                                </v-stepper>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
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
        this.$store.dispatch('getOffers')
      },

      data(){
        return{

          addRoomDialog: false,
          editRoomDialog: false,

          //at the end, should choose between object or just id.
          selectedOffer: {},
          selectedDate: {},
          selectedLocation: {},
          selectedRoom: {},

          selectedRoomsForSale: [],

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
            id: '',
            price_person : '',
            person_number: '',
            vacant_places: '',
            persons_going: '',
            options: {
              valid: false,
              add: false,
              edit: false,
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
          roomForSaleModel: {
            id: '',
            price_person : '',
            person_number: '',
            vacant_places: '',
            persons_going: '',
            options: {
              valid: false,
              add: false,
              edit: false,
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
              v => v <= this.roomForSaleModel.person_number || 'Nu puteti specifica mai multe locuri disponibile decate persoane incap in camera'

            ],
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
        }
      },

      methods: {

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



        offersStep(){
          this.currentStep = 1;
        },

        datesStep(){
          this.fetchDates(this.selectedOffer.id);
          this.currentStep = 2;
        },

        locationsStep(){
          this.currentStep = 3;
        },

        roomsStep(){
          this.currentStep = 4;
        },

        individualRoomsStep(){
          this.currentStep = 5;
        },

        addRoomToSaleDialog(room){
          this.clearRoomForSaleModel();
          this.roomForSaleModel.id = room.id;
          this.roomForSaleModel.price_person = room.price_person;
          this.roomForSaleModel.person_number = room.person_number;
          this.roomForSaleModel.vacant_places = room.vacant_places;

          this.roomForSaleModel.options.add = true;
        },

        editRoomFromSaleDialog(room){
          this.clearRoomForSaleModel();

          //sale room stored in selectedRoomsForSale
          let roomInSale = this.selectedRoomsForSale.find(r => r.id = room.id);
          console.log(JSON.parse(JSON.stringify(roomInSale)));

          this.roomForSaleModel.id = roomInSale.id;
          this.roomForSaleModel.persons_going = roomInSale.persons_going;


          //normal room from the dates array., used to retrieve aditional data
          this.roomForSaleModel.person_number = room.person_number;
          this.roomForSaleModel.vacant_places = room.vacant_places;
          this.roomForSaleModel.options.edit = true;

          this.roomForSaleModel.options.editedIndex = this.selectedRoomsForSale.indexOf(roomInSale); //find room and save the index
        },

        addRoomToSale(){
            let room = {}
            room.id = this.roomForSaleModel.id;
            room.persons_going = this.roomForSaleModel.persons_going;
            this.selectedRoomsForSale.push(room);
            this.clearRoomForSaleModel();
        },

        updateRoomFromSale(){
            let room = this.selectedRoomsForSale[this.roomForSaleModel.options.editedIndex];
            room.persons_going = this.roomForSaleModel.persons_going;
            this.clearRoomForSaleModel();
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

        roomAlreadyAdded(roomId){
          return !this.selectedRoomsForSale.find(r => r.id === roomId );
        },
        //
        findRoom(roomId){
          return this.selectedRoomsForSale.find(r => r.id = roomId);
        },

        clearRoomForSaleModel(){
          this.$refs.roomSaleFields.reset();
          this.roomForSaleModel = JSON.parse(JSON.stringify(this.roomForSaleModelDefault));
        },

        closeCreateSale(){
          this.$emit('update:dialog',false);
        },

        ...mapActions({
          searchOffers: 'searchOffers'
        })
      },

      computed: {
        roomDialogTitle(){
          return this.roomForSaleModel.options.edit ? 'Editeaza camera' : 'Adauga camera la vanzare';
        },
        ...mapGetters({
          offers: 'GET_OFFERS'
        })
      },

      props: ['dialog', 'initialOffers']

    }

</script>