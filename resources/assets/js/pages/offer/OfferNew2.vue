<template>
    <div class="container">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <!--<progress-bar :show="busy"></progress-bar>-->
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Noua oferta</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createOffer">Creaza</v-btn>
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
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
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
                                                            <span class="right" slot="badge">{{props.item.locations.length}}</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="dateModel.edit=true">
                                                        <v-icon color="teal">edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0">
                                                        <v-icon color="pink">delete</v-icon>
                                                    </v-btn>
                                                </td>
                                            </template>
                                        </v-data-table>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <!-- Locations -->
                            <v-flex md6 xs12 v-if="editingLocations">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Listare locati pentru data {{ dateFormat(this.currentDate) }}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="editingLocations = false">
                                            <v-icon>keyboard_backspace</v-icon>
                                        </v-btn>
                                        <v-btn icon dark right @click="addLocation">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                        <v-data-table
                                                :headers="datesTableHeaders"
                                                :items="currentDate.locations"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.name }}</td>
                                                <td class="text-xs-right">
                                                    <v-btn color="indigo" dark>
                                                        <v-badge color="blue" rigth>
                                                            Camere
                                                            <span class="right" slot="badge" v-if="props.item.rooms.length !== undefined && props.item.rooms.length >= 0">
                                                                {{props.item.rooms.length}}
                                                            </span>
                                                            <span class="right" slot="badge" v-else>0</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <!--<v-btn icon class="mx-0" @click="dateModel.edit=true">-->
                                                        <!--<v-icon color="teal">edit</v-icon>-->
                                                    <!--</v-btn>-->
                                                    <v-btn icon class="mx-0" @click="deleteLocation(props.item)">
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
        <v-dialog v-model="addingDate" max-width="500px">
            <v-card>
                <v-card-title>
                    <span>Adauga o data noua</span>
                    <span v-if="dateModel.options.edit">Actualizeaza data</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="dateModel.options.valid">
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
                                        :rules="validationRules.commonDateRules"
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
                                            :rules="validationRules.commonDateRules"
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
                    <v-btn color="primary" flat @click.stop="addingDate = false">Close</v-btn>
                    <v-btn  :disabled="!dateModel.options.valid" color="blue darken-1" flat @click.native="addDate">Salveaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Dialog for adding location -->
        <v-dialog v-model="addingLocation" max-width="500px">
            <v-card>
                <v-card-title>
                    <span>Adauga o locatie pentru data {{dateFormat(currentDate)}}</span>
                    <span v-if="locationModel.options.edit">Actualizeaza locatia</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="locationModel.options.valid">
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
                    <v-btn color="primary" flat @click.stop="addingLocation = false">Close</v-btn>
                    <v-btn  :disabled="!locationModel.options.valid" color="blue darken-1" flat @click.native="assignLocation">Salveaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Confirm delete location action   -->
        <v-dialog v-model="deletingLocation" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere locatie?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="deletingLocation = false">Inchide</v-btn>
                    <v-btn flat large color="error" @click="removeLocationFromDate">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>


<script>
  import {mapGetters} from 'vuex'

  export default {


    data() {
      return {
        busy: false,
        busyDateTable: false,
        addingDate:false,
        editingLocations:false,
        addingLocation: false,
        deletingLocation: false,
        confirmDelete: false,

        dateModelDefault: {
          startDate: null,
          endDate:null,
          options: {
            edit:  false,
            valid: false,
          }
        },

        locationModelDefault: {
          id: '',
          title: '',
          options: {
            edit: false,
            valid: false
          }
        },

        dateModel: {
          startDate: null,
          endDate:null,
          options: {
            edit:  false,
            valid: false,
          }
        },

        locationModel: {
          id: '',
          title: '',
          options: {
            edit: false,
            valid: false
          }
        },
        selectedLocationID: '',

        startDateMenu: false,
        endDateMenu:null,

        currentDate: {}, //current date being manipulated.
        currentLocation: {},
        validationRules: {
          commonDateRules: [
            v => !!v || 'Data este obligatorie',
          ],
          endDateRules: [
            v => !!v || 'Data este obligatorie',
          ],
          locationRules: [
            v => !!v || 'Locatia este obligatorie'
          ]
        },

        datesTableHeaders: [
          {text:'ID', value:'id'},
          {text:'Data inceput', value:'start_date'},
          {text:'Data sfarsit', value:'end_date'}
        ],

        locationsTableHeaders: [
          {text:'ID', value:'id'},
          {text:'Nume', value:'start_date'},
        ],

        falseId: 0, //For new offers only.
        dates: []
      }
    },
    methods: {

      createOffer()  {

      },

      //use current dateModel to create a new date object for this offer
      addDate(){

        let date = {};
        date.id = this.falseId++;
        date.start_date = this.dateModel.startDate;
        date.end_date = this.dateModel.endDate
        date.locations = [];
        this.dates.push(date);
        this.dateModel = JSON.parse(JSON.stringify(this.dateModelDefault));
        this.addingDate = false;

      },

      //edit the locations for the given date
      editLocations(date){
        this.currentDate = {}; //empty in case of rogue values.
        this.currentDate = date;
        this.editingLocations = true;
      },

      addLocation(){
        this.locationModel = JSON.parse(JSON.stringify(this.locationModelDefault));
        this.selectedLocationID = '';
        this.addingLocation = true;
      },

      //add the new location to the date being modified.
      assignLocation(){
        let exists = this.currentDate.locations.find(l=> l.id === this.selectedLocationID);
        console.log(exists);

        if(!exists){
          this.currentDate.locations.push(this.selectedLocation);
          this.addingLocation = false;
          return;
        }
        alert('Exists!!');
        //else show kewl error dialog
      },


      deleteLocation(location){
        
        this.deletingLocation = true;
        this.currentLocation = location;
      },

      //given the index of the location, remove it from the currentDate
      removeLocationFromDate(){

        let index = this.currentDate.locations.indexOf(location);
        this.currentDate.locations.splice(index,1);
        this.deletingLocation = false;

      },

      //empty dateModel values and open dialog.
      addDateDialog(){
        this.dateModel = JSON.parse(JSON.stringify(this.dateModelDefault));
        this.addingDate = true;
      },


      //format, helpers, other methods
      dateFormat(date){
        return date.start_date + ' - ' + date.end_date
      }

    },

    computed: {
      ...mapGetters({
        locations : 'GET_LOCATIONS'
      }),

      selectedLocation(){
        return this.locations.find(l => l.id === this.selectedLocationID);
      }
    },
    props: ['dialog']

  }
</script>