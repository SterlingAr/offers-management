<template>
    <div class="container">
        <v-btn small  @click="newOffer()" fab dark color="indigo">
            <v-icon dark>add</v-icon>
        </v-btn>
        <v-card>
            <v-alert class="animated bounceInRight" type="success"  dismissible>
                Offer created successfully.
            </v-alert>

            <v-alert class="animated bounceInRight" type="success"  dismissible>
                Offer updated successfully.
            </v-alert>
            <v-card-title>
                Offers
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
                    :items="offers"
                    :search="search"
            >
                <template slot="items" slot-scope="props">

                    <td>{{ props.item.id }}</td>
                    <td class="text-xs-left">{{ props.item.title }}</td>
                    <td class="text-xs-left">{{ props.item.description }}</td>
                    <td class="justify-center layout px-0">
                        <v-btn icon class="mx-0" @click="editOffer(props.item)">
                            <v-icon color="teal">edit</v-icon>
                        </v-btn>
                        <v-btn icon class="mx-0" @click="deleteOffer(props.item)">
                            <v-icon color="pink">delete</v-icon>
                        </v-btn>
                    </td>
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </v-data-table>
        </v-card>
        <offer-edit :dialog.sync="dialogEdit"></offer-edit>
        <offer-new :dialog.sync="dialogNew"></offer-new>
    </div>
</template>


<script>
    import OfferNew from './OfferNew.vue'
    import OfferEdit from './OfferEdit.vue'
    import axios from 'axios'
    import debounce from '../../tools/debounce/debounce.js'

    export default {
        mounted() {
            console.log('Component Offers mounted.')
            this.indexTable();
        },

        data() {
            return {

              search: '',
              delay: 5000,
              headers: [
                { text: 'ID', align: 'left', value: 'id'},
                { text: 'Offer title', value: 'title'},
                { text: 'Description', value: 'description' },
              ],

              offers: [

              ],

              dialogEdit: false,
              dialogNew: false,


            }
        },

        methods: {

          indexTable() { //query locations using the search parameters
            axios.post('/api/offers/search', { query: this.search}).then((response) => {
              console.log(response);
              this.offers = response.data.offers;
            }).catch((error) => {
                console.log(error);
            });
          },

          newOffer() {
            this.dialogNew = true;
          },

          editOffer(offer) {
            this.dialogEdit = true;
          },

          deleteOffer(offer) {
            const index = this.offers.indexOf(offer)
            confirm('Are you sure you want to delete this item?') &&

            axios.delete('/api/offers/delete/' + offer.id).then((response) => {
              console.log(response);
              this.offers.splice(index, 1);

            }).catch((error) => {
                console.log(error);
            });

          }

        },

        computed: {
          // formTitle () {
          //   return this.editedIndex === -1 ? 'New Offer' : 'Edit Offer'
          // }
        },

        components: {
            'offer-edit' : OfferEdit,
            'offer-new' : OfferNew
        },

        directives: {debounce},


    }
</script>
