# Jarvis Virtual Assistand
[Source Code](https://github.com/Mugen0815/jarvis)

![Build Status](https://github.com/Mugen0815/jarvis/actions/workflows/docker-image.yml/badge.svg)

[Dockerhub Repository](https://hub.docker.com/r/mugen0815/jarvis)


# WOP pre-alpha of Jarvis with local LLM using llama.cpp and codellama

## Description
Jarvis is a virtual assistant developed using the OpenAI API. It leverages the assistant feature to execute local function calls, known as "skills"", which will be executed locally, like Chat-GPT was accessing ur own API. Its focused on easy local code-execution for PHP-devs.

## Features
Jarvis allows for the easy addition of skills without requiring knowledge of the OpenAI API or Symfony. Basic PHP knowledge is sufficient. The predefined skills include:
- Filesystem operations (read, write, list, create) within the local container
- Reading remote files via curl_get
- Sending emails via SMTP
- Creating images via Dalle-3
- Accessing the Portainer API
- And more. To discover all the capabilities, simply ask Jarvis "what can you do?".

## Prerequisites
- Linux or WSL
- Docker version 18.06 or later
- OpenAI API key (free, registration required, usage may incur costs)

## Tech Stack
- Apache 2.4
- PHP 8.3
- Symfony 7
- VueJS 3
- Vuefinder (https://github.com/n1crack/vuefinder)
- Flysystem (https://github.com/thephpleague/flysystem)
- Composer 2.6
- NodeJs 21
- NPM 10.2
- Symfony CLI

## Local Environment Setup
1. Clone the repository: `git clone git@github.com:Mugen0815/jarvis.git`
2. Navigate to the project directory: `cd jarvis`
3. If a `.env` file doesn't exist, create one in the root directory. Set your API key in the `.env` file like so: `APIKEY=yourapikey`
4. Start the Docker containers: `docker compose up -d`
5. Access the application at: `http://localhost:8080`

## Building the Docker Image only
1. Clone the repository: `git clone git@github.com:Mugen0815/jarvis.git`
2. Navigate to the project directory: `cd jarvis`
3. Build the Docker containers: `docker build .`

## Quick-Start via Dockerhub
- In shell (linux or wsl) run: `docker run -e APIKEY='yourapikey' -p 8080:80 mugen0815/jarvis`

## ENV-VARS
- APIKEY (OpenAI-API-key)
- MODEL (defaults to gpt-3.5-turbo, can be set to other models, if available to you, like  gpt-4-1106-preview)
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- PORTAINER_URL (eg https://portainer.mydomain.com or http://localhost:9000)
- PORTAINER_USERNAME
- PORTAINER_PASSWORD

## How to add skills
1. add a class that implements Mugen0815\OpenAIAssistant\SkillsInterface (like those in src/app/src/*Skills.php)
2. add it to the assistant with setSkills()
   in this case in src/app/src/Controllers/ApiController.php:getAssistant()
3. delete content of src/app/ai_content/assistantId.txt (so a new assistant is created on openai-side)
4. (optional) check ur assistants on https://platform.openai.com/assistants and check, if ur new function was properly imported
   you can delete your old assistants, they are not needed for accessing old threads

## Where are files stored?
By Default, jarvis can only write to /usr/src/app/ai_content and /usr/src/app/public/uploads, 
but you can configure this manually, by using the setAllowedDirectories()-method of the FilesystemSkills in ApiController
so you can include ur own mounts

## License
Jarvis is completely free and released under the GPL3 License.

## Contribution
Any help is appreciated, feel free to reach out at https://github.com/Mugen0815/jarvis/discussions

## Known issues
- full-screen-mode for vuefinder in home-view doesnt work in mobile
- image-icons in vuefinder dont refresh properly

## Changelog
- 0.8.0 
   - Replaced frontend with vuejs-version
   - Added filebrowser via vuevinder
   - Fixed bug in dalle-image-generation  
- 0.7.6 Added sizes to dalle-image-generation
- 0.7.6 Minor fixes
- 0.7.5 Minor fixes
- 0.7.4 Initial release  

## Todo
- check for multiple answers
- rework laout
- replace ajax with websockets
- add tests
- add voice-input and output
- add fileuploads, vision, 
- rework dockerfile (size, healthcheck)
- add support for gemini
- add retrieval, finetuning
- rework existing skills
- add more skills
- replace internal openai-client with official one
