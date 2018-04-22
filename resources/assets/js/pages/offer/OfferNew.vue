<template>
    <v-container
            grid-list-xl
            text-xl-center
            align-center
            justify-center
    >
    <v-dialog
                    v-model="dialog"
                    fullscreen
                    hide-overlay
                    transition="dialog-bottom-transition"
                    scrollable
            >

                <v-card tile>
                    <v-toolbar :clipped-right="true" card dark color="primary">
                        <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                            <v-icon>close</v-icon>
                        </v-btn>
                        <v-toolbar-title>Adauga o oferta noua</v-toolbar-title>
                        <v-toolbar-items>
                            <v-btn dark flat @click.native="createOffer">Save</v-btn>
                        </v-toolbar-items>

                    </v-toolbar>
                    <v-layout row wrap  justify-space-between>
                        <v-flex xs12 sm8 md8 lg8 >
                            <form>
                                <v-text-field
                                        label="Offer title"
                                        v-model="newOffer.title"
                                        :error-messages="errors.collect('description')"
                                        :counter="10"
                                        required
                                ></v-text-field>
                                <v-text-field
                                        v-model="newOffer.description"
                                        label="Description"
                                        :textarea=true
                                        :error-messages="errors.collect('description')"
                                        v-validate="'required|max:1500'"
                                        data-vv-name="description"
                                        required
                                ></v-text-field>
                            </form>
                        </v-flex>
                        <v-flex xs12 sm4 md3 lg4 >
                            <p class="headline center">Add a Date</p>
                            <v-flex  class="animated fadeInDown">
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
                                            label="Start date"
                                            v-model="startDate"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker v-model="startDate" scrollable>
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="modal1 = false">Cancel</v-btn>
                                        <v-btn flat color="primary" @click="$refs.dialog1.save(startDate)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
                            </v-flex>
                            <v-flex class="animated fadeInDown">
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
                                            label="End date"
                                            v-model="endDate"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker v-model="endDate" scrollable>
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="modal2 = false">Cancel</v-btn>
                                        <v-btn flat color="primary" @click="$refs.dialog2.save(endDate)">OK</v-btn>
                                    </v-date-picker>
                                </v-dialog>
                            </v-flex>
                            <v-btn
                                    dark
                                    color="blue"
                                    class="right"
                                    @click="addDate"

                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                        </v-flex>
                        <v-flex xs12 md12 lg12 sm12 >
                            <v-layout row wrap>
                                <v-flex xs12 md12 lg12 sm12>
                                    <p class="headline">Add location</p>
                                </v-flex>
                                <v-flex xs4>
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
                            </v-layout>
                        </v-flex>
                        <v-flex xs12 md12 lg12 sm12 v-if="newOffer.dates.length > 0">
                            <v-tabs
                                    dark
                                    color="blue"
                                    show-arrows
                            >
                                <v-tabs-slider color="yellow"></v-tabs-slider>
                                <v-tab
                                        v-for="date in newOffer.dates"
                                        :key="date.id"
                                        :href="'#tab-' + date.id"
                                >
                                   s{{ date.start_date }} - {{ date.end_date }}
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
                                                    <v-card-text v-if="location.rooms.length > 0">
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
                                                                    <v-btn icon class="mx-0" @click="deleteRoom(props.item)">
                                                                        <v-icon color="pink">delete</v-icon>
                                                                    </v-btn>
                                                                </td>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                                </v-card>
                                            </v-expansion-panel-content>
                                        </v-expansion-panel>
                                    </v-tab-item>
                                </v-tabs-items>
                            </v-tabs>
                        </v-flex>
                    </v-layout>
                </v-card>
            </v-dialog>
            <v-dialog v-model="editRoomDialog" v-if="editRooms.length > 0">
                <v-card>
                    <v-toolbar card dark color="primary">
                        <v-btn icon @click="editRoomDialog = false">
                            <v-icon>close</v-icon>
                        </v-btn>
                        <v-toolbar-title>
                            Editing room of type
                            <b>{{ currentEditedRoom.type }}</b> , click on the cell to edit.
                        </v-toolbar-title>
                        <v-toolbar-items>
                            <v-btn dark right flat @click.native="createOffer">Save</v-btn>
                        </v-toolbar-items>

                    </v-toolbar>

                    <v-data-table
                            :headers="dateLocationRoomsHeaders"
                            :items="editRooms"
                            hide-actions
                            class="elevation-1"
                    >
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">
                                <v-edit-dialog
                                        :return-value.sync="props.item.offer_details.price_person"
                                        large
                                        lazy
                                        persistent
                                >
                                    <div>{{ props.item.offer_details.price_person }}</div>
                                    <div slot="input" class="mt-3 title">Update price person</div>
                                    <v-text-field
                                            slot="input"
                                            label="Edit"
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
                                    <div slot="input" class="mt-3 title">Update person number</div>
                                    <v-text-field
                                            slot="input"
                                            label="Edit"
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
    </v-container>
