<?php
namespace Mugen0815\OpenAIAssistant;

require_once __DIR__ . '/SkillsInterface.php';

use Mugen0815\OpenAIAssistant\SkillsInterface;

/**
 * Class PortainerSkills
 *
 * This class implements the SkillsInterface and represents a service for managing skills in Portainer.
 */
class PortainerSkills implements SkillsInterface
{

    /**
     * Constructor for the PortainerSkills class.
     *
     * @param string $username The username for authentication.
     * @param string $password The password for authentication.
     * @param string $url The URL of the Portainer instance.
     */
    public function __construct(string $username, string $password, string $url)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
    }


    /**
     * Retrieves the list of functions.
     *
     * @return array The list of functions.
     */
    public function getFunctions(): array
    {
        return array('get_portainer_users', 'get_stacks', 'get_containers');
    }

    /**
     * Retrieves the JSON representation of a specific function.
     *
     * @param string $sFncName The name of the function.
     * @return string The JSON representation of the function.
     */
    public function getFncJson(string $sFncName): string
    {

        $obj = $this->getFncObj($sFncName);
        return json_encode($obj);

    }

    /**
     * Retrieves the function object based on the given function name.
     *
     * @param string $sFncName The name of the function.
     * @return object The function object.
     */
    public function getFncObj(string $sFncName): object
    {
        $aFunctions = array
            (
            'get_portainer_users' => array(
                'description' => 'list all users of ur portainer-instance on a given url',
                'parameters' => array(
                    array('url', 'string', 'name or domain of server'),
                    array('result', 'string', 'json-string containing php-curl-response from portainer-rest-api from GET /users '),
                ),
                'required' => array('url'),
            ),
            'get_stacks' => array(
                'description' => 'list all docker-compose-stacks on a server',
                'parameters' => array(
                    array('location', 'string', 'name or domain of server'),
                    array('result', 'string', 'json-string containing php-curl-response from portainer-rest-api from GET /stacks '),
                ),
                'required' => array('location'),
            ),
            'get_containers' => array(
                'description' => 'list all docker-containers on a server',
                'parameters' => array(
                    array('location', 'string', 'name or domain of server'),
                    array('result', 'string', 'json-string containing php-curl-response from portainer-rest-api from GET /endpoints/2/docker/containers/json?all=1 '),
                ),
                'required' => array('location'),
            ),
        );

        $obj = new \stdClass();
        $obj->name = $sFncName;
        $obj->description = $aFunctions[$sFncName]['description'];
        $obj->parameters = new \stdClass();
        $obj->parameters->type = 'object';
        $obj->parameters->properties = new \stdClass();
        foreach ($aFunctions[$sFncName]['parameters'] as $aParam) {
            $obj->parameters->properties->{$aParam[0]} = new \stdClass();
            $obj->parameters->properties->{$aParam[0]}->type = $aParam[1];
            $obj->parameters->properties->{$aParam[0]}->description = $aParam[2];
        }
        $obj->parameters->required = $aFunctions[$sFncName]['required'];

        return $obj;
    }

    /**
     * Retrieves the containers based on the given array.
     *
     * @param array $array The array containing the necessary information.
     * @return string The retrieved containers.
     */
    public function get_containers(array $array): string
    {
        $token = $this->getToken();

        $ch = curl_init('https://admin.darkorder.de/api/endpoints/2/docker/containers/json?all=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', "Authorization: Bearer " . $token . ""));
        $apiResponse = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }
        curl_close($ch);
        $var = json_decode($apiResponse);

        $output = [];
        foreach ($var as $row) {
            if (isset($row->Name)) {
                $output[] = $row->Name;
            } else {
                #echo "response malformed";
                #var_dump($row);
            }
        }

        return $apiResponse;
    }

    /**
     * Retrieves the stacks from the given array.
     *
     * @param array $array The array containing the stacks.
     * @return string The retrieved stacks.
     */
    public function get_stacks(array $array): string
    {
        $token = $this->getToken();


        $ch = curl_init($this->url . '/api/stacks?filters={"EndpointID":2,"IncludeOrphanedStacks":true}');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', "Authorization: Bearer " . $token . ""));
        $apiResponse = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }
        curl_close($ch);
        $var = json_decode($apiResponse);

        $output = [];
        foreach ($var as $row) {
            if (isset($row->Name)) {
                $output[] = $row->Name;
            } else {
                #echo "response malformed";
                #var_dump($row);
            }
        }

        return $apiResponse;
    }

    /**
     * Retrieves the stack from the given array.
     *
     * @param array $array The array containing the stack.
     * @return string The retrieved stack.
     */
    public function get_stack(array $array): string
    {
        $token = $this->getToken();

        $ch = curl_init($this->url . '/api/stacks/' . $array[0]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', "Authorization: Bearer " . $token . ""));
        $apiResponse = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }
        curl_close($ch);

        $var = json_decode($apiResponse);
        $output = [];
        foreach ($var as $row) {
            if (isset($row->Name)) {
                $output[] = $row->Name;
            } else {
                #echo "response malformed";
                #var_dump($row);
            }
        }

        return $apiResponse;
    }

    /**
     * Retrieves the Portainer users.
     *
     * @param array $array The array parameter.
     * @return string The result of the operation.
     */
    public function get_portainer_users(array $array): string
    {
        $token = $this->getToken();

        $ch = curl_init($this->url . '/api/users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', "Authorization: Bearer " . $token . ""));
        $apiResponse = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }
        curl_close($ch);
        $var = json_decode($apiResponse);
        $output = [];

        foreach ($var as $row) {
            if (isset($row->Name)) {
                $output[] = $row->Name;
            } else {
                #echo "response malformed";
                #var_dump($row);
            }
        }

        return $apiResponse;
    }

    /**
     * Retrieves the token for accessing the Portainer API.
     *
     * @return string The token.
     */
    public function getToken(): string
    {
        $postRequest = array(
            'Username' => $this->username,
            'Password' => $this->password
        );

        $ch = curl_init($this->url . '/api/auth');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postRequest));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        $apiResponse = curl_exec($ch);
        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }
        curl_close($ch);
        $var = json_decode($apiResponse);

        return $var->jwt;
    }

}
