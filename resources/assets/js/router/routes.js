export default ({ authGuard, guestGuard }) => [
  { path: '/', name: 'welcome', component: require('~/pages/welcome.vue') },

  // Authenticated routes.
  ...authGuard([
    { path: '/admin/home', name: 'home', component: require('~/pages/home.vue') },
    { path: '/admin/locations', name: 'locations', component: require('~/pages/location/Locations.vue') },
    { path: '/admin/offers', name: 'offers', component: require('~/pages/offer/Offers.vue') },
    { path: '/admin/coupons', name: 'coupons', component: require('~/pages/sales/coupons.vue') },
    { path: '/admin/vanzari', name: 'vanzari', component: require('~/pages/sales/Sales.vue') },
    { path: '/admin/settings', component: require('~/pages/settings/index.vue'),
      children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: require('~/pages/settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: require('~/pages/settings/password.vue') }
      ] }
  ]),
  // Guest routes.
  ...guestGuard([
    { path: '/admin/login', name: 'login', component: require('~/pages/auth/login.vue') },
    { path: '/admin/register', name: 'register', component: require('~/pages/auth/register.vue') },
    { path: '/admin/password/reset', name: 'password.request', component: require('~/pages/auth/password/email.vue') },
    { path: '/password/reset/:token', name: 'password.reset', component: require('~/pages/auth/password/reset.vue') }
  ]),

  { path: '*', component: require('~/pages/errors/404.vue') }
]
