import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        newEvent: '',
        generate: false,
        idNewXMP: [],
    },
    mutations: {
        updateEvent: ( state, payload ) => {
            state.newEvent = 'payload';
        }
    },
    actions: {},
    modules: {},
    getters: {
        getEvent: state => {
            return state.newEvent
        },
        getGenerate: state => {
            return state.generate
        },
        getIdNewXMP: state => {
            return state.idNewXMP
        },
    }
})

export default store
