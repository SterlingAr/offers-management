<template  ref="locations">
    <div class="container">
        <v-btn small  @click="newLocation()" fab dark color="indigo">
            <v-icon dark>add</v-icon>
        </v-btn>
        <v-card>
            <v-alert class="animated bounceInRight" type="success" v-model="newLocationSuccess" dismissible>
                Location created successfully.
            </v-alert>

            <v-alert class="animated bounceInRight" type="success" v-model="updateLocationSuccess" dismissible>
               Location updated successfully.
            </v-alert>

            <v-card-title>
                Locations
                <v-spacer></v-spacer>
                <v-text-field
                        append-icon="search"
                        label="Search"
                        single-line
                        hide-details
                        @input="indexTable()"
                        v-model.lazy="search"
                        v-debounce="delay"
                ></v-text-field>
            </v-card-title>
            <v-data-table
                    :headers="headers"
                    :items="locations"
                    :search="search"
            >
                <template slot="items" slot-scope="props">

                    <td>{{ props.item.id }}</td>
                    <td class="text-xs-left">{{ props.item.name }}</td>
                    <td class="text-xs-left">{{ props.item.description }}</td>
                    <td class="text-xs-left">{{ props.item.address }}</td>
                    <td class="text-xs-left">{{ props.item.phone }}</td>
                    <td class="text-xs-leftt">{{ props.item.landline }}</td>
                    <td class="justify-center layout px-0">
                        <v-btn icon class="mx-0" @click="editItem(props.item)">
                            <v-icon color="teal">edit</v-icon>
                        </v-btn>
                        <v-btn icon class="mx-0" @click="deleteItem(props.item)">
                            <v-icon color="pink">delete</v-icon>
                        </v-btn>
                    </td>
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </v-data-table>
        </v-card>
        <location-new :success-new.sync="newLocationSuccess" :dialog.sync="addDialog" :room-types="roomTypes"></location-new>
        <location-mod :success-update.sync="updateLocationSuccess" :dialog.sync="editDialog" :room-types="roomTypes"  :edited-location="editedLocation" :original-rooms="origRooms"></location-mod>
    </div>
</template>


<script>

    import Location from './Location.vue'
    import LocationNew from './LocationNew.vue'
    import LocationMod from './LocationMod.vue'
    import axios from 'axios'
    import debounce from '../../tools/debounce/debounce.js'


    export default {

        name:'locations',

        mounted() {
            console.log('Component Locations mounted.')
            this.index();
        },

        data() {
            return {
                addDialog:false,
                editDialog: false,
                newLocationSuccess: false,
                updateLocationSuccess: false,
                editedLocation: {},
                roomTypes:[],
                origRooms: [],
              search: '',
              delay: 5000,
              headers: [
                {
                  text: 'ID',
                  align: 'left',
                  value: 'id'
                },
                {
                  text: 'Location name',
                  value: 'name'
                },
                { text: 'Description', value: 'description' },
                { text: 'Address', value: 'address' },
                { text: 'Phone', value: 'phone' },
                { text: 'Landline', value: 'landline' },
              ],
              locations: [

              ]

            }
        },

        methods: {

            index() {

              axios.get('/api/locations').then((response) => {
                this.locations = response.data.locations;
              }).catch((error) => {

              });

            },

            indexTable() { //query locations using the search parameters

                axios.post('/api/locations/search', { query: this.search}).then((response) => {
                    console.log(response);
                    this.locations = response.data.locations;
                }).catch((error) => {

                });

            },


            editItem(item) {
                  let em = this;

                  axios.get('/api/rooms').then((response) => {

                    em.roomTypes=response.data.roomTypes;
                    em.editedLocation = item;
                    em.editDialog=true;
                    em.origRooms = JSON.parse(JSON.stringify(item.rooms));

                  }).catch((error) => {
                    console.log(error);
                  });


            },

            deleteItem (item) {
                const index = this.locations.indexOf(item)
                confirm('Are you sure you want to delete this item?') &&
                axios.delete('/api/locations/delete/' + item.id).then((response) => {
                  console.log(response);
                  alert('deleted');
                  this.locations.splice(index, 1);
                }).catch((error) => {
                  console.log(error);
                });
            },



            newLocation() {
                //intermediary function, for possible future actions

                let em = this;
                axios.get('/api/rooms').then((response) => {

                    em.roomTypes=response.data.roomTypes;
                    em.addDialog=true;

                }).catch((error) => {
                    console.log(error);
                });

            },


        },

        components: {
            'location' : Location,
            'location-mod': LocationMod,
            'location-new': LocationNew
        },

        directives: {debounce},

    }
</script>
