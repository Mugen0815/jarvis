<template>
  <div class="msg">
      <img v-if = "message.role=='assistant'" :src="message.role=='assistant' ? 'img/jarvis.png' : 'img/usermic.png'" alt="" class="msg-img">
      <div :class="message.role=='assistant' ? 'msg left-msg' : 'msg right-msg'">
        <div class="msg-bubble">
            <div class="msg-info">
            <div class="msg-info-name">
                {{ message.role=='assistant' ? 'Jarvis' : 'User' }}    
            </div>
            <div class="msg-info-time">
                {{ getTime(message) }}
            </div>
            </div>
            <div class="msg-text" v-html="message.content"></div>
        </div>
      </div>
      <img v-if = "message.role!=='assistant'" :src="message.role=='assistant' ? 'img/jarvis.png' : 'img/usermic.png'" alt="" class="msg-img">

  </div>
</template> 

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  message: {
    type: Object,
    required: true
  }
})

function unescapeHtml(text) {
  var map = {
    '&amp;': '&',
    '&lt;': '<',
    '&gt;': '>',
    '&quot;': '"',
    '&#039;': "'"
};
  
  return text.replace(/(&amp;|&lt;|&gt;|&quot;|&#039;)/g, function(m) { return map[m]; });
}

function getTime(message) {
    var datetime = new Date(message.created_at*1000);
    var date = datetime.toLocaleDateString();
    var time = datetime.toLocaleTimeString();
    if (date === new Date().toLocaleDateString()) {
        date = '';
    }
    var strTime = date + ' ' + time;

    return strTime;
}
</script>

<style>
.msg {
    display: flex;
    margin-bottom: 20px;
}

.msg-img {
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    border-radius: 50%;
    width: 42px;
    height: 42px;
}

.msg-bubble {
    background-color: #f5f5f5;
    border-radius: 10px;
    margin-left: 10px;
    padding: 10px 15px;
    width: fit-content;
}

.msg-info {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    width: fit-content;
}

.msg-info-name {
    color: #333;
    font-size: 16px;
    font-weight: bold;
}

.msg-info-time {
    color: #000;
    font-size: 10px;
    padding-left: 5px;
}

.msg-text {
    color: #333;
    font-size: 16px;
    width: fit-content;
}

.right-msg .msg-bubble {
    background-color: #579ffb;
    color: #fff;
    margin-left: auto;
    margin-right: 10px;
}

.right-msg .msg-img {
    margin-left: auto;
}

.msg.right-msg {
  width: 96%;
}

.left-msg .msg-bubble{
    background-color: #ccc;
    color: #333;
    margin-right: 10px;
}

.left-msg .msg-img {
    margin-right: 10px;
    margin-left: 0;
}
</style>