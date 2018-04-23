import axios from 'axios'
import * as types from '../mutation-types'

export const state = {
  locations: []
}

export const getters = {
  [types.GET_LOCATIONS] (state) {
    return state.locations
  }
}
export const mutations = {
  [types.GET_LOCATIONS] (state, locations) {
    state.locations = locations
  }
}

export const actions = {
  async getLocations ({ commit }) {
    try {
      const { data } = await axios.get('/api/locations')
      commit(types.GET_LOCATIONS, data.locations)
    } catch (e) {

    }
  }
}
