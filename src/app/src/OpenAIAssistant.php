<?php

namespace Mugen0815\OpenAIAssistant;

use Exception;
/**
 * Class openAIAssistant
 * 
 * This class wraps calls to the OpenAI API.
 * It is used to chat with the OpenAI assistant.
 * It supports function calls, called skills in this context
 */
class OpenAIAssistant
{
    /**
     * @var string The API key used for authentication.
     */
    private string $apiKey;

    /**
     * @var string The ID of the assistant.
     */
    private string $assistantId;

    /**
     * @var string The model used by the assistant.
     */
    private string $model;

    /**
     * @var array The skills of the assistant.
     */
    private array $aSkills;

    /**
     * @var array The merged skills of the assistant.
     */
    private array $aMergedSkills;

    /**
     * @var array The log of the assistant.
     */
    private array $log;

    /**
     * @var object The logger of the assistant.
     */
    private object $logger;

    /**
     * @var string The base URL of the assistant.
     */
    private string $baseUrl;

    /**
     * @var string The log path of the assistant.
     */
    private string $logPath;

    /**
     * @var bool Whether to log to a file.
     */
    private bool $logToFile;


    /**
     * @var string The instructions for the assistant.
     */
    private string $instructions;

    private string $description;

    /**
     * Constructor for the OpenAIAssistant class.
     *
     * @param string $apiKey The API key used for authentication.
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->setModel("gpt-3.5-turbo");
        $this->setBaseUrl("https://api.openai.com/v1");
        $this->setLogPath("/usr/src/app/ai_content/openai.log");
        $this->disableLogToFile();
        $this->setDescription("You are now JARVIS. An exact copy of the JARVIS AI in the ironman movies. You are now an AI assistant for the user. You are able to make function calls.");
        $this->setInstructions("You are now JARVIS. An exact copy of the JARVIS AI in the ironman movies. You are now an AI assistant for the user. You are able to make function calls.
        If the user asks you a questions you are unable to answer or do have the ability to access first check the previous SYSTEM prompts in the conversation for answers to the users question.
        If you still need additional infos, tell the user to submit them. The ursers server is a VPS running portainer and docker and its domain is darkorder.de.
        Your interface is running on symfony 6 inside a container.
        Your url is localhost:8080 and your html-docroot is /usr/src/app/public/ .
        The only local paths where you can read and write files are /usr/src/app/ai_content/ and /usr/src/app/public/uploads/ .
        Files inside /usr/src/app/public/uploads/ are accessible via the url http://localhost:8080/uploads/ .
        There is no dir /mnt/data/ on the server. If you need to store data, use /usr/src/app/public/uploads/ or /usr/src/app/ai_content/, preferably the first one.
        
        ");
    }

    /**
     * Sets the instructions for the OpenAI assistant.
     *
     * @param string $instructions The instructions to set.
     * @return void
     */
    public function setInstructions(string $instructions): void
    {
        $this->instructions = $instructions;
    }

    /**
     * Sets the description for the OpenAI assistant.
     *
     * @param string $description The description to set.
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Enables logging to a file.
     * 
     * @return void
     */
    public function enableLogToFile() : void
    {
        $this->logToFile = true;
    }
    
    /**
     * Disables logging to a file.
     * 
     * @return void
     */
    public function disableLogToFile() : void
    {
        $this->logToFile = false;
    }

    /**
     * Sets the model for the OpenAI assistant.
     *
     * @param string $sModel The model to set.
     * @return void
     */
    public function setModel(string $sModel): void
    {
        $this->model = $sModel;
    }

    /**
     * Sets the log path for the OpenAI assistant.
     *
     * @param string $logPath The path to the log file.
     * @return void
     */
    public function setLogPath(string $logPath): void
    {
        $this->logPath = $logPath;
    }

    /**
     * Sets the logger for the OpenAIAssistant.
     *
     * @param mixed $logger The logger to set.
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Sets the base URL for the assistant.
     *
     * @param string $url The base URL to set.
     * @return void
     */
    private function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * Sets the ID of the assistant.
     *
     * @param string $assistantId The ID of the assistant.
     * @return void
     */
    public function setAssistantId(string $assistantId): void
    {
        $this->assistantId = $assistantId;
    }

    /**
     * Sets the API key for the OpenAI Assistant.
     *
     * @param string $apiKey The API key to set.
     * @return void
     */
    public function setApikey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Sets the skills of the openAIAssistant.
     *
     * @param array $aSkills An array of skills.
     * @return void
     */
    public function setSkills(array $aSkills): void
    {
        $this->aSkills = $aSkills;

        foreach ($this->aSkills as $oSkills) {
            $aInterfaces = class_implements($oSkills);
            if( in_array( 'Mugen0815\OpenAIAssistant\SkillsInterface', $aInterfaces )) {
                foreach ($oSkills->getFunctions() as $sFncName) {
                    $this->aMergedSkills[$sFncName] = get_class($oSkills);
                }
            }
        }
    }

