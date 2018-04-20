<template          @before-open="beforeOpen">
    <div class="container">

        <p class="h1">{{location.name}}</p>



        <table class="table">
            <thead>

                <th>Room type</th>
                <th>Number of rooms</th>
            </thead>
            <tbody>
                <tr v-for="room in location.rooms">
                    <td> {{room.type}}</td>
                    <td>{{room.details.num_rooms}}</td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-danger pull-right"  @click="$emit('close')">Close</button>
    </div>
</template>


<script>
    export default {
        mounted() {
            console.log('Component Location mounted.')
        },

        methods: {
            beforeOpen() {
                console.log('Before opening !');
            }
        },

        props: ['location']
    }
</script>
