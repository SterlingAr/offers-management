<template  ref="locations">
    <div class="container">
        <table class="table">
            <thead>
            <tr>
                <th>Location name</th>
                <th >
                    <v-btn small  @click="newLocation()" fab dark color="indigo">
                        <v-icon dark>add</v-icon>
                    </v-btn>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr  v-for="location in locations">
                <td>{{location.name}}
                    <span class="pull-right">
                        <button type="button" class="btn btn-light" @click="show(location.id)"><i class="fa fa-eye"></i></button>
                        <button type="button" class="btn btn-light" @click="showModal(location)"><i class="fa fa-edit"></i></button>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>

        <location-new :dialog.sync="addDialog" :room-types="roomTypes"></location-new>

    </div>
</template>


<script>

    import Location from './Location.vue'
    import LocationNew from './LocationNew.vue'
    import LocationMod from './LocationMod.vue'
    import axios from 'axios'

    // import VModal from 'vue-js-modal'
    //
    // Vue.use(VModal, {
    //     componentName: "new-modal"
    // })


    export default {

        name:'locations',

        mounted() {
            console.log('Component Locations mounted.')
        },

        data() {
            return {
                addDialog:false,
                roomTypes:[],

            }
        },

        methods: {

            index() {
                console.log("refreshing");
                axios.get('/api/locations').then((response) => {
                    this.locations = response.data.locations;
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
                    console.log(response);

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

        props: ['locations']
    }
</script>
