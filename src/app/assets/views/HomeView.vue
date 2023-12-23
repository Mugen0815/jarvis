<script setup>
import Messenger from '@/components/Messenger.vue'
import { ref } from 'vue';

const showFileBrowser = ref(false);
const dark = ref(true);

function toggleFileBrowser() {
  showFileBrowser.value = !showFileBrowser.value;
}
</script>

<template>
 <div id="innerapp">
    <div id="messenger">
      <Messenger msg="hallo jarvis" />
      <button id="toggleFilebrowser" class="button" @click="toggleFileBrowser">File-Browser</button>
    </div>

    <transition name="slide">
      <div id="filebrowser" v-show="showFileBrowser">
        <vue-finder id='vuefinder_home' url="/vuefinder" :dark="dark"></vue-finder>
      </div>
    </transition>
  </div>
</template>

<style>
button#toggleFilebrowser:hover {
  --key-text-color: #fff;
  --docsearch-key-gradient: linear-gradient(-225deg,#d8e12d,#d8e12d);
  --docsearch-key-shadow: inset 0 -2px 0 0 #d8e12d,inset 0 0 1px 1px #fff,0 1px 2px 1px rgba(30,35,90,0.4);
}

button#toggleFilebrowser {
  min-width:100px;float:right;
}

div#innerapp {
    display: flex;
    flex-direction: row;
    justify-content: center;
    width: 100%;
}

div#filebrowser {
    position: relative;
    /* Rest of your styles... */
}

div.relative.flex-auto.flex.flex-col.overflow-hidden {
  min-height: 450px;
}

@media (min-width: 20em) {
    div#messenger {
        width: 90%;
        max-width: 100%;
        min-height: 85vh;
        height:100%;
    }
}

@media (min-width: 32em) {
  div#messenger {
        width: 90%;
        max-width: 100%;
        min-height: 85vh;
    }
}

@media (max-width: 600px) {
    body {
        font-size: 1.125rem; 
    }
}

@media (min-width: 96em) {
    div#messenger {
        width: 50%;
        max-width: 50%;
        min-height: 85vh;
    }
}
/* Styles for screens smaller than 600px */
@media (max-width: 600px) {
    div#app {
        flex-direction: column;
    }

    div#filebrowser {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
}

</style>