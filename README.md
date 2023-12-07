# Jarvis Virtual Assistand
[Source Code](https://github.com/Mugen0815/jarvis)

![Build Status](https://github.com/Mugen0815/jarvis/actions/workflows/docker-image.yml/badge.svg)

[Dockerhub Repository](https://hub.docker.com/r/mugen0815/jarvis)

Jarvis is a virtual assistant developed using the OpenAI API. It leverages the assistant feature to execute local function calls, known as "skills"", which will be executed locally, like Chat-GPT was accessing ur own API.

## Features
Jarvis allows for the easy addition of skills without requiring knowledge of the OpenAI API or Symfony. Basic PHP knowledge is sufficient. The predefined skills include:
- Filesystem operations (read, write, list, create) within the local container
- Reading remote files via curl_get
- Sending emails via SMTP
- Creating images via Dalle-3
- Accessing the Portainer API
- And more. To discover all the capabilities, simply ask Jarvis "what can you do?".

## Prerequisites
- Linux or WSL2
- Docker version 18.06 or later
- OpenAI API key (free, registration required, usage may incur costs)

## Tech Stack
- Apache 2.4
- PHP 8.3
- Composer 2.6
- Symfony CLI
- Symfony 7 Webapp
- NodeJs 21 (not used atm)
- NPM 10.2 (not used atm)

## Local Environment Setup
1. Clone the repository: `git clone git@github.com:Mugen0815/jarvis.git`
2. Navigate to the project directory: `cd jarvis`
3. Edit `.env` to set you api-key
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

## TODO
- replace frontend with vuejs-version
- replace internal openai-client with official one
- add tests
- add voice-input and output
- add fileuploads, vision, retrieval, finetuning
- rework existing skills
- add more skills
