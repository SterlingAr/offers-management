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
                    <v-toolbar-title>Edit location</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="updateLocation">Update</v-btn>
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
                    <form>
                        <v-text-field
                                v-model="editedLocation.name"
                                label="Location name"
                                :counter="10"
                                :error-messages="errors.collect('name')"
                                v-validate="'required|max:50'"
                                data-vv-name="name"
                                required
                        ></v-text-field>
                        <v-text-field
                                v-model="editedLocation.address"
                                label="Adress"
                                :error-messages="errors.collect('address')"
                                v-validate="'required|max:250'"
                                data-vv-name="address"
                                required
                        ></v-text-field>
                        <v-text-field
                                v-model="editedLocation.phone"
                                label="Phone"
                                :error-messages="errors.collect('phone')"
                                v-validate="'required|max:35'"
                                data-vv-name="phone"
                        ></v-text-field>
                        <v-text-field
                                v-model="editedLocation.landline"
                                label="Landline"
                                :error-messages="errors.collect('landline')"
                                v-validate="'required|max:35'"
                                data-vv-name="landline"
                        ></v-text-field>
                        <v-text-field
                                v-model="editedLocation.description"
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
                                    {{formTitle}}
                                </v-card-title>
                                <v-card-text>
                                    <v-select
                                            :items="roomTypes"
                                            item-text="type"
                                            v-model.number="selectedType"
                                            label="Select"
                                            :error-messages="errors.collect('roomType')"
                                            v-validate="'required|numeric'"
                                            data-vv-name="roomType"
                                            item-value="type"
                                            single-line
                                            required
                                    ></v-select>
                                    <v-container grid-list-md>
                                        <v-layout wrap>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="editedRoom.predefined_values.num_rooms"
                                                        label="Number of rooms"
                                                        :error-messages="errors.collect('numberRooms')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="numberRooms"
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="editedRoom.predefined_values.price_person"
                                                        label="Price per person"
                                                        :error-messages="errors.collect('pricePerson')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="pricePerson"
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm6 md4>
                                                <v-text-field
                                                        v-model.number="editedRoom.predefined_values.person_number"
                                                        label="Number of persons per room"
                                                        :error-messages="errors.collect('personNumber')"
                                                        v-validate="'required|numeric'"
                                                        data-vv-name="personNumber"
                                                ></v-text-field>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" flat @click.native="close">Anuleaza</v-btn>
                                    <v-btn color="blue darken-1" flat @click.native="save">Salveaza</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>

                        <v-data-table
                                :headers="headers"
                                :items="editedLocation.rooms"
                                hide-actions
                                class="elevation-1"
                        >
                            <template slot="items" slot-scope="props">
                                <td>{{ props.item.id }}</td>
                                <td class="text-xs-left">{{ props.item.type }}</td>
                                <td class="text-xs-left">
                                    {{ props.item.predefined_values.num_rooms }}
                                </td>
                                <td class="text-xs-left">{{ props.item.predefined_values.price_person }}</td>
                                <td class="text-xs-left">{{ props.item.predefined_values.person_number }}</td>
                                <td class="justify-center layout px-0">
                                    <v-btn icon class="mx-0" @click="editItem(props.item)">
                                        <v-icon color="teal">edit</v-icon>
                                    </v-btn>
                                    <v-btn icon class="mx-0" @click="deleteItem(props.item)">
                                        <v-icon color="pink">delete</v-icon>
                                    </v-btn>
                                </td>
                            </template>
                        </v-data-table>
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
      this.$validator.localize('en', this.dictionary)
    },

    data() {
      return {

        $_veeValidate: {
          validator: 'new'
        },
        hasErros: false,
        serverSideErrors: [],

        dialogRooms: false,
        headers: [
          { text: 'ID', value: 'id', sortable: false,},
          { text: 'Room type', value: 'type', sortable: false,},
          { text: 'Number of rooms', value: 'num_rooms',sortable: false },
          { text: 'Price per person', value: 'price_person',sortable: false },
          { text: 'Persons per room', value: 'person_number',sortable: false },
        ],
        rooms: [],
        removals: {
          rooms : []
        },

        selectedType : '',

        editedIndex: -1,

        editedRoom: {
          id: 0,
          type: '',
          predefined_values : {
            price_person: '',
            person_number: '',
            num_rooms: '',
          }
        },

        defaultRoom: {
          id: 0,
          type: '',
          predefined_values : {
            price_person: '',
            person_number: '',
            num_rooms: '',
          }
        },
      }
    },

    methods: {

      updateLocation() {

        this.$validator.validateAll(this.editedLocationValues).then(result => {

          if(!result){
            return;
          }

          let location = {
            editedLocation : this.editedLocation,
            rooms : this.rooms
          };

          axios.post('/api/locations/update/'+ this.editedLocation.id, {
            location: this.editedLocation,
            removals: this.removals
          }).then((response) => {
            console.log(response);
            this.$emit('update:dialog', false);
            this.$emit('update:successUpdate', true);
            //show success dialog.
            this.hasErrors = false;
            this.serverSideErrors = '';

          }).catch((error) => {

            //show error flashbags
            console.log(error);
            this.hasErrors = true;
            this.serverSideErrors = error.response.data;

          });

        }).catch(error => {

        })

      },


      editItem (item) {
        this.selectedType = '';
        this.editedIndex = this.editedLocation.rooms.indexOf(item)
        this.editedRoom = JSON.parse(JSON.stringify(item));
        this.dialogRooms = true
      },

      deleteItem (item) {
        if(this.roomIsPersisted(item.id)){
          if(!this.roomAlreadyInRemovals(item.id)){
            this.removals.rooms.push(item);
          }
        }

        const index = this.editedLocation.rooms.indexOf(item)
        confirm('Are you sure you want to delete this location?') && this.editedLocation.rooms.splice(index, 1)
      },

      close () {
        this.dialogRooms = false;
        this.editedRoom = Object.assign({}, this.defaultRoom);
        this.editedIndex = -1;
      },

      save () {

        this.$validator.validateAll(this.valuesRoom).then(result => {

          if(!result) {
            return;
          }

          this.editedRoom.type = this.typeTextValue;

          if (this.editedIndex > -1) {
            this.editedRoom.id = this.selectedType;
            Object.assign(this.editedLocation.rooms[this.editedIndex], this.editedRoom)
          } else {

            if(this.roomAlreadyAdded()){
              alert('Room was already added.');
              return;
            }

            this.editedRoom.id = this.selectedType;
            this.editedLocation.rooms.push(this.editedRoom)
          }
          this.close()
        }).catch(error => {

        });

      },

      //check if the room is already scheduled for removal.
      roomAlreadyInRemovals(id) {
        for(let room of this.removals.rooms ) {
          if(room.id === id) {
            return true
          }
        }
        return false;
      },

      //check against original rooms if  the room is new, that way we add removals only the rooms it had previously and not the ones added during the edit.
      roomIsPersisted(id) {
        for(let room of this.originalRooms){
          if(room.id === id) {
            return true;
          }
        }
        return false;
      },

      roomAlreadyAdded() {
        for(let room of this.editedLocation.rooms) {
          if(room.id === this.selectedType){
            console.log(room);
            return true;
          }
        }
        return false;
      },

    },

    computed: {

      formTitle () {
        return this.editedIndex === -1 ? 'New room' : 'Update room'
      },


      typeTextValue() {
        for(let room of this.roomTypes) {
          if (room.id === this.selectedType) {
            return room.type;
          }
        }
      },

      valuesRoom () {
        return {
          roomType : this.selectedType,
          numberRooms : this.editedRoom.predefined_values.num_rooms,
          pricePerson : this.editedRoom.predefined_values.price_person,
          personNumber : this.editedRoom.predefined_values.person_number,
        }
      },

      editedLocationValues () {

        return {
          name : this.editedLocation.name,
          address: this.editedLocation.address,
          description: this.editedLocation.description,
          phone: this.editedLocation.phone,
          landline: this.editedLocation.landline
        }
      }
    },

    props: ['roomTypes','dialog', 'successUpdate', 'editedLocation', 'originalRooms']
  }
</script>
