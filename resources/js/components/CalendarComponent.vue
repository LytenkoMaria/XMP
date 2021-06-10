<template>
    <div class="container">
        <b-button class="booking" v-b-modal.modal-1>Booking</b-button>
        <div>
            <b-modal id="modal-1" ok-only :okTitle="buttonName" @hidden="closeModal" :title="modalLable" @ok="createEvent()">
                <form>
                    <div class="col mt-4">
                        <div class="form-row">
                            <div class="form-group col-md-3 ml-4 mt-1 text-center text-monospace">
                                <label>Select date</label>
                            </div>
                            <div class="form-group col-md-7">
                                <input v-model="eventData" type="date" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3 ml-4 mt-1 text-center text-monospace">
                                <label>Title</label>
                            </div>
                            <div class="form-group col-md-7">
                                <input v-model="eventTitle" id="event-title" class="form-control" type="text" required>
                            </div>
                            <div class="form-group col-md-3 ml-4 mt-1 text-center text-monospace">
                                <label>Your XMP</label>
                            </div>
                            <div class="form-group col-md-7">
                                <select v-model="eventXMP" class="form-control">
                                    <option :value="option.id" v-for="option in userXMP">
                                        {{ option.label_file }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-5 ml-4 mt-1">
                                <button type="button" v-on:click.prevent="createXMP()" class="mt-3 btn btn-outline-primary">Create new XMP</button>
                            </div>
                            <div class="form-group mt-4">
                                <label>{{ nameNewXMP }}</label>
                            </div>
                        </div>
                    </div>
                </form>
            </b-modal>
        </div>
        <FullCalendar :options="calendarOptions"/>
       </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

export default {
    components: {
        FullCalendar // make the <FullCalendar> tag available
    },
    data() {
        return {
            buttonName: 'Ok',
            nameNewXMP: null,
            eventXMP: null,
            eventData: null,
            eventTitle: null,
            modalLable: 'Booking',
            userXMP: [],
            idEvent: null,
            eventDataMas: {},
            calendarOptions: {
                plugins: [ dayGridPlugin, interactionPlugin ],
                initialView: 'dayGridMonth',
                dateClick: this.handleDateClick,
                eventClick: this.eventClick,
                events: [],
            }
        }
    },
    methods: {
        handleDateClick: function(arg) {
            this.eventData = arg.dateStr;
            this.$bvModal.show('modal-1')
        },
        openModal: function () {
            var modal = window.location.search.substring(1);
            var returnModal = modal.split('=')[1];
            if(returnModal) {
                if (localStorage.getItem('name_new_xmp')!= "null") {
                    this.nameNewXMP = localStorage.getItem('name_new_xmp');
                }
                this.eventData = localStorage.getItem('data');
                this.eventTitle = localStorage.getItem('title');
                console.log(document.getElementById('modal-1'), 'here2')
                this.$bvModal.show('modal-1')
            }
        },
        closeModal: function() {
            this.eventData = null;
            this.eventTitle = null;
            this.modalLable = 'Booking';
            this.buttonName = 'Ok';
            this.$bvModal.hide('modal-1');
            this.eventXMP = localStorage.getItem('id_new_xmp');
            this.cleansing();
        },
        eventClick: function(info) {
            this.idEvent = info.event.id;
                this.$bvModal.hide('modal-1');
                this.cleansing();
                this.getEventXMP(this.idEvent);
            this.modalLable = 'Edit event';
            this.buttonName = 'Change';
            this.eventData = info.event.startStr;
            this.eventTitle = info.event.title;
            localStorage.setItem('generate', false);
            localStorage.setItem('data', info.event.startStr);
            localStorage.setItem('title', info.event.title);
            this.$bvModal.show('modal-1');
            console.log(info,info.event.startStr,info.event.id,info.event.title);
        },
        createEvent: function(e) {
            if (this.eventXMP == "null"){this.eventXMP = null;}
            console.log(this.eventXMP,'bbbbbbbbbbbbbbb');
            this.eventDataMas = {'data': this.eventData, 'title': this.eventTitle, 'id_xmp': this.eventXMP, 'id': this.idEvent};
            if (this.buttonName == 'Ok'){
            axios.post('/booking/photosession',this.eventDataMas )
                .then(
                    (response) => {
                        this.$bvModal.hide('modal-1');
                        this.cleansing();
                        this.getEvent();
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
            }
            if (this.buttonName == 'Change') {
                console.log(this.eventXMP, 'this.eventXMP')
                    axios.post('/change/booking/photosession', this.eventDataMas)
                    .then(
                        (response) => {
                            this.$bvModal.hide('modal-1');
                            this.cleansing();
                            this.getEvent();
                        },
                        (error) => {
                            console.log(error.message);
                        }
                    )
            }
        },
        cleansing: function() {
            localStorage.setItem('generate', false);
            localStorage.setItem('data', null);
            localStorage.setItem('title', null);
            localStorage.setItem('name_new_xmp', null);
            localStorage.setItem('id_new_xmp', null);
        },
        getEvent: function() {
              axios.get('/booking/get/photosession')
                .then(
                    (response) => {
                        this.calendarOptions.events = response.data.photosessionEvent;
                        console.log(response.data.photosessionEvent,'vv');
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        createXMP: function() {
            localStorage.setItem('data', this.eventData);
            localStorage.setItem('title', this.eventTitle);
            localStorage.setItem('generate', true);
            window.location.href = '/dnd';
        },
        getUserXMP: function() {
            axios.get('/get/user/new/xmp')
                .then(
                    (response) => {
                        this.userXMP = response.data.getUserXMP;
                        //this.eventXMP = response.data;
                       console.log(response.data,'fff');
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        getEventXMP: function(id) {
            axios.post('/get/user/chose/xmp', {'id' : id} )
                .then(
                    (response) => {
                        this.eventXMP = response.data.choseXMP;
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        }
    },
    mounted: function(){
        this.$nextTick(this.openModal());
        $('.fc-toolbar-title').addClass('center');
    },
    created(){
        this.getEvent();
        this.getUserXMP();
    }

}
</script>

