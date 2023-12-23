<template>
    <section>            
        <header class="msger-header">
            <div class="msger-header-title">
                {{ title }}
            </div>
            <div id="threads">
                <form id="threadsform">
                    <div class="thread-controls">
                        <label for="threadlist">Thread:</label>
                        <select id="threadlist"  @change="setThread()">
                            <option v-for="threadId in threads">
                                {{ threadId }}
                            </option>
                        </select>
                        <button id="create" class="msger-create-btn button" @click="(event) => createThread(event)">Create</button>
                        <button id="delete" class="msger-delete-btn button" @click="(event) => deleteThread(event)">Delete</button>
                    </div>
                </form>
            </div>
        </header>

        <main class="msger-chat">
            <htmlmessage v-if = "messages.length > 0" v-for = "message in messages" :message="message" />
            <loader v-if = "waitingForResponse" />
        </main>
        
        <form class="msger-inputarea" @submit.prevent="sendMessage()">
            <input type="text" class="msger-input" placeholder="Enter your message..." />
            <button type="submit" class="msger-send-btn button">Send</button>
        </form>
    </section>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import htmlmessage from '@/components/Message.vue';
import loader from '@/components/Loader.vue';
import $ from "jquery";

 
const threads = ref([]);
const activeThread = ref(0);
const messages = ref([]);
const waitingForResponse = ref(false);
const title = ref("Jarvis offline");
const selectortextInput = ".msger-input";
const selectorThreadList = "#threadlist";
const selectorChat = ".msger-chat";


onMounted(() => {
    getThreads();
})

watch(messages, () => {
    scrollDownChat();
});

async function getThreads() { 
  fetch('/api/threads')
    .then(response => response.json())
    .then(data => {
      threads.value = [];
      data.reverse();
      data.forEach(thread => {
        threads.value.push(thread);
      });
      title.value = "Jarvis online";
      activeThread.value = data[0];
      getMessages();
    });
};

async function getMessages() {
    fetch(`/api/messages?thread=${activeThread.value}`)
    .then(response => response.json())
    .then(data => {
        data.reverse();
        messages.value = [];
        data.forEach(message => {
          messages.value.push(message);
      });
      scrollDownChat();
    });
};

async function sendMessage() {
    var message = document.querySelector(selectortextInput).value;
    var thread = document.getElementById("threadlist").value;
    if (message === '') {
        return;
    }
    document.querySelector(selectortextInput).value = '';
    let tmpmessage = createTemporaryMessage(message, 'user');
    messages.value.push(tmpmessage);
    scrollDownChat();
    waitingForResponse.value = true;
    $.ajax({
        method: "POST",
            url: '/api/chat',
            data: {
                message: message, thread: thread
            },

            
        success: function(data) {
            waitingForResponse.value = false;
            getMessages();
        }
    });
}

function createThread(event) {
    event.preventDefault();
  fetch(`/api/newthread`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      getThreads();
    });
}

function deleteThread(event) {
    event.preventDefault();
    var threadId = document.querySelector(selectorThreadList).value;
    fetch(`/api/deletethread?thread=${threadId}`, {
      method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
      getThreads();
    });
}

function setThread() {
    activeThread.value = document.querySelector(selectorThreadList).value;
    getMessages();
}

function createTemporaryMessage(text, role) {
    var message = {
        "role": role,
        "content": [
            {
                "type": "text",
                "text": {
                    "value": text
                }
            }
        ],
        "created_at": Math.floor(Date.now() / 1000)
    };

    return message;
    
}

function scrollDownChat() {
    nextTick(() => {
        var chat = document.querySelector(selectorChat);
        chat.scrollTop = chat.scrollHeight;
    });
}

</script>

<style>
.msger-header {
    align-items: center;
    background-color: #f0f0f0;
    border-bottom: 1px solid #e6e6e6;
    display: flex;
    height: 60px;
    justify-content: space-between;
    padding: 10px 20px;
}

.msger-header-title {
    color: #333;
    font-size: 18px;
    font-weight: 500;
}

.msger-header-options {
    color: #999;
    cursor: pointer;
    font-size: 24px;
}

.msger-chat {
    background-color: #fff;
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px;
}

.msger-inputarea {
    background-color: #f5f5f5;
    display: flex;
    padding: 10px 20px;
}

.msger-input {
    background-color: #fff;
    border: none;
    border-radius: 20px;
    flex-grow: 1;
    font-size: 16px;
    outline: none;
    padding: 10px 15px;
    height:100px;
}

.msger-send-btn:hover {
    background-color: #292;
}

.msger-create-btn:hover {
  --key-text-color: #fff;
  --docsearch-key-gradient: linear-gradient(-225deg,#579ffb,#579ffb);
  --docsearch-key-shadow: inset 0 -2px 0 0 #579ffb,inset 0 0 1px 1px #fff,0 1px 2px 1px rgba(30,35,90,0.4);
}

.msger-delete-btn:hover {
  --key-text-color: #fff;
  --docsearch-key-gradient: linear-gradient(-225deg,#ff5f5f,#ff5f5f);
  --docsearch-key-shadow: inset 0 -2px 0 0 #ff5f5f,inset 0 0 1px 1px #fff,0 1px 2px 1px rgba(30,35,90,0.4);
}

.msger-send-btn:hover {
  --key-text-color: #fff;
  --docsearch-key-gradient: linear-gradient(-225deg,#15dc15,#15dc15);
  --docsearch-key-shadow: inset 0 -2px 0 0 #579ffb,inset 0 0 1px 1px #fff,0 1px 2px 1px rgba(30,35,90,0.4);
}

@media (min-width: 20em) {
    .msger-header-title {
        
         font-size: 1.125rem; /* 18px */
        color: green;
    }
    .msger-chat {
        height: 60vh;
    }
    .msger-inputarea {
        min-height: 60px;
    }
  .msger-input {
      height:100px;
  }
  .msger-send-btn {
      margin: 13px;
  }
}

@media (min-width: 32em) {
    .msger-chat {
        height: 60vh;
    }
    .msger-inputarea {
        min-height: 60px;
    }
}

@media (max-width: 600px) { /* 600px */
    body {
        font-size: 1.125rem; /* 18px */
    }
}

@media (min-width: 96em) {
    .msger-header-title {
        font-size: 18px;
        color: green;
    }
    .msger-chat {
        height: 60vh;
    }
    .msger-inputarea {
        min-height: 100px;
    }

    
    .msger-input {
        height:50px;
    }
    
.msger-send-btn {
    margin: 0px 0px 0px 20px;
}
    /* Add more responsive styles as needed */

    select {
        height: 2.5em;
        text-align: center;
        padding: 0.6em;
        margin: 0 0.6em;
      width: 60%;
    }
}

</style>