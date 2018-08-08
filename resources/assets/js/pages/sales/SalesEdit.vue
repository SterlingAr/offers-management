<template>
    <div class="container">
        <v-dialog
                v-model="editDialog"
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
                    <v-toolbar-title>Edit sale</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="updateSale">Update sale</v-btn>
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
                                        <v-toolbar-title>Client details</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                    </v-toolbar>
                                    <v-card-text >
                                        <v-form
                                                v-model="saleModel.options.valid"
                                                ref="saleFields"
                                        >
                                            <v-text-field
                                                    label="Name"
                                                    v-model="saleModel.first_name"
                                                    :rules="validationRules.nameRules"
                                                    :counter="10"
                                                    required
                                            ></v-text-field>
                                            <v-text-field
                                                    label="Family name"
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
                                                    label="Phone"
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
                                                You haven't chosen any offer
                                            </span>
                                            <span v-else>
                                               Rooms for the offer: {{selectedOffer.title}}
                                            </span>
                                        </v-toolbar-title>
                                        <v-btn  color="blue" dark  @click="selectOffer" v-if="Object.keys(this.selectedOffer).length === 0">
                                            Choose offer.
                                        </v-btn>
                                        <v-btn color="red lighten-2" dark @click="changeOffer" v-else>
                                            Switch offer
                                        </v-btn>
                                        <v-spacer></v-spacer>
                                        <v-btn color="blue" right @click="showStepper" v-if="Object.keys(this.selectedOffer).length">
                                            Add room
                                        </v-btn>
                                    </v-toolbar>
                                </v-card>
                                <v-card-text>
                                    <v-data-table
                                            :headers="roomsInSaleHeaders"
                                            :items="allocatedRooms"
                                            class="elevation-1"
                                    >
                                        <template slot="items" slot-scope="props">
                                            <td class="text-xs-center">{{ props.item.id }}</td>
                                            <td class="text-xs-center">{{dateConcat(props.item.start_date, props.item.end_date)}}</td>
                                            <td class="text-xs-center">{{props.item.location_name}}</td>
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
                                <v-card-text>
                                    <span class="left headline">
                                        Total: {{ totalPriceSale() }}
                                    </span>
                                    <v-spacer></v-spacer>
                                    <v-flex md6 xs6>
                                        <v-text-field
                                                name="coupon"
                                                label="Cod cupon"
                                                v-model="couponCodeBuffer"
                                                @input="$emit('update:couponCode', couponCodeBuffer)"
                                        ></v-text-field>

                                        <v-btn color="blue right" @click="applyCoupon">
                                            Apply coupon
                                        </v-btn>
                                        <v-btn color="red right" @click="deleteCoupon" v-if="Object.keys(this.coupon).length > 0">
                                            Delete coupon
                                        </v-btn>
                                    </v-flex>
                                </v-card-text>
                            </v-flex>
                            <!-- STEPPER CONTAINER -->
                            <v-flex md6 xs12 v-if="addingRoomsToSale">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title>Select date, location and reserved rooms</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn class="left" color="white" large flat  light @click="closeStepper">
                                            Done
                                        </v-btn>
                                    </v-toolbar>
                                </v-card>
                                <v-stepper v-model="currentStep">
                                    <v-stepper-header>
                                        <v-stepper-step step="1" :complete="currentStep > 1">Choose date</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="2" :complete="currentStep > 2">Choose location</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="3" :complete="currentStep > 3">Select room type</v-stepper-step>
                                        <v-divider></v-divider>
                                        <v-stepper-step step="4">Rooms or places for sale</v-stepper-step>
                                    </v-stepper-header>
                                    <v-stepper-items>
                                        <!-- Dates -->
                                        <v-stepper-content step="1" transition="fade-transition">
                                            <v-flex md12 xs12>
                                                <p class="headline">Dates for the offer: {{ selectedOffer.title}}</p>
                                                <v-card-text>
                                                    <v-data-table
                                                            :headers="datesTableHeaders"
                                                            :items="dates"
                                                            class="elevation-1"
                                                            no-data-text="There are no dates for the selected offer"
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
                                                Back
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedDate).length === 0"  class="left" color="green" large flat  @click="locationsStep">
                                                Next
                                            </v-btn>
                                        </v-stepper-content>
                                        <!-- Locations -->
                                        <v-stepper-content step="2">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Locations for the date: {{dateConcat2(selectedDate)}}</p>
                                                <v-card-text>
                                                    <v-data-table
                                                            :headers="locationsTableHeaders"
                                                            :items="selectedDate.locations"
                                                            class="elevation-1"
                                                            no-data-text="There are no available locations for this date"
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
                                                Back
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedLocation).length === 0"  class="left" color="green" large flat  @click="roomsStep">
                                                Next
                                            </v-btn>
                                        </v-stepper-content>
                                        <!-- Rooms -->
                                        <v-stepper-content step="3">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Room types available for the location: {{selectedLocation.name}}</p>
                                                <v-card-text>
                                                    <v-data-table
                                                            :headers="roomsTableHeaders"
                                                            :items="selectedLocation.rooms"
                                                            class="elevation-1"
                                                            no-data-text="There is no room type available for this location"
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
                                                Back
                                            </v-btn>
                                            <v-spacer></v-spacer>
                                            <v-btn :disabled="Object.keys(this.selectedRoom).length === 0"  class="left" color="green" large flat  @click="individualRoomsStep">
                                                Next
                                            </v-btn>

                                        </v-stepper-content>
                                        <!-- individual rooms -->
                                        <v-stepper-content step="4">
                                            <v-flex md12 xs12 lg12>
                                                <p class="headline">Individual rooms of type {{selectedRoom.type}} for the location {{selectedLocation.name}} at the date: {{dateConcat(selectedDate)}}</p>
                                                <v-card-text>
                                                    <v-data-table
                                                            :headers="individualRoomHeaders"
                                                            :items="selectedRoom.individualRooms"
                                                            class="elevation-1"
                                                            no-data-text="There is no individual room of this type"
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
                                                Back
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
                                    label="Search offer"
                                    single-line
                                    hide-details
                                    @input="indexTable()"
                                    v-model.lazy="search"
                            ></v-text-field>
                            <v-alert class="left" :value="true" v-if="offerModel.options.changingOffer" type="error">
                                If the offer is changed, all previously selected rooms will be excluded from the sale.
                            </v-alert>
                            <v-data-table
                                    :headers="offersHeaders"
                                    :items="offers"
                                    no-data-text="There is no offer available."

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
                                    No results for:  "{{ search }}".
                                </v-alert>
                            </v-data-table>
                        </v-card-text>
                    </v-flex>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="closeOfferDialog">Close</v-btn>
                    <v-btn color="blue darken-1" @click="updateOffer" v-if="offerModel.options.selectingOffer">Select</v-btn>
                    <v-btn color="blue darken-1" @click="updateOffer" v-else>Update</v-btn>
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
                    <br>
                    <span class="headline">Vacant places: {{roomForSaleModel.vacant_places}}</span>
                </v-card-title>
                <v-card-text>
                    <v-form
                            v-model="roomForSaleModel.options.valid"
                            ref="roomSaleFields"
                            lazy-validation
                    >
                        <v-flex xs12>
                            <v-text-field
                                    label="Person number"
                                    v-model.number="roomForSaleModel.persons_going"
                                    :rules="validationRules.personsGoing"
                                    required
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-select
                                    v-model="roomForSaleModel.persons_names"
                                    label="Number of persons going"
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
                    <v-btn  :disabled="!roomForSaleModel.options.valid" color="blue darken-1" flat @click="addRoomToSale" v-if="roomForSaleModel.options.add">Save</v-btn>
                    <v-btn  :disabled="!roomForSaleModel.options.valid" color="blue darken-1" flat @click="updateRoomFromSale" v-else>Update</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Confirm remove room from sale dialog -->
        <v-dialog v-model="roomForSaleModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Delete room?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearRoomForSaleModel">Close</v-btn>
                    <v-btn flat large color="error" @click="removeRoom">Yes, delete it.</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>

  import axios from 'axios'
  import moment from 'moment'
  import {mapGetters} from 'vuex'

  export default {
    name: "SalesEdit",
    metaInfo () {
      return {title: "Editare vanzare"}
    },

    data () {
      return {

        addingRoomsToSale: false,
        addRoomDialog: false,
        editRoomDialog: false,

        temporalOffer: {},

        //at the end, should choose between object or just id.
        selectedDate: {},
        selectedLocation: {},
        selectedRoom: {},
        selectedIndividualRoom: {},
        deallocatedRooms: [],

        couponCodeBuffer: '',
        dates: [],
        search: '',
        busy: false,
        busyOffers: false,
        selectingDates: false,
        selectingLocations: false,
        selectingRooms: false,
        selectingIndividualRooms: false,
        currentStep: 0,

        offerModelDefault: {
          options: {
            selectingOffer: false,
            changingOffer: false,
          }
        },

        offerModel: {
          options: {
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
          total_amount: 0.00,
          options: {
            valid: false,
          }
        },

        roomForSaleModelDefault: {
          offer_dates_location_room_id: '',
          price_person: '',
          person_number: '',
          persons_names: [],
          vacant_places: '',
          persons_going: '',
          options: {
            valid: false,
            add: false,
            edit: false,
            delete: false,
            editedIndex: '',
            deletedIndex: ''
          }
        },

        roomForSaleModel: {
          offer_dates_location_room_id: '',
          price_person: '',
          person_number: '',
          persons_names: [],
          vacant_places: '',
          persons_going: '',
          options: {
            valid: false,
            add: false,
            edit: false,
            delete: false,
            editedIndex: 0,
            deletedIndex: 0
          }
        },

        validationRules: {
          nameRules: [
            v => !!v || 'Client name is required',
            v => v.length <= 10 || 'Name must be less than 10 characters'
          ],
          phoneRules: [
            v => !!v || 'Phone number is required',
          ],
          emailRules: [
            v => !!v || 'Email is required',
            v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'Email format must be valid.'
          ],

          personsGoing: [
            v => !!v || 'Number of persons per room is required',
            v => /^\d+$/.test(v) || 'A numeric value is required for this field',
            v => v <= this.roomForSaleModel.persons_going  || 'You cannot allocate more people than the remaining vacant places',
            v => v <= this.roomForSaleModel.person_number || 'You cannot allocate more people than the maximum ofpeople allowed in the room',
          ],

          personsNames: [
            v => this.roomForSaleModel.persons_names.length === this.roomForSaleModel.persons_going || 'Insert the name of the ' + this.roomForSaleModel.persons_going + ' persons going ',
            v => this.roomForSaleModel.persons_names.length <= this.roomForSaleModel.persons_going || 'The total number of names is not the same as the number of people going. '
          ]
        },
        offersHeaders: [
          {text: '', value: ''},
          {text: 'ID', value: 'id'},
          {text: 'Offer title', value: 'title'},
          {text: 'Description', value: 'description'},
        ],
        datesTableHeaders: [
          {text: '', value: ''},
          {text: 'ID', value: 'id'},
          {text: 'Start date', value: 'start_date'},
          {text: 'End date', value: 'end_date'}
        ],
        locationsTableHeaders: [
          {text: '', value: ''},
          {text: 'ID', value: 'id'},
          {text: 'Name', value: 'name'},
        ],
        roomsTableHeaders: [
          {text: '', value: ''},
          {text: 'ID', value: 'id'},
          {text: 'Room type', value: 'type'},
        ],
        individualRoomHeaders: [
          {text: 'ID', value: 'id'},
          {text: 'Price person', value: 'price_person'},
          {text: 'Persons per room', value: 'person_number'},
          {text: 'Vacant places', value: 'vacant_places'}
        ],
        roomsInSaleHeaders: [
          {text: 'ID', value: 'id'},
          {text: 'Date', value: 'date'},
          {text: 'Location', value: 'location'},
          {text: 'Room type', value: 'type'},
          {text: 'Person number', value: 'person_number'},
          {text: 'Person names ', value: 'persons_names'},
          {text: 'Price per person', value: 'price_person'},
          {text: 'Total price', value: 'total_price'},
          {text: '', value: ''},

        ]
      }
    },

    methods: {

      mounted: function () {
        this.couponCode = this.saleModel.coupon_code
        this.applyCoupon();
      },


      async updateSale(){
        this.busy = true;
        try {
          const {data} = await axios.post('/api/sales/update/'+ this.saleModel.id, {
            'allocatedRooms' : this.allocatedRooms,
            'saleFields': this.saleModel,
            'deallocatedRooms': this.deallocatedRooms
          });

          this.$store.dispatch('responseMessage', {
            type: 'success',
            text: 'Vanzare actualizata'
          });

          this.clearAllData();
          this.$emit('update:editDialog',false);
          this.$emit('update:allocatedRooms',[]);
          this.$emit('update:reindex', true);
          this.deleteCoupon();


        } catch(e){
          console.log(e);
        }
        this.busy = false;
      },

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

      applyCoupon: async function () {
        try {
          const {data} = await axios.get('/api/coupons/' + this.couponCode)

          this.$emit('update:coupon', data.coupon)
          this.$emit('update:couponReduction', data.coupon.reduction_value)
          let saleModelCopy = JSON.parse(JSON.stringify(this.saleModel))
          saleModelCopy.coupon_id = data.coupon.id
          this.$emit('update:saleModel', saleModelCopy)
          console.log(data)
        } catch (e) {
          console.log(e)
        }
      },


      deleteCoupon(){
        this.$emit('update:coupon',{});
        let saleModelCopy = JSON.parse(JSON.stringify(this.saleModel));
        saleModelCopy.coupon_id = null;
        this.$emit('update:saleModel', saleModelCopy);
        this.$emit('update:couponReduction',0);
        this.$emit('update:couponCode','');
        this.couponCodeBuffer = '';
      },


      totalPriceSale: function (){
        let total = 0;
        for(let room of this.allocatedRooms){
          total += room.persons_going * room.price_person - (room.persons_going * this.couponReduction);
        }
        return total;
      },


      selectOffer(){
        this.$store.dispatch('getOffers');
        this.offerModel.options.selectingOffer = true;
      },

      changeOffer(){
        this.$store.dispatch('getOffers');
        this.offerModel.options.changingOffer = true;
      },

      updateOffer(){

        if(this.offerModel.options.changingOffer){

          for(let room of this.allocatedRooms){
            if(room.isNew === undefined){
              this.deallocatedRooms.push(room);
            }
          }
          this.$emit('update:allocatedRooms', []);
        }
        this.$emit('update:selectedOffer', this.temporalOffer);
        let newSaleModel = JSON.parse(JSON.stringify(this.saleModel));
        newSaleModel.offer_id = this.temporalOffer.id;
        this.$emit('update:saleModel', newSaleModel);
        this.clearOfferModel();
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
      },

      removeRoomFromTableDialog(room){
        this.roomForSaleModel.options.deletedIndex = this.allocatedRooms.indexOf(room);
        this.roomForSaleModel.options.delete = true;
      },
       //from stepper
      removeRoomDialog(room){
        let r = this.allocatedRooms.find(r => r.offer_dates_location_room_id === room.id);
        this.roomForSaleModel.options.deletedIndex = this.allocatedRooms.indexOf(r);
        this.roomForSaleModel.options.delete = true;
      },

      removeRoom(){

        let index = this.roomForSaleModel.options.deletedIndex;
        let removableRoom = this.allocatedRooms[index];

        if(!removableRoom.isNew){
          this.deallocatedRooms.push(removableRoom);
        }

        if(this.currentStep > 0){
          let indivRoom = this.findIndivRoom(removableRoom.offer_dates_location_room_id);
          indivRoom.vacant_places += removableRoom.persons_going;
        }

        this.allocatedRooms.splice(index,1);
        this.clearRoomForSaleModel();
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

      // the room objects in the stepper (num 5)  table are not the same as the ones in selectedRoomForSale use the room's id to
      // find a room in allocatedRooms that matches this room's id
      editRoomFromStepper(room){
        let r = this.allocatedRooms.find(r => r.offer_dates_location_room_id === room.id);
        this.editRoom(r);
      },



      addRoomToSale(){
        let room = {}
        room.isNew = true;
        room.offer_dates_location_room_id = this.roomForSaleModel.offer_dates_location_room_id;
        room.persons_going = this.roomForSaleModel.persons_going;
        room.type = this.selectedRoom.type;
        room.price_person = this.roomForSaleModel.price_person;
        room.person_number = this.roomForSaleModel.person_number;
        room.persons_names = this.roomForSaleModel.persons_names;

        room.start_date = this.selectedDate.start_date;
        room.end_date = this.selectedDate.end_date;

        room.location_name = this.selectedLocation.name;
        this.allocatedRooms.push(room);

        let indivRoom = this.selectedRoom.individualRooms.find(r => r.id === room.offer_dates_location_room_id);
        indivRoom.vacant_places -= room.persons_going;

        this.selectedIndividualRoom = {};

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

      async updateRoomFromSale(){
        this.busy = true;
        try {

          if(!this.dates.length){
            await this.fetchDates(this.selectedOffer.id);
          }

          let room = this.allocatedRooms[this.roomForSaleModel.options.editedIndex];

          let oldPersonsGoing = room.persons_going;

          room.persons_going = this.roomForSaleModel.persons_going;
          room.persons_names = this.roomForSaleModel.persons_names;

          let places = room.persons_going;

          let indivRoom = this.findIndivRoom(room.offer_dates_location_room_id);


          if(room.persons_going > oldPersonsGoing){
            places = room.persons_going - oldPersonsGoing;
            indivRoom.vacant_places -= places;
            room.vacant_places = indivRoom.vacant_places;
          }

          if(room.persons_going < oldPersonsGoing){
            places = oldPersonsGoing - room.persons_going;
            indivRoom.vacant_places += places;
            room.vacant_places = indivRoom.vacant_places;
          }

          this.clearRoomForSaleModel();

        } catch(e){
        }
        this.busy = false;

      },

      //remove person from roomForSaleModel.persons_names
      removePerson(person){
        this.roomForSaleModel.persons_names.splice(this.roomForSaleModel.persons_names.indexOf(person), 1)
        this.roomForSaleModel.persons_names = JSON.parse(JSON.stringify([...this.roomForSaleModel.persons_names]));
      },

      //helpers
      totalPrice(price,numPersons) {
        return price * numPersons;
      },


      dateConcat2(date){
        let start =  this.friendlyDateFormat(new Date(date.start_date));
        let end = this.friendlyDateFormat(new Date(date.end_date));
        return start + ' - ' + end
      },

      dateConcat(start_date, end_date){
        let start =  this.friendlyDateFormat(new Date(start_date));
        let end = this.friendlyDateFormat(new Date(end_date));
        return start + ' - ' + end
      },
      //return formated date for display
      friendlyDateFormat(date) {
        let dateObj = new Date(date);
        return moment(dateObj).format('DD-MM-YYYY');
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
        this.temporalOffer = {};
      },
      clearSaleModel(){
        this.$refs.saleFields.reset();
      },

      clearRoomForSaleModel(){
        this.$refs.roomSaleFields.reset();
        this.roomForSaleModel = JSON.parse(JSON.stringify(this.roomForSaleModelDefault));
      },

      clearAllData(){

        this.dates = [];
        this.selectedDate = [];
        this.selectedRoom = {};
        this.temporalOffer = {};
        this.selectedLocation = {};
        this.deallocatedRooms = [];
        this.currentStep = 0;
        this.addingRoomsToSale = false;
        this.couponCodeBuffer = '';
      },

      closeCreateSale(){

        this.clearAllData();
        this.$emit('update:editDialog',false);

      },
    },
    computed: {

      totalPersonNumber(){
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

    props: ['editDialog','saleModel','allocatedRooms', 'selectedOffer','coupon','couponCode','couponReduction']
  }

</script>