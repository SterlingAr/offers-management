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

                    <td class="text-xs-left">{{ props.item.id }}</td>
                    <td class="text-xs-left">{{clientName(props.item.first_name, props.item.last_name)}}</td>
                    <td class="text-xs-left">{{ props.item.email }}</td>
                    <td class="text-xs-left">{{ props.item.phone }}</td>
                    <td class="text-xs-left">{{ props.item.total_person_number}}</td>
                    <td class="text-xs-left">{{ props.item.payment_status}}</td>
                    <td class="text-xs-left">{{ props.item.total_amount}}</td>
                    <td class="justify-center layout px-0">
                        <v-btn icon class="mx-0" @click="editSale(props.item)">
                            <v-icon color="teal">edit</v-icon>
                        </v-btn>
                        <v-btn icon class="mx-0" @click="deleteSaleDialog(props.item)">
                            <v-icon color="pink">delete</v-icon>
                        </v-btn>
                    </td>
                </template>
                <v-alert slot="no-results" :value="true" color="error" icon="warning">
                    Cautarea pentru "{{ search }}" nu are rezultate.
                </v-alert>
            </v-data-table>
        </v-card>

        <!-- Confirm delete sale dialog -->
        <v-dialog v-model="deletingSale" max-width="290">
            <v-card>
                <v-card-title class="headline">Stergere vanzare?</v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click="deletingSale = false">Inchide</v-btn>
                    <v-btn flat large color="error" @click="deleteSale">Da, sterge</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <new-sale-dialog :reindex.sync="reindex" :dialog.sync="dialog"></new-sale-dialog>
        <edit-sale-dialog :selected-offer.sync="selectedOffer" :sale-model.sync="saleModel" :allocated-rooms.sync="allocatedRooms" :coupon.sync="coupon" :coupon-reduction.sync="couponReduction" :coupon-code.sync="couponCode" :reindex.sync="reindex" :edit-dialog.sync="editDialog"></edit-sale-dialog>
    </div>
</template>

<script>
  import SalesNew from './SalesNew'
  import SalesEdit from './SalesEdit'
  import { mapState } from 'vuex'
  import axios from 'axios'
  import debounce from '../../tools/debounce/debounce.js'

  export default {
    name:"Vanzari",
    metaInfo () {
      return { title: "Vanzari" }
    },
    mounted() {
        this.indexTable();

    },

    data(){
      return {
        busy: false,
        dialog:false,
        editDialog: false,
        deletingSale: false,
        saleForDelete: {},
        selectedOffer: {},

        //props for edit
        saleModel: {
          first_name: "",
          last_name: "",
          email: "",
          phone: "",
          offer_id: "",
          total_person_number: 0,
          payment_status: "notpaid",
          coupon_code: "",
          total_amount: 0.00,
          options: {
            valid: false,
          }
        },
        allocatedRooms: [],
        coupon: {},
        couponCode: '',
        couponReduction: '',
        search: '',
        reindex : false,
        delay: 4000,

        salesHeaders: [
          {text: 'ID', value: 'id'},
          {text: 'Client', value: 'client_name'},
          {text: 'Email', value: 'email'},
          {text: 'Telefon', value: 'phone'},
          {text: 'Total persoane', value:'total_person_number'},
          {text: 'Status plata', value:'payment_status'},
          {text: 'Suma totala', value:'total_amount'},
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

      async deleteSale(){
        this.busy = true;
        let index = this.sales.indexOf(this.saleForDelete);
        try {
          const {data} = await axios.delete('/api/sales/delete/'+ this.saleForDelete.id);

          this.sales.splice(index,1);
          this.deletingSale = false;

        } catch(e) {
          console.log(e);
        }
        this.busy = false;

      },

      deleteSaleDialog(sale){
        this.saleForDelete = sale;
        this.deletingSale = true;

      },

      async editSale(sale){
        this.saleModel.id = sale.id;
        this.saleModel.last_name = sale.last_name;
        this.saleModel.first_name = sale.first_name;
        this.saleModel.email = sale.email;
        this.saleModel.phone = sale.phone;
        this.saleModel.total_person_number = sale.total_person_number;
        this.saleModel.payment_status = sale.payment_status;
        this.saleModel.coupon_code = sale.coupon_code;
        this.saleModel.total_amount = sale.total_amount;

        try {
          const {data} = await axios.get('/api/sales/' + sale.id);
          this.allocatedRooms = data.allocatedRooms;
          this.selectedOffer = data.offer;
          this.saleModel.offer_id = data.offer.id;

          if(data.coupon !== undefined && data.coupon !== null){

            this.coupon = data.coupon;
            this.couponReduction = data.coupon.reduction_value;
            this.couponCode = data.coupon.code;
            this.saleModel.coupon_id = data.coupon.id;
          }

          console.log(data);
        } catch (e) {
          console.log(e);
        }

        this.editDialog=true;
      },


      addModalInit(){
        this.dialog=true;
      },

      clientName(firstName,lastName){
        return firstName + ' ' + lastName;
      }

    },

    watch : {
      reindex: function (val){
        val && this.indexTable();
      }
    },


    computed:{
      // ...mapState({
      //   offers: state => state.offers.offers //?
      // })
    },

    components: {
      'new-sale-dialog' : SalesNew,
      'edit-sale-dialog' : SalesEdit,
    },

    directives: {debounce},


  }
</script>
