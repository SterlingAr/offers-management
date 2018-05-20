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
               <td class="text-xs-left">{{ props.item.value }}</td>
               <td class="text-xs-left">{{ props.item.reedems }}</td>

               <td class="justify-center layout px-0">
                   <v-btn icon class="mx-0" @click="updateCoupon(props.item)">
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






      <v-dialog v-model="addModal" persistent max-width="500px">
          <progress-bar :show="busy"></progress-bar>

          <v-card>
            <v-card-title>
               <span class="headline"><span v-if="couponModel.edit">Actualizeaza</span> <span v-else>Adauga</span> Cupon</span>
            </v-card-title>
            <v-card-text>
                <v-form v-model="couponModel.valid">
                <v-flex xs12>
                <v-text-field :rules="validationRules.codeRules" label="Nume Cupon" v-model="couponModel.code" required></v-text-field>
              </v-flex>
                <v-flex xs12>
                    <v-text-field type="number" v-model="couponModel.reedems" label="Numar folosiri" required></v-text-field>
                </v-flex>
                    <v-flex xs12>
                        <v-text-field type="number" v-model="couponModel.reduction_value" label="Reducere pe persoana" required></v-text-field>
                    </v-flex>
                </v-form>
            </v-card-text>
             <v-card-actions>
                 <v-spacer></v-spacer>
                 <v-btn color="blue darken-1" flat @click.native="addModal = false">Inchide</v-btn>
                 <v-btn :disabled="!couponModel.valid" color="blue darken-1" flat @click.native="updateCoupon">Salveaza</v-btn>
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
                busy:false,
                couponModelDefault:{
                    valid:false,
                    edit:false,
                    code:"",
                    reedems:20,
                    reduction_value: 0,
                },
                couponModel:{
                    valid:false,
                    edit:false,
                    code:"",
                    reedems:20,
                    reduction_value: 0,
                },
                validationRules: {
                    codeRules: [
                        v => !!v || 'Codul e obligatoriu',
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
                        text: 'Cupon code',
                        value: 'code'
                    },
                  { text: 'Reducere pe persoana', value: 'reduction_value' },
                  { text: 'Numar validari', value: 'reedems' },

                ],

            }
        },

        methods:{

            updateCoupon(model){
                this.couponModel={
                    code:model.code,
                    valid:false,
                    edit:true,
                    reedems: model.reedems,
                    reduction_value: model.reduction_value,

                };
                this.addModal=true;
            },

            addModalInit(){
                this.couponModel={
                    code:"",
                    valid:false,
                    edit:false,
                    reedems:20,
                    reduction_value: 0

                };
                this.addModal=true;
            },

            async addCoupon (){
                this.busy=true;

                try {
                const { data } = await   axios.post('/api/coupons',this.couponModel)
                    this.busy=false;

                    this.$store.dispatch('responseMessage', {
                        type: 'success',
                        text: 'Cupon adaugat'
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

            async fetchCoupons(){
                this.busy=false;
                try {
                    const { data } = await   axios.get('/api/coupons')
                    this.busy=false;

                    this.addModal=false;

                    this.coupons=data.coupons


                } catch (e){

                    this.$store.dispatch('responseMessage', {
                        type: 'error',
                        text: e.message
                    })
                }
            },

          clearCouponModel(){
              this.couponModel = this.couponModelDefault;
          }

        }
    }
</script>
