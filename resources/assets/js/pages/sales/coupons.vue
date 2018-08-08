<template>
   <div>

      <v-btn fab small dark color="indigo" @click="addModalInit">
         <v-icon dark>add</v-icon>
      </v-btn>



       <v-data-table
               :headers="headers"
               :items="coupons"
               :search="search"
       >
           <template slot="items" slot-scope="props">

               <td>{{ props.item.id }}</td>
               <td class="text-xs-left">{{ props.item.code }}</td>
               <td class="text-xs-left">{{ props.item.reduction_value }} €</td>
               <td class="text-xs-left">{{ props.item.redeems }}</td>

               <td class="justify-center layout px-0">
                   <v-btn icon class="mx-0" @click="updateCouponDialog(props.item)">
                       <v-icon color="teal">edit</v-icon>
                   </v-btn>
                   <v-btn icon class="mx-0" @click="deleteCouponDialog(props.item)">
                       <v-icon color="pink">delete</v-icon>
                   </v-btn>
               </td>
           </template>
           <v-alert slot="no-results" :value="true" color="error" icon="warning">
               Your search for "{{ search }}" found no results.
           </v-alert>
       </v-data-table>






      <v-dialog v-model="addModal" persistent max-width="500px">
          <progress-bar :show="busy"></progress-bar>

          <v-card>
            <v-card-title>
               <span class="headline"><span v-if="couponModel.edit">Edit</span> <span v-else>Add</span>Coupon</span>
            </v-card-title>
            <v-card-text>
                <v-form v-model="couponModel.valid">
                <v-flex xs12>
                <v-text-field :rules="validationRules.codeRules" label="Coupon name" v-model="couponModel.code" required></v-text-field>
              </v-flex>
                <v-flex xs12>
                    <v-text-field type="number" v-model.number="couponModel.redeems" label="Redeems" required></v-text-field>
                </v-flex>
                    <v-flex xs12>
                        <v-text-field type="number" v-model.number="couponModel.reduction_value" label="Discounted value per person" required></v-text-field>
                    </v-flex>
                </v-form>
            </v-card-text>
             <v-card-actions>
                 <v-spacer></v-spacer>
                 <v-btn color="blue darken-1" flat @click.native="closeCouponDialog">Close</v-btn>
                 <v-btn :disabled="!couponModel.valid" color="blue darken-1" flat @click.native="updateCoupon" v-if="couponModel.edit">Update</v-btn>
                 <v-btn :disabled="!couponModel.valid" color="blue darken-1" flat @click.native="addCoupon" v-else>Save</v-btn>
             </v-card-actions>

         </v-card>

      </v-dialog>
       <!-- Confirm delete coupon dialog -->
       <v-dialog v-model="deletingCoupon" max-width="290">
           <v-card>
               <v-card-title class="headline">Delete coupon?</v-card-title>
               <v-card-actions>
                   <v-spacer></v-spacer>
                   <v-btn color="green darken-1" flat="flat" @click="deletingCoupon = false">Close</v-btn>
                   <v-btn flat large color="error" @click="deleteCoupon">Yes, delete it</v-btn>
               </v-card-actions>
           </v-card>
       </v-dialog>
   </div>
</template>

<script>

    import axios from 'axios';

    export default {
        name: 'coupons',
        metaInfo () {
            return { title: "Cupoane discount" }
        },
        mounted(){
            this.fetchCoupons();
        },
        data(){
            return {
                coupons:[],
                search:'',
                addModal:false,
                deletingCoupon: false,
                busy:false,
                couponModelDefault:{
                    valid:false,
                    edit:false,
                    code:"",
                    redeems:20,
                    reduction_value: 0,
                },
                couponModel:{
                    valid:false,
                    edit:false,
                    code:"",
                    redeems:20,
                    reduction_value: 0,
                },
                validationRules: {
                    codeRules: [
                        v => !!v || 'Coupon name is required',
                        v => v.length <= 10 || 'Name must be less than 10 characters'
                    ],

                },

                headers: [
                    {
                        text: 'ID',
                        align: 'left',
                        value: 'id'
                    },
                    {
                        text: 'Coupon code',
                        value: 'code'
                    },
                  { text: 'Discount per person', value: 'reduction_value' },
                  { text: 'Redeems', value: 'redeems' },

                ],

            }
        },

        methods:{

          addModalInit(){
            this.couponModel={
              code:"",
              valid:false,
              edit:false,
              redeems:20,
              reduction_value: 0

            };
            this.addModal=true;
          },

          updateCouponDialog(model){
                this.couponModel = {
                    id: model.id,
                    code:model.code,
                    valid:false,
                    edit:true,
                    redeems: model.redeems,
                    reduction_value: model.reduction_value,
                };

                this.addModal=true;
            },

            deleteCouponDialog(coupon){
                this.couponModel.id = coupon.id;
                this.deletingCoupon = true;
            },

            async updateCoupon(){
              this.busy = true;
              try{
                const {data} = await axios.post('/api/coupons/update',{
                  coupon : this.couponModel,
                });
                console.log(data);
                this.fetchCoupons();
              } catch(e){
                console.log(e);
              }
              this.busy = false;
              this.clearCouponModel();

            },

            async addCoupon (){
                this.busy=true;
                try {
                const { data } = await   axios.post('/api/coupons',this.couponModel)
                    this.busy=false;

                    this.$store.dispatch('responseMessage', {
                        type: 'success',
                        text: 'Coupon saved.'
                    });
                    this.addModal=false;
                    this.fetchCoupons();

                } catch (e){

                    this.$store.dispatch('responseMessage', {
                        type: 'error',
                        text: e.message
                    })
                }
            },

          deleteCoupon: async function () {
            this.busy = true
            try {
              const {data} = await axios.delete('/api/coupons/delete/' + this.couponModel.id)
              this.clearCouponModel();
              this.fetchCoupons();
              this.deletingCoupon = false;
            } catch (e) {
              console.log(e)
            }
            this.busy = false
          },

            async fetchCoupons(){
                this.busy=false;
                try {

                    const { data } = await   axios.get('/api/coupons')
                    this.busy=false;
                    this.addModal=false;
                    this.coupons=data.coupons;

                } catch (e){
                    this.$store.dispatch('responseMessage', {
                        type: 'error',
                        text: e.message
                    })
                }
            },

          closeCouponDialog(){
            this.clearCouponModel();
            this.addModal = false;
          },

          clearCouponModel(){
              this.couponModel = JSON.parse(JSON.stringify(this.couponModelDefault));
          }

        }
    }
</script>