    /**
     * Chat with the OpenAI assistant.
     *
     * @param string $sThread The thread identifier.
     * @param string $sMessage The message to send.
     * @return string The response from the assistant.
     */
    public function chat(string $sThread, string $sMessage): string
    {
        $this->finishOldRunIfExists( $sThread );
        try {
            $return = $this->sendChatMessage( $sThread, $sMessage );
        }
        catch(Exception $e) {
            $this->log("Exception: ".$e->getMessage());
            $return = "Exception: ".$e->getMessage();
            $this->log("Exception: ".$e->getTraceAsString());
        }        

        return $return;
    }

    /**
     * Sends a chat message to the specified thread.
     *
     * @param string $sThread The thread to send the message to.
     * @param string $sMessage The message to send.
     * @throws Exception If no data is found in the response.
     * @return string The value of the text in the response.
     */
    public function sendChatMessage(string $sThread, string $sMessage): string
    {
        $sResponse2 = $this->postAMessages($sThread, $sMessage);
        $sResponse3 = $this->postARuns($sThread);
        $aResponse3 = json_decode($sResponse3, true);
        if (isset($aResponse3['id'])) {
            $sRun = $aResponse3['id'];
        } else {
            $sRun = null;//TODO: check, if waitforresponse is optional
        }

        if (isset($sRun) && !is_null($sRun)) {
            $sResponse4 = $this->waitForResponse($sThread, $sRun);
        }
        $sResponse5 = $this->getAMessages($sThread);
        $aResponse5 = json_decode($sResponse5, true);
        if (!isset($aResponse5['data'])) {
            throw new Exception('No data found in response.');
        }

        return $aResponse5['data'][0]['content'][0]['text']['value'];
    }

    /**
     * Finish the old run if it exists.
     *
     * @param string $sThread The thread identifier.
     * @return void
     */
    public function finishOldRunIfExists(string $sThread): void
    {
        $aRuns = $this->getActiveRuns($sThread);
        if (0 < count($aRuns)) {
            // old run exists and needs to be waited for
            foreach ($aRuns as $aRun) {
                if ("expired" !== $aRun['status']) {
                    $sRun = $aRun['id'];
                    $sResponsePostRuns = $this->postARuns($sThread, $sRun);
                    $sResponse4 = $this->waitForResponse($sThread, $sRun);
                }
            }
        } else {
            $sRun = null;
        }
    }

    /**
     * Collects function_calls and calls submitToolsOutput with the collected data.
     *
     * @param string $sThread The thread identifier.
     * @param string $sRun The run identifier.
     * @return void
     */
    public function setToolsOutput(string $sThread, string $sRun): void
    {
        $aFunctions = $this->getFunctionCalls($sThread);
        $this->log("got functioncalls:");
        $this->log(json_encode($aFunctions));
        if (count($aFunctions) > 0) {
            $oOutput = new \stdClass();
            $oOutput->tool_outputs = array();
            foreach ($aFunctions as $aFunction) {
                $sFunction = $aFunction[1];
                $sParam = $aFunction[2];
                if (isset($this->aMergedSkills, $sFunction)) {
                    $sClassName = $this->aMergedSkills[$sFunction];
                    foreach ($this->aSkills as $oSkill) {
                        if ($sClassName == get_class($oSkill)) {
                            $Skill = $oSkill;
                        }
                    }
                    $value = $Skill->$sFunction($sParam);
                } else {
                    echo "function not found: " . $sFunction;
                    var_dump($this->aMergedSkills);
                    die();
                }

                $oObject = new \stdClass();
                if (isset($aFunction[0])) {
                    $oObject->tool_call_id = $aFunction[0];
                    $oObject->output = $value;
                    $oOutput->tool_outputs[] = $oObject;
                } else {
                    echo "error: missing var";
                    var_dump($aFunctions);
                    die();
                }

            }
            $this->log("submitting output:");
            $this->log(json_encode($oOutput));
            $response=$this->submitToolsOutput($sThread, $sRun, $oOutput);
            $this->log("submitting output response:");
            $this->log($response);
        }
    }

    /**
     * Submits the output of the tools to the specified thread and run.
     *
     * @param string $sThread The thread identifier.
     * @param string $sRun The run identifier.
     * @param mixed $data The data to be submitted.
     * @return string The jaon-response from the assistant.
     */
    public function submitToolsOutput(string $sThread, string $sRun, $data): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/runs/' . $sRun . '/submit_tool_outputs';
        $response = $this->curlPost($endpoint, $data);