</template>


<script>

    import axios from 'axios'
    import {mapActions,mapGetters} from 'vuex'
    export default {

      mounted() {
        this.getLocations();
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

          dateLocationRoomsHeaders: [
            {text: "Price person", value: 'price_person'},
            {text: "Num person", value: 'person_number'}
          ],

          startDate: null,
          endDate: null,
          modal1: false,
          modal2: false,
          selectingDate : false,
          editRoomDialog:false,
          editRooms: [],
          currentEditedRoom: {},

          newOffer : {
            title: '',
            description: '',
            dates: [

            ]
          },
          // locations: [],


          //its a new object, so there is no date id, however you can first create the id and then forward this object to persist.
          //if you edit ar
          // offerDateLocationRooms: [
          //   {
          //     location_id: '',
          //     room_id: '',
          //     offer_details : {
          //           price_person : '',
          //           person_number : '',
          //     }
          //   }
          // ],

          dateId: 0,
          // locations: [],
          selectedLocations: [],
          selectedLocationID: null,
          text : 'lorem ipsum'

        }
      },

      methods : {

        ...mapActions({
          getLocations : 'getLocations'
        }),


        addDate() {
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
                   r.location_id = location.id;
                   r.room_id = room.id;
                   r.type = room.type;
                   r.num_rooms = room.predefined_values.num_rooms;
                   r.offer_details = {};
                   r.offer_details.price_person = room.predefined_values.price_person;
                   r.offer_details.person_number = room.predefined_values.person_number;
                   date.offerDateLocationRooms.push(r);
                 }
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

        //To edit all variations of this room.
        editRoom(room,date,location) {
          this.editRoomDialog = true;
          // this.iterableRooms = item.predefined_values.num_rooms;
          //open a modal, which will have a table with iterableRoom * type.
          //the first time the data is not persisted in a object, but if you click save, it is saved
          //next time you edit the object, you first check if it exists, if it does you just load the dialog with the existing data
          //if it doesn't exist, use a function to create a dummy object.
          //or even better, for each room already create the object, so when you edit you simply pick up from an array.

          // for(let date of this.newOffer.dates) {
          //   if(date.id === dateId) {
          //
          //   }
          // }

          // let dateIndex = this.newOffer.dates.indexOf(date);
          let deepObj  = [];
          for(let r of date.offerDateLocationRooms) {
            if(r.room_id === room.id && r.location_id === location.id) {
              deepObj.push(r)
            }
          }
          this.currentEditedRoom = room; // to use aditional data in the modal.
          // console.log(deepObj);
         this.editRooms = deepObj;

         // this.editRooms = date.offerDateLocationRooms.filter(function(r) {
         //    return r.room_id === room.id && r.location_id === location.id
         //  })
         //  console.log(JSON.parse(JSON.stringify(this.editRooms)));

        },

        deleteRoom(item) {
          console.log(item);
        }
      },

      computed : {
        ...mapGetters({
          locations : 'GET_LOCATIONS'
        }),

        selectedLocation() {
          for(let location of this.locations) {
            if(location.id === this.selectedLocationID){
              return location;
            }
          }
        }
      },

      props: ['dialog']
    }

</script>