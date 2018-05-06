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
                    <v-btn icon @click.native="closeCreateOffer" dark>
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
                                        <v-toolbar-title> Listare locati pentru data {{ dateConcat(this.currentDate) }}</v-toolbar-title>
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
                            <!-- Rooms -->
                            <v-flex md6 xs12 v-if="!editingIndividualRooms && editingRooms">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Listare camere pentru locatia {{currentLocation.name}}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="editingRooms = false">
                                            <v-icon>keyboard_backspace</v-icon>
                                        </v-btn>
                                        <v-btn icon dark right @click="addRoomDialog">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                        <v-data-table
                                                :headers="roomsTableHeaders"
                                                :items="currentLocation.rooms"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.type }}</td>
                                                <td class="text-xs-right">
                                                    <v-btn color="indigo" dark @click="editIndividualRooms(props.item)">
                                                        <v-badge color="blue" rigth>
                                                            Camere individuale
                                                            <span class="right" slot="badge" v-if="props.item.individualRooms !== undefined && props.item.individualRooms.length >= 0">
                                                                {{ props.item.individualRooms.length}}
                                                            </span>
                                                            <span class="right" slot="badge" v-else>0</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="deleteRoomDialog(props.item)">
                                                        <v-icon color="pink">delete</v-icon>
                                                    </v-btn>
                                                </td>
                                            </template>
                                        </v-data-table>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <!-- Individual rooms -->
                            <v-flex md6 xs12 v-if="editingIndividualRooms">
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Camere individuale de tip {{currentRoom.type}} pentru locatia {{currentLocation.name}}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="editingIndividualRooms = false">
                                            <v-icon>keyboard_backspace</v-icon>
                                        </v-btn>
                                        <v-btn icon dark right @click="addIndividualRoomDialog">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-spacer></v-spacer>
                                    <!--<span class="left">Numarul de camere predefinit in locatia {{currentLocation.name}} pentru tipul {{currentRoom.type}}:  {{ currentRoom.predefined_values.num_rooms }}</span>-->
                                    <v-card-text>
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                        <v-data-table
                                                :headers="individualRoomHeaders"
                                                :items="currentRoom.individualRooms"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.price_person }}</td>
                                                <td class="text-xs-left">{{ props.item.person_number }}</td>
                                                <td class="text-xs-left">{{ props.item.vacant_places }}</td>
                                                <td class="text-xs-right">
                                                    <v-btn icon class="mx-0" @click="editIndividualRoomDialog(props.item)">
                                                        <v-icon color="teal">edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="deleteIndividualRoomDialog(props.item)">
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
                                <v-date-picker
                                        v-model="dateModel.startDate"
                                        @input="$refs.startDateMenu.save(dateModel.startDate)"
                                >
                                </v-date-picker>
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
                    <span class="headline">Adauga o locatie pentru data {{dateConcat(currentDate)}}</span>
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
        <!-- Dialog for adding room to currentLocation -->
        <v-dialog
                max-width="500px"
                v-model="roomModel.options.add"
                persistent
        >
            <v-card>
                <v-card-title>
                    <span class="headline">Adauga un tip de camera pentru locatia  {{ currentLocation.name }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="roomModel.options.valid"
                             ref="roomFields"
                    >
                        <v-flex xs12>
                            <v-select
                                    label="Select"
                                    :items="fetchUnmodifiedLocation.rooms"
                                    v-model="selectedRoomTypeID"
                                    :rules="validationRules.roomRules"
                                    item-text="type"
                                    item-value="id"
                            ></v-select>
                        </v-flex>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="clearRoomModelAndClose">Close</v-btn>
                    <v-btn  :disabled="!roomModel.options.valid" color="blue darken-1" flat @click.native="addRoom">Salveaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Dialog for adding individual room to currentRoom -->
        <v-dialog
                max-width="500px"
                v-model="individualRoomModel.options.add || individualRoomModel.options.edit"
                persistent
        >
            <v-card>
                <v-card-title>
                    <span class="headline">Adauga o camera individuala de tipul {{currentRoom.type}} pentru locatia {{currentLocation.name}}</span>
                </v-card-title>
                <v-card-text>
                    <v-form
                            v-model="individualRoomModel.options.valid"
                            ref="individualRoomFields"
                            lazy-validation
                    >
                        <v-flex xs12>
                            <v-text-field
                                    label="Pret pe persoana"
                                    v-model.number="individualRoomModel.price_person"
                                    :rules="validationRules.pricePerson"
                                    required
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field
                                    label="Numar de persoane pe camera"
                                    v-model.number="individualRoomModel.person_number"
                                    :rules="validationRules.personNumber"
                                    required
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field
                                    label="Locuri disponibile"
                                    v-model.number="individualRoomModel.vacant_places"
                                    :rules="validationRules.vacantPlaces"
                                    required
                            ></v-text-field>
                        </v-flex>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click="clearIndividualRoomModelAndClose">Close</v-btn>
                    <v-btn  :disabled="!individualRoomModel.options.valid" color="blue darken-1" flat @click.native="updateIndividualRoom" v-if="individualRoomModel.options.edit">Salveaza</v-btn>
                    <v-btn  :disabled="!individualRoomModel.options.valid" color="blue darken-1" flat @click.native="addIndividualRoom" v-else>Salveaza</v-btn>
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
        <!-- Confirm delete room dialog -->
        <v-dialog v-model="roomModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere camera?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearRoomModelAndClose">Inchide</v-btn>
                    <v-btn flat large color="error" @click="deleteRoom">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- Confirm delete individual room dialog -->
        <v-dialog v-model="individualRoomModel.options.delete" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere camera?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="clearIndividualRoomModelAndClose">Inchide</v-btn>
                    <v-btn flat large color="error" @click="deleteIndividualRoom">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
  import {mapGetters} from 'vuex'
  import axios from 'axios';
  import moment from 'moment'
  import Vue from 'vue'

  export default {

    data() {
      return {
        busy: false,
        busyDateTable: false,
        editingLocations:false,
        editingRooms:false,
        editingIndividualRooms:false,

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
        roomModelDefault: {
          id: '',
          type: '',
          options: {
            add:false,
            edit: false,
            delete:false,
            valid: false,
            editedIndex:0,
            deletedIndex:0,
          }
        },
        individualRoomModelDefault: {
          id: 0,
          price_person: '',
          person_number:'',
          vacant_places:'',
          options: {
            add:false,
            edit:  false,
            delete:false,
            valid: false,
            editedIndex:0,
            deletedIndex:0,
          }
        },
        offerModel: {
          title: '',
          description: '',
          options: {
            valid: false
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
        roomModel: {
          id: '',
          type: '',
          options: {
            add:false,
            edit: false,
            delete:false,
            valid: false,
            editedIndex:0,
            deletedIndex:0,
          }
        },
        individualRoomModel: {
          id: 0,
          price_person: '',
          person_number:'',
          vacant_places: '',
          options: {
            add:false,
            edit:  false,
            delete:false,
            valid: false,
            editedIndex:0,
            deletedIndex:0,
          }
        },
        selectedLocationID: '',
        selectedRoomTypeID: '',
        startDateMenu: false,
        endDateMenu:null,

        //current date being edited.
        currentDate: {
          locations: [],
        },
        //current location being edited.
        currentLocation: {
          id: '',
          rooms:[],
        },
        currentRoom:{
          individualRooms: [],
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
            v => /^\d+$/.test(v) || 'Este necesara o valoare numerica'
          ],
          personNumber: [
            v => !!v || 'Numarul de persoane pe camera este obligatoriu',
            v => /^\d+$/.test(v) || 'Este necesara o valoare numerica'
          ],
          vacantPlaces: [
            v => /^\d+$/.test(v) || 'Este necesara o valoare numerica',
            v => v <= this.individualRoomModel.person_number || 'Nu puteti specifica mai multe locuri disponibile decate persoane incap in camera'
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
        roomsTableHeaders: [
          {text:'ID', value:'id'},
          {text:'Tip de camera', value:'type'},
        ],
        individualRoomHeaders: [
          {text: 'ID', value: 'id'},
          {text: 'Pret pe persoana', value:'price_person'},
          {text: 'Persoane pe camera', value:'person_number'},
          {text: 'Locuri disponibile', value:'vacant_places'}
        ],
        falseId: 0, //For new offers only.
        roomFalseId: 0,
        dates: []

      }
    },
    methods: {

      async createOffer()  {

        // if(this.offerModel.options.valid){
          try {
            this.busy = true;
            const {data} = await  axios.post('/api/offers/add',{
              newOffer: this.offerModel,
              dates: this.dates,
            })
            await this.closeCreateOffer();

            this.$store.dispatch('responseMessage', {
              type: 'success',
              text: 'Oferta adaugata'
            });

            this.$emit('update:reindex', true);


          } catch (e){
            console.log(e.response.data.errors);
            for(let error of e.response.data.errors) {
              this.$store.dispatch('responseMessage', {
                type: 'error',
                text: error
              })
            }
          }
        // }

      },

      // dayFormat(){return "dd"},

      //TABLES
      //for the given date, set it as currentDate and open the locations in a table
      editLocations(date){
        this.currentDate = {}; //empty in case of rogue values.
        this.currentDate = date;
        this.editingLocations = true;
      },
      //for the given location, set it as currentLocation and open the rooms in a table
      editRooms(location){
        this.currentLocation= {};
        this.currentLocation = location;
        this.editingRooms = true;
      },
      //for the given room, set it as currentRoom and open the individual rooms in a table
      editIndividualRooms(room){
        this.currentRoom = {};
        this.currentRoom = room;
        if(!this.currentRoom.individualRooms){
          Vue.set(this.currentRoom, 'individualRooms',[]);7
        }
        this.editingIndividualRooms = true;
      },

      // for displaying the start_date and end_date in the table, use a computed function.  To store it.


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

  
      //ROOMS CRUD
      addRoomDialog(){
        this.clearRoomModelAndClose();
        this.roomModel.options.add = true;
      },
      //prepare room for delete and open confirmation dialog
      deleteRoomDialog(room){
        this.clearRoomModelAndClose();
        this.roomModel.options.deletedIndex = this.currentLocation.rooms.indexOf(room);
        this.roomModel.options.delete = true;
      },
      //assign room type to the currentLocation
      addRoom(){
        let room = JSON.parse(JSON.stringify(this.selectedRoomType))
        this.currentLocation.rooms.push(room);
        this.generateIndividualRooms(room);
        this.clearRoomModelAndClose();
      },
      //remove room from currentLocation
      deleteRoom(){
        let index = this.roomModel.options.deletedIndex;
        this.currentLocation.rooms.splice(index,1);
        this.clearRoomModelAndClose();
      },

      //INDIVIDUAL ROOMS CRUD
      //Clear model and open dialog for adding invididual room
      addIndividualRoomDialog(){
        this.clearIndividualRoomModelAndClose();
        this.individualRoomModel.options.add = true;
      },
      //prepare individual room for editing and open edit dialog
      editIndividualRoomDialog(individualRoom){
        this.clearIndividualRoomModelAndClose();
        this.individualRoomModel.price_person = individualRoom.price_person;
        this.individualRoomModel.person_number = individualRoom.person_number;
        this.individualRoomModel.options.editedIndex = this.currentRoom.individualRooms.indexOf(individualRoom);
        this.individualRoomModel.options.edit = true;
      },
      //prepare individual room for delete and open confirmation dialog
      deleteIndividualRoomDialog(individualRoom){
        this.clearIndividualRoomModelAndClose();
        this.individualRoomModel.options.deletedIndex = this.currentRoom.individualRooms.indexOf(individualRoom);
        this.individualRoomModel.options.delete = true;
      },

      //assign a new individual room to the currentRoom.
      addIndividualRoom(){
        let individualRoom = {};
        individualRoom.id = this.roomFalseId++;
        //data needed for persistence.
        individualRoom.price_person = this.individualRoomModel.price_person;
        individualRoom.person_number = this.individualRoomModel.person_number;
        individualRoom.vacant_places = this.individualRoomModel.vacant_places;
        this.currentRoom.individualRooms.push(individualRoom);
        this.clearIndividualRoomModelAndClose();
      },

      updateIndividualRoom(){
        let individualRoom = this.currentRoom.individualRooms[this.individualRoomModel.options.editedIndex];
        individualRoom.person_number = this.individualRoomModel.person_number;
        individualRoom.price_person = this.individualRoomModel.price_person;
        individualRoom.vacant_places = this.individualRoomModel.vacant_places;
        this.clearIndividualRoomModelAndClose();
      },
      //remove from currentRoom
      deleteIndividualRoom(){
        let index = this.individualRoomModel.options.deletedIndex;
        this.currentRoom.individualRooms.splice(index,1);
        this.clearIndividualRoomModelAndClose();
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

      //given the num_rooms assigned to the room type, generate the individual rooms.
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
            individualRoom.vacant_places = room.predefined_values.person_number;
            room.individualRooms.push(individualRoom);
          }
        }
      },
      //set offer model to default values, reset form fields and close any add,edit or delete dialogs.
      clearOfferModel(){
        this.$refs.offerFields.reset();
        this.offerModel = JSON.parse(JSON.stringify(this.offerModelDefault));
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
      //set room model to default values, reset form fields and close any add,edit or delete dialogs.
      clearRoomModelAndClose(){
        this.$refs.roomFields.reset();
        this.roomModel = JSON.parse(JSON.stringify(this.roomModelDefault));
      },
      //set individual room model to default values, reset form fields and close any add,edit or delete dialogs.
      clearIndividualRoomModelAndClose(){
        this.$refs.individualRoomFields.reset();
        this.individualRoomModel = JSON.parse(JSON.stringify(this.individualRoomModelDefault));
      },
      clearAllData(){
        this.clearOfferModel();
        this.clearDateModelAndClose();
        this.clearLocationModelAndClose();
        this.clearRoomModelAndClose();
        this.clearIndividualRoomModelAndClose();
        this.dates = [];
      },
      //deactivate any active tables except the default one, which is for listing dates
      closeTableViews(){
        this.editingLocations = false;
        this.editingRooms = false;
        this.editingIndividualRooms = false;
      },

      async closeCreateOffer(){
        await this.clearAllData();
        this.closeTableViews();
        this.$emit('update:dialog', false);
        this.busy = false;
      }

    },

    computed: {
      ...mapGetters({
        locations : 'GET_LOCATIONS'
      }),

      selectedLocation(){
        return this.locations.find(l => l.id === this.selectedLocationID);
      },
      selectedRoomType(){
        return this.fetchUnmodifiedLocation.rooms.find(r => r.id === this.selectedRoomTypeID);
      },
      //returns the original location in store, this is used to retrieve the rooms of the location in case the user deletes them or wants to add another type.
      fetchUnmodifiedLocation(){
        return this.currentLocation.id ?  this.locations.find(l => l.id === this.currentLocation.id) : '';
      },

    },
    props: ['dialog','reindex']

  }
</script>