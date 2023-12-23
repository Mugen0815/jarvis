import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/jarvis-about',
      name: 'about',
      component: () => import('../views/AboutView.vue')
    },
    {
      path: '/jarvis-messenger',
      name: 'messenger',
      component: () => import('../components/Messenger.vue')
    },
    { 
      path: '/jarvis-filebrowser', 
      component: { template: '<vue-finder id="vuefinder_component" url="/vuefinder" dark=true></vue-finder>' } 
    },

  ]
})

export default router
