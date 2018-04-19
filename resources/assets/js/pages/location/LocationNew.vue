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
                        <v-btn dark flat @click.native="dialog = false">Save</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>

                    </v-menu>
                </v-toolbar>





                <v-card-text>
        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Name</span>
            </div>
            <input type="text" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm"  v-model="locationData.name">
        </div>
        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="inputGroup-sizing-sm">Address</span>
            </div>
            <input type="text" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" v-model="locationData.address">
        </div>

        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Phone</span>
            </div>
            <input type="text" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" v-model="locationData.phone">
        </div>
        <div class="input-group input-group-sm mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Landline</span>
            </div>
            <input type="text" class="form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" v-model="locationData.landline">
        </div>
        <hr>
        <div class="input-group mb-3">

            <div class="input-group-prepend">
                <label class="input-group-text" for="inputGroupSelect01">Room Type</label>
            </div>



            <select class="custom-select" id="inputGroupSelect03" v-model="selectedType">
                <!--<option selected>{{roomTypes}}</option>-->
                <option v-for="roomType in roomTypes" v-bind:value="roomType.id">{{roomType.type}}</option>
            </select>

            <div class="input-group-append">
                <span class="input-group-text" id="">Num</span>
            </div>
            <input type="text" class="form-control" placeholder="Number of rooms" v-model="numRooms">

            <input type="text" class="form-control" aria-label="Amount (to the nearest dollar)" placeholder="Default price" v-model="price">
            <div class="input-group-prepend">
                <span class="input-group-text">€</span>
            </div>

            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" @click="addRoomType()"><i class="fa fa-plus fa-lg"></i></button>
            </div>


        </div>
        <p class="h4">RoomTypes</p>
        <hr>

        <table class="table table-sm" v-if="assocRoomTypes">
            <thead>
            <tr>
                <th scope="col">Type</th>
                <th scope="col">Price</th>
                <th scope="col">Num Rooms</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="assocType in assocRoomTypes">
                <td>{{assocType.type}}</td>
                <td>{{assocType.details.price}}</td>
                <td>{{assocType.details.num_rooms}}</td>
                <!--<td><button class="btn btn-outline-secondary" type="button" @click="removeAssoc(assocType.id)"><i class="fa fa-times"></i></button></td>-->
            </tr>
            </tbody>
        </table>

        <hr>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">Location description</span>
            </div>

            <textarea class="form-control" aria-label="With textarea" v-model="locationData.description"></textarea>

        </div>
        <br>
        <!--<button type="button" style="margin-left:5px;" class="btn btn-success pull-right" @click="create()">Create</button>-->

        <!--<button type="button" class="btn btn-danger pull-right" @click="$emit('close')">Close</button>-->


                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
    export default {

        mounted() {
            console.log('Component LocationNew mounted.');
        },

        data() {
            return {

                selectedType : '',
                price: '',
                numRooms: '',
                assocRoomTypes: [],

                locationData : {
                    name : '',
                    address : '',
                    phone : '',
                    landline : '',
                    description : '',
                    rooms : []
                }
            }
        },

        methods: {
            reindex(){
                // this.$refs.locations.index();
                this.$root.locations.index();
            },

            create() {

                //if validated... but no time
                  this.locationData.rooms = this.assocRoomTypes;

                  console.log(this.locationData);
                  axios.post('/api/locations/store', {
                      location: this.locationData
                  }).then((response) => {

                      $emit('close');

                  }).catch((error) => {
                    console.log(error);
                  });
            },

            addRoomType() {

                for(let type of this.roomTypes){
                    if(type.id === this.selectedType){

                        if(!this.alreadyAssociated(this.selectedType))
                        {
                            let modType = type;
                            let details = {};
                            details.price = this.price;
                            details.num_rooms = this.numRooms;
                            modType.details = details;
                            this.assocRoomTypes.push(modType);

                        } else {
                            alert('Room already associated');
                            return;
                        }
                    }
                }
            },

            alreadyAssociated(id){
                for (let type of this.assocRoomTypes){
                    if(type.id === id){
                        return true;
                    }
                }
                return false;
            },



            // removeAssoc(id){
            //
            //     for(let i = 0; i < this.assocRoomTypes.length-1; i++)
            //     {
            //         console.log(this.assocRoomTypes[i].id);
            //         if(this.assocRoomTypes[i].id === id){
            //             this.assocRoomTypes.splice(i,1);
            //             return;
            //         }
            //     }
            //
            // }
        },

        props: ['roomTypes','dialog']
    }
</script>
