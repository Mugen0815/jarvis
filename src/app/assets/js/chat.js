$(document).ready(function() {
    $('#startbutton').click(function() {
        getThreads();
        //hide startbutton
        $('#startbutton').hide();
    });
    $('#create').click(function(e) {
        e.preventDefault();
        newThread();
    });
    $('#delete').click(function(e) {
        e.preventDefault();
        deleteThread();
    });
    $('#list').change(function() {
        onSelectThread();
    });
    $('.msger-inputarea').submit(function(e) {
        e.preventDefault();
        sendMessage();
    });

    fetch('/api')
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.result.status === "ok") {
                $('#startbutton').click();
            }
    });

    $('#startbutton').click(function() {
        getThreads();
    });

    $('#list').change(function() {
        onSelectThread();
    });

});

function compileTemplate(message) {
    var side = 'right';
    var name = 'You';
    if (message.role === 'assistant') {
        side = 'left';
        name = 'Jarvis';
    }
    var img = 'img/usermic.png';
    if (message.role === 'assistant') {
        img = 'img/jarvis.png';        
    }
    var datetime = new Date(message.created_at*1000);
    var date = datetime.toLocaleDateString();
    var time = datetime.toLocaleTimeString();
    if (date === new Date().toLocaleDateString()) {
        date = '';
    }
    var datetime = date + ' ' + time;

    $('.msger-chat').append($('#template').html()
    .replace('{$side}', side)
    .replace('{$img}', img)
    .replace('{$name}', name)
    .replace('{$time}',  datetime)
    .replace('{$text}', message.content[0].text.value)
    );
}

function getThreads() {
  fetch('/api/threads')
    .then(response => response.json())
    .then(data => {
      console.log(data);
      $('#list').empty();
      data.forEach(thread => {
        $('#list').append(`<option value="${thread}">${thread}</option>`);
      });
      $('#list').val(data[data.length - 1]);
      $('.msger-header-title').text('Jarvis online');
      getMessages();
    });
}

function getMessages() {
  fetch(`/api/messages?thread=` + $('#list').val() )
    .then(response => response.json())
    .then(data => {
      data.reverse();
      console.log(data);
      $('.msger-chat').empty();

      data.forEach(message => {
        compileTemplate(message);
      });

      $('.msger-chat').scrollTop($('.msger-chat')[0].scrollHeight);
    });
}
 
function sendMessage() {
    var message = $('.msger-input').val();
    var thread = $('#list').val();
    if (message === '') {
        return;
    }
    $('.msger-input').val('');
    var messageObj = {
        role: 'user',
        content: [
            {
                text: {
                    value: message
                }
            }
        ],
        created_at: new Date().getTime()/1000
    };
    compileTemplate(messageObj);
    $('.msger-chat').append($('#template').html()
    .replace('{$side}', 'left')
    .replace('{$img}', 'img/assistant.png')
    .replace('{$name}', 'Jarvis')
    .replace('{$time}',  '')
    .replace('{$text}', 'Jarvis is typing...')

    );
   // add simple blink-effect to text of last message
    $('.msger-chat').children().last().addClass('blink');
    
    $.ajax({
        method: "POST",
            url: '/api/chat',
            data: {
                message: message, thread: thread
            },
        success: function(data) {
            getMessages();
        }
    });
}

function deleteThread() {
  fetch(`/api/deletethread?thread=${$('#list').val()}`, {
      method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
      console.log(data);
      $('#list').empty();
      getThreads();
    });
}

function onSelectThread() {
    getMessages();
}

function newThread() {
  fetch(`/api/newthread`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      console.log(data);
      $('#list').empty();
      getThreads();
    });
}