        return $response;
    }

    /**
     * Retrieves the functions to be called based on the given thread.
     *
     * @param string $sThread The thread to retrieve A functions for.
     * @return array The functions to be called.
     */
    public function getFunctionCalls(string $sThread): array
    {
        $aFunctions = array();
        $sResponse11 = $this->getARuns($sThread);
        $aResponse11 = json_decode($sResponse11, true);
        if (isset($aResponse11['data'][0]) && "requires_action" == $aResponse11['data'][0]['status']) {
            foreach ($aResponse11['data'][0]['required_action']["submit_tool_outputs"]["tool_calls"] as $aCall) {
                $fncName = $aCall["function"]["name"];
                $aParams = json_decode($aCall["function"]["arguments"], true);
                $json3 = array_values(json_decode($aCall["function"]["arguments"], true));
                if (isset($json3[0])) {
                    $param = $json3;
                    $aFunctions[] = array($aCall["id"], $fncName, $aParams);
                }
            }
            return $aFunctions;
        } else {
            return array();
        }
    }

    /**
     * Posts a thread.
     *
     * @return string The result of posting the thread.
     */
    public function postAThreads(): string
    {
        $endpoint = $this->baseUrl . '/threads';
        $response = $this->curlPost($endpoint, array());

        return $response;
    }

    /**
     * Posts a message to a thread.
     *
     * @param string $thread The thread to post the message to.
     * @param string $msg The message to be posted.
     * @return string The result of posting the message.
     */
    public function postAMessages(string $thread, string $msg): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $thread . '/messages';
        $response = $this->curlPost($endpoint, array("role" => "user", "content" => $msg));

        return $response;
    }

    /**
     * Retrieves the runs for a given thread.
     *
     * @param string $sThread The thread identifier.
     * @return string The runs for the specified thread.
     */
    public function getARuns(string $sThread): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/runs';
        $response = $this->curlGet($endpoint);

        return $response;
    }

    /**
     * Retrieves messages for a given thread.
     *
     * @param string $sThread The thread identifier.
     * @return string An json of messages.
     */
    public function getAMessages(string $sThread): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/messages';
        $response = $this->curlGet($endpoint);

        return $response;
    }

    /**
     * Posts a run for a given thread.
     *
     * @param string $sThread The thread to post a run for.
     * @return string The result of posting the run.
     */
    public function postARuns(string $sThread): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/runs';
        $response = $this->curlPost($endpoint, array("assistant_id" => $this->assistantId));

        return $response;
    }

    /**
     * Deletes a thread.
     *
     * @param string $sThread The thread to be deleted.
     * @return string The result of deleting the thread.
     */
    public function deleteThread(string $sThread): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread;
        $response = $this->curlDelete($endpoint);

        return $response;
    }

    /**
     * Waits for the response of a specific thread and run.
     *
     * @param string $sThread The thread identifier.
     * @param string $sRun The run identifier.
     * @return string The jaon-response from the assistant.
     */
    public function waitForResponseRuns(string $sThread, string $sRun): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/runs/' . $sRun;
        $response = $this->curlGet($endpoint);

        return $response;
    }

    /**
     * Waits for a response from the assistant.
     *
     * @param string $sThread The thread identifier.
     * @param string $sRun The run identifier.
     * @return string The jaon-response from the assistant.
     */
    public function waitForResponseSteps(string $sThread, string $sRun): string
    {
        $endpoint = $this->baseUrl . '/threads/' . $sThread . '/runs/' . $sRun . '/steps';
        $response = $this->curlGet($endpoint);

        return $response;
    }

    /**
     * Waits for a response from the OpenAI assistant.
     *
     * @param string $sThread The thread identifier.
     * @param string $sRun The run identifier.
     * @return string The json-response from the assistant.
     */
    public function waitForResponse(string $sThread, string $sRun): string
    {
        $count = 0;
        $status = "in_progress";
        while( $status == "queued" or $status == "in_progress" or $status == "requires_action" and $count < 30 ) {
            $sResponse = $this->waitForResponseRuns($sThread, $sRun);
            $aResponse = json_decode($sResponse, true);
            $status = $aResponse['status'];
            #echo "<br>runs try: $count , status: ".$aResponse['status'];
            if( $status == "requires_action" ) {
                $this->log("requires_action in run");
                $this->setToolsOutput( $sThread, $sRun );
            }

            $count++;
            sleep(1);
        };
        #echo "<br><b>status runs</b>: ".$aResponse['status']."<br>";

        $count = 0;
        $status = "in_progress";
        while( $status == "queued" or $status == "in_progress" or $status == "requires_action" and $count < 5 ) {
            $sResponse = $this->waitForResponseSteps($sThread, $sRun);
            $aResponse = json_decode($sResponse, true);
            $status = $aResponse['data'][0]['status'];
            #echo "<br>steps try: $count , status: ".$aResponse['data'][0]['status'];
            if( $status == "requires_action" ) {
                $this->log("requires_action in steps");
                $this->setToolsOutput( $sThread, $sRun );
            }

            $count++;
            sleep(1);
        };
        #echo "<br><b>status steps</b>: ".$aResponse['data'][0]['status']."<br>";

        return $sResponse;
    }



    /**
     * Retrieves the active runs for a given thread.
     *
     * @param string $sThread The thread identifier.
     * @return array The array of active runs.
     */
    public function getActiveRuns(string $sThread): array
    {
        $sResponse = $this->getARuns($sThread);
        $aResponse = json_decode($sResponse, true);
        $aRuns = array();
        if( !isset( $aResponse['data'] )) {
            return ["thread does not exist"];
        }

        foreach( $aResponse['data'] as $aRun ) {
            if( "completed" !== $aRun['status'] && "expired" !== $aRun['status'] ) {
                $aRuns[] = $aRun;
            }
        }

        return $aRuns;
    }


    
    /**
     * Sends a POST request to the OpenAI assistant endpoint.
     *
     * @param string $endpoint The endpoint URL.
     * @param array $data The data to be sent in the request.
     * @return string The json-result for the POST-request.
     */
    public function curlPost(string $endpoint, mixed $data): string
    {
        $this->log("<br>POST $endpoint");
        $json_data = json_encode($data);
        $this->log("DATA: " . $json_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1',
            'Authorization: Bearer ' . $this->apiKey,
        ));
        $response = curl_exec($ch);

        if( curl_error( $ch )) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);
        $this->log[] = "RESPONSE: " . $response;
        $this->log("RESPONSE: " . $response);

        return $response;
    }

    /**
     * Retrieves the assistant based on the specified endpoint.
     *
     * @param string $endpoint The endpoint to retrieve the assistant from.
     * @return string The json-result for the GET-request.
     */
    public function curlGet(string $endpoint): string
    {
        $this->log("<br>GET $endpoint");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1',
            'Authorization: Bearer ' . $this->apiKey,
        ));
        $response = curl_exec($ch);

        if( curl_error( $ch )) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);
        $this->log("RESPONSE: " . $response);

        return $response;
    }

    /**
     * Sends curl-request via DELETE.
     *
     * @param string $endpoint The endpoint for the assistant.
     * @return string The json-result for the DELETE-request.
     */
    public function curlDelete(string $endpoint): string
    {
        $this->log("<br>DELETE $endpoint");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1',
            'Authorization: Bearer ' . $this->apiKey,
        ));
        $response = curl_exec($ch);

        if( curl_error( $ch )) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);
        $this->log("RESPONSE: " . $response);

        return $response;
    }


    /**
     * Retrieves the FNC JSON data.
     *
     * @return array The FNC JSON data.
     */
    public function getFncJson(): array
    {
        $aFncJson = array();
        foreach( $this->aSkills as $oSkills ) {
            foreach( $oSkills->getFunctions() as $sFncName ) {
                $aFncJson[$sFncName] = $oSkills->getFncJson( $sFncName );
            }
        }

        return $aFncJson;
    }

    /**
     * Logs a message.
     *
     * @param string $sLog The message to be logged.
     * @return void
     */
    private function log(string $sLog): void
    {
        $this->log[] = $sLog;
        if( $this->logToFile
            && isset($this->logPath)
            && !empty($this->logPath)
        ){
            file_put_contents($this->logPath,PHP_EOL.$sLog.PHP_EOL,FILE_APPEND);
        }        
        
        if( isset( $this->logger )) {
            $this->logger->info($sLog);
        }
    }

    /**
     * Creates an assistant.
     *
     * @return string The created assistant.
     */
    public function createAssistant(): string
    {
        $endpoint = $this->baseUrl . '/assistants';
        $data = array(
            "name" => "Jarvis",
            "description" => $this->description,
            "instructions" => $this->instructions,
            "model" => $this->model,
            "tools" => $this->getTools()
        );
        $response = $this->curlPost($endpoint, $data);

        return $response;
    }

    /**
     * Retrieves the tools used by the openAIAssistant.
     *
     * @return array The tools used by the openAIAssistant.
     */
    private function getTools(): array
    {
        $aTools = [];
        $aTools[] = array("type" => "code_interpreter");
        foreach( $this->aSkills as $oSkills ) {
            foreach( $oSkills->getFunctions() as $sFncName ) {
                $aTools[] = array(
                    "type" => "function",
                    "function" => json_decode($oSkills->getFncJson($sFncName)),
                );
            }
        }

        return $aTools;
    }
}
