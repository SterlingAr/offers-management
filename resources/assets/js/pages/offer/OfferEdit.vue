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
                    <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Editeaza oferta</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="updateOffer">Actualizeaza</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>
                    </v-menu>
                </v-toolbar>
                <v-card-text>
                    <v-container grid-list-md text-xs-center>
                        <v-layout row wrap>
                            <v-flex md6 xs12 >
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>perm_identity</v-icon>
                                        <v-toolbar-title> Detalii oferta</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                    </v-toolbar>
                                    <v-card-text>
                                        <v-form
                                                v-model="offerModel.options.valid"
                                                lazy-validation
                                                ref="offerFields"
                                        >
                                            <v-text-field
                                                    label="Titlu oferta"
                                                    v-model="offerModel.title"
                                                    :rules="validationRules.titleRules"
                                                    required
                                            ></v-text-field>
                                            <v-text-field
                                                    v-model="offerModel.description"
                                                    label="Descriere oferta"
                                                    :rules="validationRules.descriptionRules"
                                                    :textarea=true
                                                    value="aaaaaaaaaaaaa"
                                                    required
                                            ></v-text-field>

                                        </v-form>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <!-- Dates -->
                            <v-flex md6 xs12 v-if="!this.editingLocations">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Listare date</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="addDateDialog">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <progress-bar :show="busyDatesTable"></progress-bar>
                                        <v-data-table
                                                :headers="datesTableHeaders"
                                                :items="dates"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.start_date }}</td>
                                                <td class="text-xs-left">{{ props.item.end_date}}</td>
                                                <td class="text-xs-right">
                                                    <v-btn color="indigo" dark @click="editLocations(props.item)">
                                                        <v-badge color="blue" rigth>
                                                            Locatii
                                                            <span class="right" slot="badge" v-if="props.item.locations.length !== undefined && props.item.locations.length >= 0">
                                                                {{props.item.locations.length}}
                                                            </span>
                                                            <span class="right" slot="badge" v-else>0</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="editDateDialog(props.item)">
                                                        <v-icon color="teal">edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="deleteDateDialog(props.item)">
                                                        <v-icon color="pink">delete</v-icon>
                                                    </v-btn>
                                                </td>
                                            </template>
                                        </v-data-table>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <!-- Locations -->
                            <v-flex md6 xs12 v-if="!editingRooms && editingLocations">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Listare locati pentru data {{ dateFormat(this.currentDate) }}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="editingLocations = false">
                                            <v-icon>keyboard_backspace</v-icon>
                                        </v-btn>
                                        <v-btn icon dark right @click="addLocationDialog">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                        <v-data-table
                                                :headers="locationsTableHeaders"
                                                :items="currentDate.locations"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.name }}</td>
                                                <td class="text-xs-right">
                                                    <v-btn color="indigo" dark @click="editRooms(props.item)">
                                                        <v-badge color="blue" rigth>
                                                            Camere
                                                            <span class="right" slot="badge" v-if="props.item.rooms.length !== undefined && props.item.rooms.length >= 0">
                                                                {{props.item.rooms.length}}
                                                            </span>
                                                            <span class="right" slot="badge" v-else>0</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="deleteLocationDialog(props.item)">
                                                        <v-icon color="pink">delete</v-icon>
                                                    </v-btn>
                                                </td>
                                            </template>
                                        </v-data-table>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- Dialog for adding a date -->
        <v-dialog max-width="500px"
                  v-model="dateModel.options.add || dateModel.options.edit"
                  persistent

        >
            <v-card>
                <v-card-title>
                    <span v-if="dateModel.options.add">Adauga o data noua</span>
                    <span v-else>Actualizeaza data</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="dateModel.options.valid"
                             ref="dateFields"
                    >
                        <v-flex xs12>
                            <v-menu
                                    ref="startDateMenu"
                                    lazy
                                    :close-on-content-click="false"
                                    v-model="startDateMenu"
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                    :nudge-right="40"
                                    min-width="290px"
                                    :return-value.sync="dateModel.startDate"
                            >
                                <v-text-field
                                        slot="activator"
                                        label="Data inceput"
                                        v-model="dateModel.startDate"
                                        prepend-icon="event"
                                        :rules="validationRules.startDateRules"
                                        readonly
                                        required
                                ></v-text-field>
                                <v-date-picker v-model="dateModel.startDate" @input="$refs.startDateMenu.save(dateModel.startDate)"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12>
                            <v-menu
                                    ref="endDateMenu"
                                    lazy
                                    :close-on-content-click="false"
                                    v-model="endDateMenu"
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                    :nudge-right="40"
                                    min-width="290px"
                                    :return-value.sync="dateModel.endDate"
                            >
                                <v-text-field
                                        slot="activator"
                                        label="Data sfarsit"
                                        v-model="dateModel.endDate"
                                        prepend-icon="event"
                                        :rules="validationRules.endDateRules"
                                        readonly
                                        required
                                ></v-text-field>
                                <v-date-picker v-model="dateModel.endDate" @input="$refs.endDateMenu.save(dateModel.endDate)"></v-date-picker>
                            </v-menu>
                        </v-flex>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="clearDateModelAndClose">Close</v-btn>
                    <v-btn  :disabled="!dateModel.options.valid" color="blue darken-1" flat @click.native="addDate" v-if="dateModel.options.add">Salveaza</v-btn>
                    <v-btn  :disabled="!dateModel.options.valid" color="blue darken-1" flat @click.native="updateDate" v-else>Actualizeaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Dialog for adding location to currentDate -->
        <v-dialog
                max-width="500px"
                v-model="locationModel.options.add"
                persistent
        >
            <v-card>
                <v-card-title>
                    <span class="headline">Adauga o locatie pentru data {{dateFormat(currentDate)}}</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="locationModel.options.valid"
                             ref="locationFields"
                             lazy-validation
                    >
                        <v-flex xs12>
                            <v-select
                                    label="Select"
                                    :items="locations"
                                    v-model="selectedLocationID"
                                    :rules="validationRules.locationRules"
                                    item-text="name"
                                    item-value="id"
                            ></v-select>
                        </v-flex>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="clearLocationModelAndClose">Close</v-btn>
                    <v-btn  :disabled="!locationModel.options.valid" color="blue darken-1" flat @click.native="addLocation">Salveaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- CONFIRMATION DIALOGS -->
        <!-- Confirm delete date dialog -->
        <v-dialog v-model="dateModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere data?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearDateModelAndClose">Inchide</v-btn>
                    <v-btn flat large color="error" @click="deleteDate">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Confirm delete location dialog  -->
        <v-dialog v-model="locationModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere locatie?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearLocationModelAndClose">Inchide</v-btn>
                    <v-btn flat large color="error" @click="removeLocation">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
  import axios from 'axios';
  import {mapGetters} from 'vuex'
  import Vue from 'vue'

  export default {

      mounted(){
        // console.log(this.editedOffer);
        // this.fetchDates();
      },

      data() {
        return {
          busy:false,
          busyDatesTable:false,
          editingLocations:false,
          editingRooms:false,

          startDateMenu: false,
          endDateMenu:null,

          offerModelDefault: {
            title: '',
            description: '',
            options: {
              valid: false
            }
          },
          dateModelDefault: {
            startDate: null,
            endDate:null,
            options: {
              add:false,
              edit:  false,
              delete:false,
              valid: false,
              editedIndex:0,
              deletedIndex:0,
            }
          },
          locationModelDefault: {
            id: '',
            title: '',
            options: {
              add:false,
              edit: false,
              delete:false,
              valid: false,
              editedIndex:0,
              deletedIndex:0,
            }
          },
          // offerModel: {
          //   title: '',
          //   description: '',
          //   options: {
          //     valid: false
          //   }
          // },
          locationModel: {
            id: '',
            title: '',
            options: {
              add:false,
              edit: false,
              delete:false,
              valid: false,
              editedIndex:0,
              deletedIndex:0,
            }
          },
          dateModel: {
            startDate: null,
            endDate:null,
            options: {
              add:false,
              edit: false,
              delete:false,
              valid: false,
              editedIndex:0,
              deletedIndex:0,
            }
          },
          validationRules: {
            titleRules: [
              v => !!v || 'Titlul ofertei este obligatoriu',
              v => v.length <= 25 || 'Titlul nu poate contine peste 25 de caractere'
            ],
            descriptionRules: [
              v => !!v || 'Descrierea ofertei este obligatorie',
              v => v.length <= 2500 || 'Descrierea nu poate contine peste 2500 de caractere'
            ],
            startDateRules: [
              v => !!v || 'Data este obligatorie',
            ],
            endDateRules: [
              v => !!v || 'Data este obligatorie',
            ],
            locationRules: [
              v => !!v || 'Locatia este obligatorie',
              v => !this.currentDate.locations.find(l => l.id === v) || 'Locatia a fost deja adaugata'
            ],
            roomRules: [
              v => !!v || 'Tipul de camera este obligatoriu',
              v => !this.currentLocation.rooms.find(r => r.id === v) || 'Camera a fost deja adaugata'
            ],
            pricePerson: [
              v => !!v || 'Pretul pe persoana este obligatoriu',
              v => !isNaN(v) || 'Este necesara o valoare numerica'

            ],
            personNumber: [
              v => !!v || 'Numarul de persoane pe camera este obligatoriu',
              v => !isNaN(v) || 'Este necesara o valoare numerica'
            ]
          },
          datesTableHeaders: [
            {text:'ID', value:'id'},
            {text:'Data inceput', value:'start_date'},
            {text:'Data sfarsit', value:'end_date'}
          ],
          locationsTableHeaders: [
            {text:'ID', value:'id'},
            {text:'Nume', value:'name'},
          ],
          removals: {
            dates : [],
            locations: [],
            rooms: [],
            individualRooms: []
          },
          falseId: 1000,
          selectedLocationID: 0,
          currentDate: {
            locations: []
          },
        }
      },

      methods: {

        async updateOffer(){
          this.busy = true;
          try {
            const {data}  = await  axios.post('/api/offers/update', {
              editedOffer: this.offerModel,
              // editedDates: this.dates,
            });
            console.log(data);
            this.busy=false;
            console.log(data);
            this.$store.dispatch('responseMessage', {
              type: 'success',
              text: 'Oferta actualizata'
            });
            this.closeUpdateOffer();
            console.log(data);

          } catch (e) {
            for(let error of e.response.data.errors) {
              this.$store.dispatch('responseMessage', {
                type: 'error',
                text: error
              })
            }
          }
        },


        //TABLES
        //for the given date, set it as currentDate and open the locations in a table
        editLocations(date){
          this.currentDate = {}; //empty in case of rogue values.
          this.currentDate = date;
          this.editingLocations = true;
        },

        //DATES CRUD
        //empty dateModel values and open dialog.
        addDateDialog(){
          this.dateModel = JSON.parse(JSON.stringify(this.dateModelDefault));
          this.dateModel.options.add = true;
        },
        //method used to receive the date object and to show the edit dialog
        editDateDialog(date){
          this.clearDateModelAndClose();
          this.dateModel.startDate = date.start_date;
          this.dateModel.endDate = date.end_date;
          this.dateModel.options.editedIndex = this.dates.indexOf(date);
          this.dateModel.options.edit = true;
        },
        //prepare date for delete and open confirmation dialog
        deleteDateDialog(date){
          this.clearDateModelAndClose();
          this.dateModel.options.deletedIndex = this.dates.indexOf(date);
          this.dateModel.options.delete = true;
        },

        //use current dateModel to create a new date object for this offer
        addDate(){
          let date = {};
          date.id = this.falseId++;
          date.isNew = true;
          date.start_date = this.dateModel.startDate;
          date.end_date = this.dateModel.endDate
          date.locations = [];
          this.dates.push(date);
          this.clearDateModelAndClose();
        },
        //update a date in the dates array
        updateDate(){
          let index = this.dateModel.options.editedIndex;
          this.dates[index].start_date = this.dateModel.startDate;
          this.dates[index].end_date = this.dateModel.endDate;
          this.clearDateModelAndClose();
        },
        deleteDate(){
          let index = this.dateModel.options.deletedIndex;
          this.removals.dates.push(this.dates[index]);
          this.dates.splice(index,1);
          this.clearDateModelAndClose();
        },

        //LOCATIONS CRUD
        addLocationDialog(){
          this.clearLocationModelAndClose();
          this.selectedLocationID = '';
          this.locationModel.options.add = true;
        },
        //prepare location for delete and open confirmation dialog.
        deleteLocationDialog(location){
          this.clearLocationModelAndClose();
          this.locationModel.options.deletedIndex = this.currentDate.locations.indexOf(location);
          this.locationModel.options.delete = true;
        },

        //add the new location to the date being modified.
        addLocation(){
          let location = JSON.parse(JSON.stringify(this.selectedLocation));
          this.currentDate.locations.push(location);//assign copy of the object, not the object in vuex store.
          location.rooms.forEach(room => this.generateIndividualRooms(room));
          this.locationModel.options.add = false;
          this.clearLocationModelAndClose();
        },
        //given the index of the location, remove it from the currentDate
        removeLocation(){
          let index = this.locationModel.options.deletedIndex;
          this.currentDate.locations.splice(index,1);
          this.clearLocationModelAndClose();
        },


        //FORMAT, HELPERS AND OTHER METHODS.
        dateFormat(date){
          return date.start_date + ' - ' + date.end_date
        },
        generateIndividualRooms(room){
          if(room.predefined_values.num_rooms > 0){

            Vue.set(room, 'individualRooms',[]);
            let numRooms = room.predefined_values.num_rooms;

            for(let i = numRooms; i !== 0 ; i--){
              let individualRoom = {};
              individualRoom.id = this.roomFalseId++;
              //data needed for persistence.
              individualRoom.price_person = room.predefined_values.price_person;
              individualRoom.person_number = room.predefined_values.person_number;
              room.individualRooms.push(individualRoom);
            }
          }
        },



        //set date model to default values, reset form fields and close any add,edit or delete dialogs.
        clearDateModelAndClose(){
          this.$refs.dateFields.reset();
          this.dateModel = JSON.parse(JSON.stringify(this.dateModelDefault));
        },
        //set location model to default values, reset form fields and close any add,edit or delete dialogs.
        clearLocationModelAndClose(){
          this.$refs.locationFields.reset();
          this.locationModel = JSON.parse(JSON.stringify(this.locationModelDefault));
        },

        clearAllData(){
          this.clearDateModelAndClose();
          // this.clearLocationModelAndClose();
          // this.clearRoomModelAndClose();
        },
        //deactivate any active tables except the default one, which is for listing dates
        closeTableViews(){
          // this.editingLocations = false;
          // this.editingRooms = false;
          // this.editingIndividualRooms = false;
        },
        async closeUpdateOffer(){
          await this.clearAllData();
          await this.closeTableViews();
          this.$emit('update:dialog', false);
          // this.$emit('update:dialog', false);
          this.$emit('update:reindex', true);
        }
      },

      computed: {
        ...mapGetters({
          locations : 'GET_LOCATIONS'
        }),

        selectedLocation(){
          return this.locations.find(l => l.id === this.selectedLocationID);
        },
      },
      props: ['reindex','dialog','offerModel', 'dates']

    }
</script>