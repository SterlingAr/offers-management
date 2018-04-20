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
                    <v-btn icon @click.native="$emit('update:dialog', false) " dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Adauga o locatie noua</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createLocation">Save</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>

                    </v-menu>
                </v-toolbar>

                <v-card-text>
                    <form>
                        <v-text-field
                                v-model="locationValues.name"
                                label="Name"
                                :counter="10"
                                :error-messages="errors.collect('name')"
                                v-validate="'required|max:50'"
                                data-vv-name="name"
                                required
                        ></v-text-field>
                        <v-text-field
                                v-model="locationValues.address"
                                label="Address"
                                :error-messages="errors.collect('address')"
                                v-validate="'required|max:250'"
                                data-vv-name="address"
                                required
                        ></v-text-field>
                        <v-text-field
                                v-model="locationValues.phone"
                                label="Phone"
                                :error-messages="errors.collect('phone')"
                                v-validate="'max:20'"
                                data-vv-name="phone"
                        ></v-text-field>
                        <v-text-field
                                v-model="locationValues.landline"
                                label="Landline"
                                :error-messages="errors.collect('landline')"
                                v-validate="'max:20'"
                                data-vv-name="Landline"
                        ></v-text-field>
                        <v-text-field
                                v-model="locationValues.description"
                                label="Description"
                                :textarea=true
                                :error-messages="errors.collect('description')"
                                v-validate="'required|max:1500'"
                                data-vv-name="description"
                                required
                        ></v-text-field>


                        <v-dialog v-model="dialogRooms" max-width="500px">
                            <v-btn color="primary" dark slot="activator" class="mb-2">Add room</v-btn>
                            <v-card>
                                <v-card-title>
                                    Adauga o camera.
                                </v-card-title>
                                <v-card-text>
                                    <v-select
                                            :items="roomTypes"
                                            item-text="type"
                                            v-model="selectedType"
                                            label="Select"
                                            :error-messages="errors.collect('roomType')"
                                            v-validate="'required'"
                                            data-vv-name="roomType"
                                            item-value="id"
                                            single-line
                                    ></v-select>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                          v-model.number="numberRooms"
                                                          label="Number of rooms"
                                                          :error-messages="errors.collect('numberRooms')"
                                                          v-validate="'required|numeric'"
                                                          data-vv-name="numberRooms"
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="pricePerson"
                                                        label="Price person"
                                                        :error-messages="errors.collect('pricePerson')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="pricePerson"
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="personNumber"
                                                        label="Persons per rooms"
                                                        :error-messages="errors.collect('personNumber')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="personNumber"
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="availableRooms"
                                                        label="Available rooms"
                                                        :error-messages="errors.collect('availableRooms')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="availableRooms"
                                                ></v-text-field>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" flat @click.native="close">Cancel</v-btn>
                                    <v-btn color="blue darken-1" flat @click="createRoomWithDefaultDetails">Save</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                        <v-data-table
                                :headers="headers"
                                :items="rooms"
                                hide-actions
                                class="elevation-1"
                        >
                            <template slot="items" slot-scope="props">
                                <td>{{ props.item.id }}</td>
                                <td class="text-xs-left">{{ props.item.type }}</td>
                                <td class="text-xs-left">{{ props.item.predefined_values.num_rooms }}</td>
                                <td class="text-xs-left">{{ props.item.predefined_values.price_person }}</td>
                                <td class="text-xs-left">{{ props.item.predefined_values.person_number }}</td>
                                <td class="text-xs-left">{{ props.item.predefined_values.available_rooms }}</td>
                                <td class="justify-center layout px-0">
                                    <v-btn icon class="mx-0" >
                                        <v-icon color="teal">edit</v-icon>
                                    </v-btn>
                                    <v-btn icon class="mx-0" >
                                        <v-icon color="pink">delete</v-icon>
                                    </v-btn>
                                </td>
                            </template>
                            <template slot="no-data">
                                <!--<v-btn color="primary" @click="initialize">Reset</v-btn>-->
                            </template>
                        </v-data-table>

                        <v-btn >submit</v-btn>
                        <v-btn >clear</v-btn>
                    </form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>

    import axios from 'axios'

    export default {

        mounted() {
            console.log('Component LocationNew mounted.');
            this.$validator.localize('en', this.dictionary)
        },

        data() {
            return {

                $_veeValidate: {
                  validator: 'new'
                },

                  dialogRooms: false,
                  headers: [
                    { text: 'ID', value: 'id', sortable: false,},
                    { text: 'Room type', value: 'type', sortable: false,},
                    { text: 'Number of rooms', value: 'num_rooms',sortable: false },
                    { text: 'Price person', value: 'price_person',sortable: false },
                    { text: 'Persons per room', value: 'person_number',sortable: false },
                    { text: 'Available rooms', value: 'available_rooms',sortable: false },
                  ],

                  rooms: [],

                  selectedType : '',


                  locationValues : {
                        name : '',
                        address: '',
                        description: '',
                        phone: '',
                        landline: ''
                    },


              //room related predefined values
                  pricePerson: '',
                  personNumber: '',
                  numberRooms: '',
                  availableRooms: '',

                  dictionary: {
                    attributes: {
                      email: 'E-mail Address'
                      // custom attributes
                    },
                    custom: {
                      name: {
                        required: () => 'Name can not be empty',
                        max: 'The name field may not be greater than 10 characters'
                        // custom messages
                      },
                      select: {
                        required: 'Select field is required'
                      }
                    }
                  }
            }
        },

        methods: {

             createLocation() {


               this.$validator.validateAll(this.locationValues).then(result => {

                 if(!result){
                   return;
                 }

                 let location = {
                   locationValues : this.locationValues,
                   rooms : this.rooms
                 };

                 axios.post('/api/locations/store', {
                   location: location,
                 }).then((response) => {
                    console.log(response);
                    //show success dialog.

                 }).catch((error) => {

                   //show error flashbags
                   console.log(error);

                 });

               }).catch(error => {

               })

            },

             close () {
                this.dialogRooms = false
             },

             //creates room with the selected type and predefined details
             createRoomWithDefaultDetails() {

                let valuesObj = {
                  numberRooms : this.numberRooms,
                  pricePerson : this.pricePerson,
                  personNumber : this.personNumber,
                  availableRooms : this.availableRooms
                }

                this.$validator.validateAll(valuesObj).then(result => {

                  if(!result) {
                    return;
                  }

                  let room = {};
                  room.id  = this.selectedType;
                  room.type = this.typeTextValue;
                  room.predefined_values = {};
                  room.predefined_values.num_rooms = this.numberRooms;
                  room.predefined_values.price_person = this.pricePerson;
                  room.predefined_values.person_number = this.personNumber;
                  room.predefined_values.available_rooms = this.availableRooms;
                  this.rooms.push(JSON.parse(JSON.stringify(room)));//push copy of object.
                  this.clearRoomValues();
                  this.close();

                }).catch(() => {


                });

             },


             //method to clear rogue values, just in case
             clearLocationValues () {
                this.name = '';
                this.description = '';
                this.address = '';
                this.phone = '';
                this.landline = '';
                this.rooms = [];
                this.$validation.clear();
             },

             clearRoomValues() {
               this.numberRooms = '';
               this.pricePerson = '';
               this.personNumber = '';
               this.availableRooms = '';
               this.selectedType= '';
             }


        },

        computed: {

            typeTextValue() {
              for(let room of this.roomTypes) {
                if (room.id === this.selectedType) {
                  return room.type;
                }
              }
            }

        },

        props: ['roomTypes','dialog']
    }
</script>
