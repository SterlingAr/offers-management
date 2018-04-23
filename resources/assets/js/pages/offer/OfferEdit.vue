<template>
    <div class="container">
        <v-dialog
                v-model="dialog"
                fullscreen
                hide-overlay
                transition="dialog-bottom-transition"
                scrollable
        >
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="$emit('update:dialog', false)" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Editeaza oferta</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="createOffer">Actualizeaza</v-btn>
                    </v-toolbar-items>
                    <v-menu bottom right offset-y>
                        <v-btn slot="activator" dark icon>
                            <v-icon>more_vert</v-icon>
                        </v-btn>
                    </v-menu>
                </v-toolbar>
                <v-card-text>


                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>


<script>
    export default {


      data() {
        return {

        }
      },

      methods: {

        createOffer()  {

        }

      },

      props: ['dialog']

    }
</script>