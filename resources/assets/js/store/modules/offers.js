import axios from 'axios'
import * as types from '../mutation-types'
// state
export const state = {
  offers: []
}

export const getters = {
  [types.GET_OFFERS] (state) {
    return state.offers
  }
}

export const mutations = {
  [types.GET_OFFERS] (state, offers) {
    state.offers = offers
  }
}

// actions
export const actions = {
  async getOffers ({ commit }) {
    try {
      const { data } = await axios.get('/api/offers')
      // console.log(data.offers)
      commit(types.GET_OFFERS, data.offers)
    } catch (e) {
      console.log(e)
    }
  },
  async searchOffers ({ commit }, search) {
    try {
      const { data } = await axios.post('/api/offers/search', {query: search})
      console.log(data.offers)
      commit(types.GET_OFFERS, data.offers)
    } catch (e) {
      console.log(e)
    }
  }
}
