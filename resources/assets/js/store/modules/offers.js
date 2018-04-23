import axios from 'axios'
import * as types from '../mutation-types'
// state
export const state = {
  offers: []
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
      const { data } = await axios.get('/api/offers-list')
      commit(types.GET_OFFERS, data.offers)
    } catch (e) {
    }
  }
}
