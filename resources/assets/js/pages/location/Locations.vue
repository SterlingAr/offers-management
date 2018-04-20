<template  ref="locations">
    <div class="container">
        <v-btn small  @click="newLocation()" fab dark color="indigo">
            <v-icon dark>add</v-icon>
        </v-btn>
        <v-card>
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
                    :items="items"
                    :search="search"
            >
                <template slot="items" slot-scope="props">

                    <td>{{ props.item.id }}</td>
                    <td class="text-xs-right">{{ props.item.name }}</td>
                    <td class="text-xs-right">{{ props.item.description }}</td>
                    <td class="text-xs-right">{{ props.item.address }}</td>
                    <td class="text-xs-right">{{ props.item.phone }}</td>
                    <td class="text-xs-right">{{ props.item.landline }}</td>
                    <td class="justify-center layout px-0">
                        <v-btn icon class="mx-0" @click="show(props.item.id)">
                            <v-icon color="blue">info</v-icon>
                        </v-btn>
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
        <location-new :dialog.sync="addDialog" :room-types="roomTypes"></location-new>
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
                roomTypes:[],
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
              items: [

              ]

            }
        },

        methods: {

            index() {

              axios.get('/api/locations').then((response) => {
                this.items = response.data.locations;
              }).catch((error) => {

              });

            },

            indexTable() { //query locations using the search parameters

                axios.post('/api/locations/search', { query: this.search}).then((response) => {
                    console.log(response);
                    this.items = response.data.locations;
                }).catch((error) => {

                });

            },

            show(id) {

                axios.get('/api/locations/' + id).then((response) => {
                    console.log(response);
                    this.showModal(response.data.location);


                }).catch((error) => {
                    console.log(error);
                });
            },

            mod(id) {

            },

            newLocation() {
                //intermediary function, for possible future actions

                let em=this;
                axios.get('/api/rooms').then((response) => {

                    em.roomTypes=response.data.roomTypes;
                    em.addDialog=true;

                }).catch((error) => {
                    console.log(error);
                });

            },



            modModal(location){
                this.$modal.show(LocationMod, {
                    location: location
                },{
                    draggable:true
                });
            },

            newModal(roomTypes){

                // this.$modal.show('new', {roomTypes : roomTypes})

                this.$modal.show(LocationNew, {
                    roomTypes : roomTypes
                },{
                    draggable:true,
                    adaptive:true,
                    resizable:true,
                    width:900,
                    height:650
                });

            }

        },

        components: {
            'location' : Location,
            'locationMod': LocationMod,
            'location-new': LocationNew
        },

        directives: {debounce},

        props: ['locations']
    }
</script>
