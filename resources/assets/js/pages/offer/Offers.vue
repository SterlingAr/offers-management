<template>
    <div class="container">
        <v-btn small  @click="newOffer()" fab dark color="indigo">
            <v-icon dark>add</v-icon>
        </v-btn>
        <v-card>
            <v-alert class="animated bounceInRight" type="success" v-model="newOfferSuccess" dismissible>
                Oferta creata cu succes
            </v-alert>
            <v-alert class="animated bounceInRight" type="success"  dismissible>
                Oferta creata cu succes
            </v-alert>

            <v-alert class="animated bounceInRight" type="success"  dismissible>
                Oferta actualizata cu succes
            </v-alert>
            <v-card-title>
                Offers
                <v-spacer></v-spacer>
                <v-text-field
                        append-icon="search"
                        label="Cauta oferta"
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
                   Cautarea pentru "{{ search }}" nu are rezultate.
                </v-alert>
            </v-data-table>
        </v-card>
        <offer-edit :reindex.sync="reindex" :dialog.sync="dialogEdit" :offer-model="offerModel" :dates="dates"></offer-edit>
        <offer-new :reindex.sync="reindex" :dialog.sync="dialogNew" :success-new.sync="newOfferSuccess"></offer-new>
    </div>
</template>


<script>
import OfferNew from './OfferNew.vue'
import OfferEdit from './OfferEdit.vue'
import {mapActions} from 'vuex'
import axios from 'axios'
import debounce from '../../tools/debounce/debounce.js'

  export default {

    mounted(){
       this.indexTable();
       this.getLocations();
    },
    data() {
        return {
          newOfferSuccess: false,
          search: '',
          delay: 5000,
          headers: [
            { text: 'ID', align: 'left', value: 'id'},
            { text: 'Titlu oferta', value: 'title'},
            { text: 'Descriere', value: 'description' },
          ],

          offers: [],
          offerModel: {
            id: 0,
            title: '',
            description: '',
            options: {
              valid: false
            }
          },
          dates: [],
          dialogEdit: false,
          dialogNew: false,
          reindex: false, //used to fetch again all offers when one is updated or added.

        }
    },

    methods: {

      indexTable(){ //query locations using the search parameters
        this.offers = [];
        axios.post('/api/offers/search', { query: this.search}).then((response) => {
          this.offers = response.data.offers;
          console.log(this.search)
        }).catch((error) => {
            console.log(error);
        });
        this.reindex = false;
      },

      newOffer() {
        this.dialogNew = true;
      },

      //prepare the offerModel object, fetch the dates belonging to the offer and pass them as props.
      editOffer(offer) {
        this.offerModel.id = offer.id;
        this.offerModel.title = offer.title;
        this.offerModel.description = offer.description;
        // this.editedOffer = JSON.parse(JSON.stringify(offer));
        // this.editedOffer.valid = false;
        this.fetchDates(offer.id)
        this.dialogEdit = true;
      },

      //get all dates belonging to this offer + locations.
      async fetchDates(offerId){
        this.busyDatesTable = true;
        try {
          const { data } = await axios.get('/api/offers/'+ offerId + '/dates')
          console.log(data.dates);
          this.dates = data.dates;
        } catch(e) {
          console.log(e);
        }
      },

      deleteOffer(offer) {
        const index = this.offers.indexOf(offer)
        confirm('Esti sigur ca doresti sa stergi oferta?') &&

        axios.delete('/api/offers/delete/' + offer.id).then((response) => {
          this.offers.splice(index, 1);

        }).catch((error) => {
            console.log(error);
        });
      },

      ...mapActions({
        getLocations : 'getLocations'
      }),
    },


    watch : {
      reindex: function (val){
        val && this.indexTable();
      }
    },

    components: {
        'offer-edit' : OfferEdit,
        'offer-new' : OfferNew,
    },
    directives: {debounce},
 }
</script>
