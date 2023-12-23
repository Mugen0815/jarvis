/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import { createApp } from "vue";
import App from "./App.vue";
import router from "./js/router.js";
import VueFinder from "vuefinder"

import './styles/app.scss';
import 'vuefinder/dist/style.css'
import './styles/theme.scss';

createApp(App).use(router).use(VueFinder).mount('#app');