<template>
    <div class="container">
        <progress-bar :show="busy"></progress-bar>
        <v-btn fab small dark color="indigo" @click="addModalInit">
            <v-icon dark>add</v-icon>
        </v-btn>
        <v-card>
            <v-card-title>
                Offers
                <v-spacer></v-spacer>
                <v-text-field
                        append-icon="search"
                        label="Cauta vanzari"
                        single-line
                        hide-details
                        @input="indexTable()"
                        v-model.lazy="search"
                        v-debounce="delay"
                ></v-text-field>
            </v-card-title>
            <v-data-table
                    :headers="salesHeaders"
                    :items="sales"
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

        <new-sale :dialog.sync="dialog" :initial-offers="initialOffers"></new-sale>
    </div>
</template>

<script>
  import SalesNew from './SalesNew'
  import { mapState } from 'vuex'
  import axios from 'axios'
  import debounce from '../../tools/debounce/debounce.js'

  export default {
    name:"Vanzari",
    metaInfo () {
      return { title: "Vanzari" }
    },
    mounted() {
      console.log('Component mounted.');
    },

    data(){
      return {
        busy: false,
        dialog:false,
        search: '',
        delay:4000,
        initialOffers: [],

        salesHeaders: [
          {text: 'ID', value: 'id'},
          {text: 'Oferta', value: 'offer_name'},
          {text: 'Client', value: 'client_name'},
          {text: 'Email', value: 'email'},
          {text: 'Phone', value: 'phone'}
        ],

        sales: []

      }
    },

    methods: {


      async indexTable(){ //query sales using the search parameters
        this.busy = true;
        try {
          const { data } = await axios.post('/api/sales/search', { query: this.search});
          console.log(data);
          this.sales = data.sales;
          this.reindex = false;
          this.busy = false;

        } catch(e) {
          console.log(e);
        }
      },


      newOffer() {
        this.dialogNew = true;
      },


      addModalInit(){
        this.dialog=true;
      },

      // async getOfferDetails(offer_id){
      //
      //   this.selectedOfferDates=[];
      //   this.selectedOfferLocations=[];
      //   this.busy=true;
      //
      //   try {
      //     const { data } = await axios.get('/api/offers/'+offer_id)
      //
      //     this.selectedOfferDetails=data.offer
      //     this.selectedOfferDates=data.offer.dates
      //     this.busy=false;
      //
      //   } catch (e) {
      //     this.busy=false;
      //   }
      // },
      //
      // async getAvailableRooms(selectedLocation){
      //   this.busy=true;
      //
      //   try {
      //     const { data } = await axios.get('/api/available-rooms',{params:{
      //         offer_id:this.sale.offer_id,
      //         location_id:selectedLocation.id,
      //         offer_date_id:this.sale.offer_date_id.id,
      //       }})
      //     this.busy=false;
      //
      //   } catch (e) {
      //     console.log(e.message);
      //     this.busy=false;
      //   }
      // },


    },

    computed:{
      ...mapState({
        offers: state => state.offers.offers //?
      })
    },

    components: {
      'new-sale' : SalesNew,
    },

    directives: {debounce},


  }
</script>
