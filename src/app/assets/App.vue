<template>
  <div id="innerapp">
    <RouterView />
  </div>

  <div id="router">
    <router-link to="/">Home</router-link> - 
    <router-link to="/jarvis-messenger"> Messenger</router-link> - 
    <router-link to="/jarvis-filebrowser">Filebrowser</router-link> - 
    <router-link to="/jarvis-about">About</router-link>
  </div>
</template>

<script setup>
</script>

<style>
div#innerapp {
    display: flex;
    flex-direction: row;
    justify-content: center;
    width: 100%;
}

div.relative.flex-auto.flex.flex-col.overflow-hidden {
  min-height: 450px;
}

div#router {
  text-align: center;
}

@media (max-width: 600px) {
    body {
        font-size: 1.125rem; 
    }
}

/* Styles for screens smaller than 600px */
@media (max-width: 600px) {
    div#app {
        flex-direction: column;
    }

}
</style>