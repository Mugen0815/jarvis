      <template>
        <div class="about">
          <h1>About</h1>
          <div id="jarvis-virtual-assistant">
            <h2>Jarvis Virtual Assistant</h2>
            <p>
              <a href="https://github.com/Mugen0815/jarvis">Source Code</a>
            </p>
            <p>
              <a href="https://hub.docker.com/r/mugen0815/jarvis">Dockerhub Repository</a>
            </p>
            <h3>Description</h3>
            <p>
              Jarvis is a virtual assistant developed using the OpenAI API. It leverages the assistant feature to execute local function calls, known as "skills", which will be executed locally, like Chat-GPT was accessing your own API.
            </p>
            <h3>Features</h3>
            <p>Jarvis allows for the easy addition of skills without requiring knowledge of the OpenAI API or Symfony. Basic PHP knowledge is sufficient. The predefined skills include:</p>
            <ul>
              <li>Filesystem operations (read, write, list, create) within the local container</li>
              <li>Reading remote files via curl_get</li>
              <li>Sending emails via SMTP</li>
              <li>Creating images via Dalle-3</li>
              <li>Accessing the Portainer API</li>
              <li>And more. To discover all the capabilities, simply ask Jarvis "what can you do?".</li>
            </ul>

            <h3>Tech Stack</h3>
            <ul>
              <li>Apache 2.4</li>
              <li>PHP 8.3</li>
              <li>Symfony 7</li>
              <li>VueJS 3</li>
              <li>Vuefinder (<a href="https://github.com/n1crack/vuefinder">https://github.com/n1crack/vuefinder</a>)</li>
              <li>Flysystem (<a href="https://github.com/thephpleague/flysystem">https://github.com/thephpleague/flysystem</a>)</li>
              <li>Composer 2.6</li>
              <li>NodeJs 21</li>
              <li>NPM 10.2</li>
              <li>Symfony CLI</li>
            </ul>

            <h3>License</h3>
            <p>Jarvis is completely free and released under the GPL3 License.</p>
            <h3>Contribution</h3>
            <p>Any help is appreciated, feel free to reach out at <a href="https://github.com/Mugen0815/jarvis/discussions">https://github.com/Mugen0815/jarvis/discussions</a></p>
          </div>
        </div>
      </template>

      <style>
      .about {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        background-color: #ffffff;
      }

      h1 {
        font-size: 24px;
        margin-bottom: 20px;
      }

      h2 {
        font-size: 20px;
        margin-bottom: 10px;
      }

      h3 {
        font-size: 18px;
        margin-bottom: 10px;
      }

      p {
        margin-bottom: 10px;
      }

      ul, ol {
        margin-bottom: 10px;
        padding-left: 20px;
      }

      code {
        background-color: #f0f0f0;
        padding: 2px 4px;
        font-family: monospace;
      }

      a {
        color: #007bff;
        text-decoration: none;
      }

      a:hover {
        text-decoration: underline;
      }
      </style>

