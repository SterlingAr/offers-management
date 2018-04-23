<template>
    <div>
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Adauga o oferta noua</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createOffer">Salveaza</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>
                    </v-menu>
                </v-toolbar>
                <v-card-text>

                    <v-alert type="error" :value="hasErrors" v-for="error in serverSideErrors">
                        {{error}}
                    </v-alert>

                    <v-container grid-list-md text-xs-center>
                        <v-layout row wrap>


                            <v-flex xs12 md6 lg6 sm12>
                                <form>
                                    <v-text-field
                                            label="Titlu oferta"
                                            v-model="newOffer.title"
                                            :error-messages="errors.collect('title')"
                                            v-validate="'required|max:255'"
                                            data-vv-name="offerTitle"
                                            required
                                    ></v-text-field>
                                    <v-text-field
                                            v-model="newOffer.description"
                                            label="Descriere oferta"
                                            :textarea=true
                                            :error-messages="errors.collect('description')"
                                            v-validate="'required|max:1500'"
                                            data-vv-name="description"
                                            required
                                    ></v-text-field>
                                </form>
                            </v-flex>


                            <v-flex xs12 md6 lg6 sm12>
                                <p class="headline center">Adauga o data</p>
                                    <v-dialog
                                            ref="dialog1"
                                            persistent
                                            v-model="modal1"
                                            lazy
                                            full-width
                                            width="290px"
                                            :return-value.sync="startDate"
                                    >
                                        <v-text-field
                                                slot="activator"
                                                label="Data inceput"
                                                v-model="startDate"
                                                prepend-icon="event"
                                                lazy
                                                :error-messages="errors.collect('startDate')"
                                                v-validate="'required'"
                                                data-vv-name="startDate"
                                                readonly
                                        ></v-text-field>
                                        <v-date-picker v-model="startDate" scrollable locale="ro">
                                            <v-spacer></v-spacer>
                                            <v-btn flat color="primary" @click="modal1 = false">Cancel</v-btn>
                                            <v-btn flat color="primary" @click="$refs.dialog1.save(startDate)">OK</v-btn>
                                        </v-date-picker>
                                    </v-dialog>
                                    <v-dialog
                                            ref="dialog2"
                                            persistent
                                            v-model="modal2"
                                            lazy
                                            full-width
                                            width="290px"
                                            :return-value.sync="endDate"
                                    >
                                        <v-text-field
                                                slot="activator"
                                                label="Data sfarsit"
                                                v-model="endDate"
                                                prepend-icon="event"
                                                lazy
                                                :error-messages="errors.collect('endDate')"
                                                v-validate="'required'"
                                                data-vv-name="endDate"
                                                readonly

                                        ></v-text-field>
                                        <v-date-picker v-model="endDate" scrollable locale="ro">
                                            <v-spacer></v-spacer>
                                            <v-btn flat color="primary" @click="modal2 = false">Cancel</v-btn>
                                            <v-btn flat color="primary" @click="$refs.dialog2.save(endDate)">OK</v-btn>
                                        </v-date-picker>
                                    </v-dialog>
                                    <v-btn
                                            dark
                                            color="blue"
                                            class="right"
                                            @click="addDate"
                                    >
                                        <v-icon>add</v-icon>
                                    </v-btn>
                            </v-flex>

                            <v-flex  xs12 sm12 md12 lg12 v-if="newOffer.dates.length>0">
                                <p class="headline">Adauga o locatie </p>
                                <v-select
                                        label="Select"
                                        :items="locations"
                                        v-model="selectedLocationID"
                                        item-text="name"
                                        item-value="id"
                                ></v-select>
                                <v-btn
                                        dark
                                        color="blue"
                                        class="right"
                                        @click="addLocation()"

                                >
                                    <v-icon>add</v-icon>
                                </v-btn>
                            </v-flex>



                            <v-flex xs12 sm12 md12 lg12>
                                <v-tabs
                                        dark
                                        color="blue"
                                        show-arrows
                                        v-if="newOffer.dates.length > 0"
                                >
                                    <v-tabs-slider color="yellow"></v-tabs-slider>
                                    <v-tab
                                            v-for="date in newOffer.dates"
                                            :key="date.id"
                                            :href="'#tab-' + date.id"
                                    >
                                        {{ date.start_date }} - {{ date.end_date }}
                                    </v-tab>
                                    <v-tabs-items>
                                        <v-tab-item
                                                v-for="date in newOffer.dates"
                                                :key="date.id"
                                                :id="'tab-' + date.id"
                                        >
                                            <v-expansion-panel v-if="date.locations.length > 0">
                                                <v-expansion-panel-content v-for="location in date.locations" >
                                                    <div slot="header">{{location.name}}</div>
                                                    <v-card>
                                                        <v-card-text >
                                                            <v-data-table
                                                                    :headers="roomHeaders"
                                                                    :items="location.rooms"
                                                                    hide-actions
                                                                    class="elevation-1"
                                                            >
                                                                <template slot="items" slot-scope="props">
                                                                    <td>{{ props.item.type}}</td>
                                                                    <td class="text-xs-left">{{ props.item.predefined_values.num_rooms }}</td>
                                                                    <td class="justify-center layout px-0">
                                                                        <v-btn icon class="mx-0" @click="editRoom(props.item,date,location)">
                                                                            <v-icon color="teal">edit</v-icon>
                                                                        </v-btn>
                                                                        <v-btn icon class="mx-0" @click="deleteRoom(date,location, props.item)">
                                                                            <v-icon color="pink">delete</v-icon>
                                                                        </v-btn>
                                                                    </td>
                                                                </template>
                                                            </v-data-table>
                                                            <v-card-text v-if="addingRoom">
                                                                <v-select
                                                                        label="Selecteaza"
                                                                        :items="originalRooms"
                                                                        item-text="type"
                                                                        item-value="id"
                                                                        v-model="selectedOriginalRoomID"
                                                                ></v-select>
                                                            </v-card-text>
                                                            <v-btn
                                                                    v-show="!addingRoom"
                                                                    @click="fetchRooms(location.id)"
                                                                    class="right"
                                                                    color="blue"
                                                            >
                                                                Adauga camera
                                                            </v-btn>

                                                            <v-btn
                                                                    v-show="addingRoom"
                                                                    @click="addRoom(date,location) "
                                                                    class="right"
                                                                    color="blue"
                                                            >
                                                                Ok
                                                            </v-btn>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-expansion-panel-content>
                                            </v-expansion-panel>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </v-tabs>
                            </v-flex>
                        </v-layout>
                    </v-container>
        <v-dialog v-model="editRoomDialog" v-if="editRooms.length > 0">
                <v-card>
                    <v-toolbar card dark color="primary">
                        <v-btn icon @click="editRoomDialog = false">
                            <v-icon>close</v-icon>
                        </v-btn>
                        <v-toolbar-title>
                            Editare tip camera
                            <b>{{ currentEditedRoom.type }}</b> , click on the cell to edit.
                        </v-toolbar-title>
                        <v-toolbar-items>
                            <v-btn dark right flat @click.native="createOffer">Salveaza</v-btn>
                        </v-toolbar-items>

                    </v-toolbar>

                    <v-data-table
                            :headers="dateLocationRoomsHeaders"
                            :items="editRooms"
                            hide-actions
                            class="elevation-1"
                    >
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">{{props.item.id}}</td>
                            <td class="text-xs-left">
                                <v-edit-dialog
                                        :return-value.sync="props.item.offer_details.price_person"
                                        large
                                        lazy
                                        persistent
                                >
                                    <div>{{ props.item.offer_details.price_person }}</div>
                                    <div slot="input" class="mt-3 title">Actualizeaza pret pe persoana</div>
                                    <v-text-field
                                            slot="input"
                                            label="Editeaza"
                                            v-model="props.item.offer_details.price_person"
                                            single-line
                                            autofocus
                                    ></v-text-field>
                                </v-edit-dialog>
                            </td>
                            <td>
                                <v-edit-dialog
                                        :return-value.sync="props.item.offer_details.person_number"
                                        large
                                        lazy
                                        persistent
                                >
                                    <div>{{ props.item.offer_details.person_number }}</div>
                                    <div slot="input" class="mt-3 title">Update numar persoane</div>
                                    <v-text-field
                                            slot="input"
                                            label="Editeaza"
                                            v-model="props.item.offer_details.person_number"
                                            single-line
                                            autofocus
                                    ></v-text-field>
                                </v-edit-dialog>
                            </td>
                            <td class="justify-center layout px-0">
                                <v-btn icon class="mx-0" >
                                    <v-icon color="pink">delete</v-icon>
                                </v-btn>
                            </td>
                        </template>
                        <template slot="no-data">
                        </template>
                    </v-data-table>
                </v-card>
            </v-dialog>
        </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>

    import axios from 'axios'
    import {mapActions,mapGetters} from 'vuex'
    export default {

      mounted() {
        // this.getLocations();
      },

      data() {
        return {

          $_veeValidate: {
            validator: 'new'
          },

          roomHeaders: [
             { text: 'Type', value: 'type' },
             { text: 'Number of rooms', value: 'num_rooms' },
          ],


          hasErros: false,
          serverSideErrors: [],

          dateLocationRoomsHeaders: [
            { text: 'ID', value: 'id' },
            {text: "Pret pe persoana", value: 'price_person'},
            {text: "Numar persoane", value: 'person_number'}
          ],

          startDate: null,
          endDate: null,
          modal1: false,
          modal2: false,
          selectingDate : false,
          editRoomDialog:false,
          editRooms: [],
          currentEditedRoom: {},
          addingRoom : false,
          originalRooms: [],
          selectedOriginalRoomID : '',
          roomTempId: 0,

          newOffer : {
            title: '',
            description: '',
            dates: [

            ]
          },


          dateId: 0,
          selectedLocations: [],
          selectedLocationID: null,
          text : 'lorem ipsum'

        }
      },

      methods : {

        ...mapActions({
          getLocations : 'getLocations'
        }),


        createOffer() {

          let validateValues = {
            title : this.newOffer.title,
            description: this.newOffer.title,
          }

          this.$validator.validateAll(validateValues).then(result => {

            if (!result) {
              return;
            }

            axios.post('/api/offers/add', {newOffer: this.newOffer}).then((response) => {

              console.log(response);
              this.$emit('update:dialog', false);
              this.$emit('update:successNew', true);

            }).catch((error) => {

              console.log(error);

              this.hasErrors = true;
              this.serverSideErrors = error.response.data;

            })
          }).catch(error => {
            console.log(error);
          });
        },

        addDate() {

          let validateValues = {
            startDate : this.startDate,
            endDate: this.endDate,
          }

          this.$validator.validateAll(validateValues).then(result => {

            if (!result) {
              return;
            }

            let date = {
              id: this.dateId++,
              start_date: this.startDate,
              end_date: this.endDate,
              locations: [],
              offerDateLocationRooms: []
            };
            this.newOffer.dates.push(date);
            this.startDate = null;
            this.endDate = null;

            this.addSelectedLocationsToNewDate(date);

          }).catch(error => {

          });



        },

        addSelectedLocationsToNewDate(date) {
          for(let location of this.selectedLocations)
          {
            date.locations.push(location);
            this.createOfferDateLocationRooms(date,location);
          }
        },

        //for each room in the locations belonging to this OfferDate, create the OfferDateLocationRoom object
        createOfferDateLocationRooms (date,location) {
            for(let room of location.rooms) {
               let num = room.predefined_values.num_rooms;
               if(num > 0){
                 for(let i = 0; i < num; i++) {
                   let r = {};
                   r.id = this.roomTempId++;
                   r.location_id = location.id;
                   r.room_id = room.id;
                   r.type = room.type;
                   r.num_rooms = room.predefined_values.num_rooms;
                   r.offer_details = {};
                   if(room.predefined_values.price_person === null) {
                     r.offer_details.price_person = 0;
                   } else {
                     r.offer_details.price_person = room.predefined_values.price_person;
                   }
                   if(room.predefined_values.person_number === null) {
                     r.offer_details.person_number = 0;
                   } else {
                     r.offer_details.person_number = room.predefined_values.person_number;
                   }

                   date.offerDateLocationRooms.push(r);
                 }
               }
            }
        },

        //same as above, but only for one room.
        createOfferDateLocationRoom (date,location,room) {
            let num = room.predefined_values.num_rooms;
            if(num > 0){
              for(let i = 0; i < num; i++) {
                let r = {};
                r.id = this.roomTempId++;
                r.location_id = location.id;
                r.room_id = room.id;
                r.type = room.type;
                r.num_rooms = room.predefined_values.num_rooms;
                r.offer_details = {};
                if(room.predefined_values.price_person === null) {
                  r.offer_details.price_person = 0;
                } else {
                  r.offer_details.price_person = room.predefined_values.price_person;
                }
                if(room.predefined_values.person_number === null) {
                  r.offer_details.person_number = 0;
                } else {
                  r.offer_details.person_number = room.predefined_values.person_number;
                }
                date.offerDateLocationRooms.push(r);
              }
            }
        },

        addLocation() {
            let index = this.selectedLocations.indexOf(this.selectedLocation)
            if(index === -1) {

               this.selectedLocations.push(this.selectedLocation);

              for(let date of this.newOffer.dates) {
                date.locations.push(this.selectedLocation);
                this.createOfferDateLocationRooms(date,this.selectedLocation)
              }
            } else {alert('location already added');}
        },

        addRoom(date,location) {
          for (let r of location.rooms) {
            console.log(r)
            console.log(this.selectedOriginalRoomID)
            if(r.id === this.selectedOriginalRoomID) {
              alert('Romm type already exists in the location')
              return;
            }
          }
          let room = this.findRoom(location);
          location.rooms.push(room);
          this.createOfferDateLocationRoom(date,location,room);
          this.addingRoom = false;

        },

        findRoom(location) {
          for(let loc of this.locations) {
            if(location.id === loc.id ) {
              for(let room of loc.rooms){
                if(room.id === this.selectedOriginalRoomID)
                {
                  return JSON.parse(JSON.stringify(room));
                }
              }
            }
          }
          return false;
        },

        fetchRooms(locationId)
        {
          this.originalRooms = [];

          for(let location of this.locations) {
            if(location.id === locationId)
            {
               this.originalRooms = JSON.parse(JSON.stringify(location.rooms));
               this.addingRoom = true;
               return;
            }
          }
        },

        //To edit all variations of this room.
        editRoom(room,date,location) {
          this.editRoomDialog = true;

          let deepObj  = [];
          for(let r of date.offerDateLocationRooms) {
            if(r.room_id === room.id && r.location_id === location.id) {
              deepObj.push(r)
            }
          }
          this.currentEditedRoom = room; // to use aditional data in the modal.
         this.editRooms = deepObj;

        },

        deleteRoom(date,location,room) {

          let index = location.rooms.indexOf(room)

          let yes = confirm('Are you sure you want to delete this item?');
          console.log(yes)
            if(yes) {

              let removals = [];
              for(let r of date.offerDateLocationRooms) {
                console.log(room.id);
                if(r.room_id === room.id && r.location_id === location.id) {
                  removals.push(date.offerDateLocationRooms.indexOf(r));
                }
              }

              let newOfferDate = date.offerDateLocationRooms.filter(function(r) {
                return !(r.room_id === room.id && r.location_id === location.id)
              });

              console.log(newOfferDate);
              date.offerDateLocationRooms = newOfferDate;
              location.rooms.splice(index, 1);
            }
        }
      },

      computed : {
        ...mapGetters({
          locations : 'GET_LOCATIONS'
        }),

        selectedLocation() {
          for(let location of this.locations) {
            if(location.id === this.selectedLocationID){
              return JSON.parse(JSON.stringify(location));
            }
          }
        }
      },

      props: ['dialog','successNew']
    }

</script>