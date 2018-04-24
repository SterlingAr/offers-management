<template>
    <div class="container">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <!--<progress-bar :show="busy"></progress-bar>-->
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Noua oferta</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createOffer">Creaza</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>
                    </v-menu>
                </v-toolbar>
                <v-card-text>
                    <v-container grid-list-md text-xs-center>
                        <v-layout row wrap>

                            <v-flex md6 xs12 >
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>perm_identity</v-icon>
                                        <v-toolbar-title> Detalii oferta</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                    </v-toolbar>
                                    <v-card-text>
                                    </v-card-text>
                                </v-card>
                            </v-flex>


                            <v-flex md6 xs12 >
                                <v-card light>
                                    <v-toolbar color="indigo" dark>
                                        <v-icon>event</v-icon>
                                        <v-toolbar-title> Listare date</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon dark right @click="addDateDialog">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <!--<v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>-->
                                        <v-data-table
                                                :headers="datesTableHeaders"
                                                :items="dates"
                                                class="elevation-1"
                                        >
                                            <template slot="items" slot-scope="props">
                                                <td class="text-xs-left">{{ props.item.id }}</td>
                                                <td class="text-xs-left">{{ props.item.start_date }}</td>
                                                <td class="text-xs-left">{{ props.item.end_date}}</td>
                                                <td class="text-xs-right">
                                                    <v-btn color="indigo" dark>
                                                        <v-badge color="blue" rigth>
                                                            Locatii
                                                            <span class="right" slot="badge">{{props.item.locations.length}}</span>
                                                        </v-badge>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0" @click="dateModel.edit=true">
                                                        <v-icon color="teal">edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="mx-0">
                                                        <v-icon color="pink">delete</v-icon>
                                                    </v-btn>
                                                </td>
                                            </template>
                                        </v-data-table>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-dialog v-model="addingDate" max-width="500px">
            <v-card>
                <v-card-title>
                    <span>Adauga o data noua</span>
                    <span v-if="dateModel.options.edit">Actualizeaza data</span>
                </v-card-title>
                <v-card-text>
                    <v-form  v-model="dateModel.options.valid">
                        <v-flex xs12>
                            <v-menu
                                    ref="startDateMenu"
                                    lazy
                                    :close-on-content-click="false"
                                    v-model="startDateMenu"
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                    :nudge-right="40"
                                    min-width="290px"
                                    :return-value.sync="dateModel.startDate"
                            >
                                <v-text-field
                                        slot="activator"
                                        label="Data inceput"
                                        v-model="dateModel.startDate"
                                        prepend-icon="event"
                                        :rules="validationRules.commonDateRules"
                                        readonly
                                        required
                                ></v-text-field>
                                <v-date-picker v-model="dateModel.startDate" @input="$refs.startDateMenu.save(dateModel.startDate)"></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs12>
                                <v-menu
                                        ref="endDateMenu"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="endDateMenu"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        min-width="290px"
                                        :return-value.sync="dateModel.endDate"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Data sfarsit"
                                            v-model="dateModel.endDate"
                                            prepend-icon="event"
                                            :rules="validationRules.commonDateRules"
                                            readonly
                                            required
                                    ></v-text-field>
                                    <v-date-picker v-model="dateModel.endDate" @input="$refs.endDateMenu.save(dateModel.endDate)"></v-date-picker>
                                </v-menu>
                        </v-flex>
                    </v-form>

                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" flat @click.stop="addingDate = false">Close</v-btn>
                    <v-btn  :disabled="!dateModel.options.valid" color="blue darken-1" flat @click.native="addDate">Salveaza</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
  export default {


    data() {
      return {
        busy: false,
        busyDateTable: false,
        addingDate:false,
        dateModelDefault: {
          startDate: null,
          endDate:null,
          options: {
            edit:  false,
            valid: false,
          }
        },
        dateModel: {
          startDate: null,
          endDate:null,
          options: {
            edit:  false,
            valid: false,
          }
        },
        startDateMenu: false,
        endDateMenu:null,
        validationRules: {
          commonDateRules: [
            v => !!v || 'Data este obligatorie',
          ],
          endDateRules: [
            v => !!v || 'Data este obligatorie',
          ]
        },
        datesTableHeaders: [
          {text:'ID', value:'id'},
          {text:'Data inceput', value:'start_date'},
          {text:'Data sfarsit', value:'end_date'}
        ],

        falseId: 0, //For new offers only.
        dates: [
          {
            id:1,
            start_date: '20-05-2018',
            end_date:   '20-06-2018',
            locations: [
              {
                id: 1,
                name: 'Location1',
                rooms: [
                  {

                  }
                ]
              },
              {
                id: 2,
                name: 'Location2',
              },
              {
                id:3,
                name: 'Location3',
              }
            ]
          },
          {
            id: 2,
            start_date: '20-05-2018',
            end_date:   '20-06-2018',
            locations: [
              {

              }
            ]
          }
        ]

      }
    },

    methods: {

      createOffer()  {

      },


      addDateDialog(){
        this.dateModel = JSON.parse(JSON.stringify(this.dateModelDefault));
        this.addingDate = true;
      }

    },

    props: ['dialog']

  }
</script>