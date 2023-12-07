<?php
namespace Mugen0815\OpenAIAssistant;

require_once __DIR__ . '/SkillsInterface.php';

use Mugen0815\OpenAIAssistant\SkillsInterface;


/**
 * Class Skills
 * 
 * This class implements the SkillsInterface.
 */
class DefaultSkills implements SkillsInterface
{

    /**
     * Retrieves the list of functions.
     *
     * @return array The list of functions.
     */
    public function getFunctions(): array
    {
        return array('get_excerpt', 'curl_get', 'get_news', 'web_search');
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
            'get_weather' => array(
                'description' => 'get weather for a given location',
                'parameters' => array(
                    array('location', 'string', 'name or domain of server'),
                    array('result', 'string', 'json-string containing php-curl-response from portainer-rest-api from GET /endpoints/2/docker/containers/json?all=1 '),
                ),
                'required' => array('location'),
            ),
            'get_excerpt' => array(
                'description' => 'loads a html-excerpt from a given url',
                'parameters' => array(
                    array('target', 'string', 'url'),
                    array('result', 'string', 'json-string containing html-excerpt'),
                ),
                'required' => array('target'),
            ),
            'curl_get' => array(
                'description' => 'makes curl-request with GET to given target url and returns result as json-string',
                'parameters' => array(
                    array('target', 'string', 'url'),
                    array('result', 'string', 'json-strong containing curl-result'),
                ),
                'required' => array('target'),
            ),
            'get_news' => array(
                'description' => 'get todays news',
                'parameters' => array(
                    array('topic', 'string', 'topic of news.'),
                    array('result', 'string', 'json-string containing news'),
                ),
                'required' => array('location'),
            ),
            'get_stacks' => array(
                'description' => 'list all docker-compose-stacks on a server',
                'parameters' => array(
                    array('location', 'string', 'name or domain of server'),
                    array('result', 'string', 'json-string containing php-curl-response from portainer-rest-api from GET /stacks '),
                ),
                'required' => array('location'),
            ),
            'web_search' => array(
                'description' => 'search the web',
                'parameters' => array(
                    array('topic', 'string', 'topic to search for'),
                    array('result', 'string', 'json-string containing search-results'),
                ),
                'required' => array('topic'),
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
     * Saves a local file.
     *
     * @param array $array The data to be saved.
     * @return string The path of the saved file.
     */
    public function save_local_file(array $array): string
    {
        $filename = str_replace('..', '', $array['filename']);
        $filename = str_replace('//', '', $filename);
        $content = str_replace('\n', PHP_EOL, $array['content']);
        file_put_contents('/usr/src/app/ai_content/' . $filename, $content);

        return filesize('/usr/src/app/ai_content/' . $filename);
    }

    /**
     * Retrieves the excerpt from an array.
     *
     * @param array $array The array from which to retrieve the excerpt.
     * @return string The extracted excerpt.
     */
    public function get_excerpt(array $array): string
    {
        $endpoint = $array['target'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);

        $sTest = $response;
        $aTest1 = explode('<body', $sTest);
        $sTest2 = $aTest1[1];
        $sTest2 = substr($sTest2, 0, strpos($sTest2, '</body>'));

        if (false !== strpos($sTest2, '</header>')) {
            $sTest2 = substr($sTest2, strpos($sTest2, '</header>') + 9);
        }

        if (false !== strpos($sTest2, '<footer')) {
            $sTest2 = substr($sTest2, 0, strpos($sTest2, '<footer'));
        }

        return $sTest2;
    }

    /**
     * Performs a GET request using cURL.
     *
     * @param array $array The parameters for the GET request.
     * @return string The response from the GET request.
     */
    public function curl_get(array $array): string
    {
        
        $endpoint = $array['target'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);

        return $response;
    }


    /**
     * Retrieves news based on the given array.
     *
     * @param array $array The array containing the news data.
     * @return string The retrieved news.
     */
    public function get_news_old(array $array): string
    {
        if(''==$array['topic'] || 'allgemein'==$array['topic'])
        {
            $endpoint = 'https://www.n-tv.de/rss';    
        }
        else
        {
            $endpoint = 'https://www.n-tv.de/'.$array['topic'].'/rss';
        }

        #$endpoint = 'https://www.n-tv.de/'.$array['topic'].'/rss';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);

        return $response;
    }

    
    /**
     * Retrieves news based on the given array of parameters.
     *
     * @param array $array The array of parameters for retrieving news.
     * @return string The retrieved news as a json-string.
     */
    public function get_news(array $array): string
    {
        $sTopic = $array['topic'];

        $endpoint = 'https://searx.prvcy.eu/search?q='.$sTopic.'&format=json&categories_news=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);

        return $response;
    }


    /**
     * Performs a web search using the given array of parameters.
     *
     * @param array $array The array of parameters for the web search.
     * @return string The result of the web search as json.
     */
    public function web_search(array $array): string
    {
        $sTopic = $array['topic'];

        $endpoint = 'https://searx.prvcy.eu/search?q='.$sTopic.'&format=json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        } else {
        }
        curl_close($ch);

        return $response;
    }

    /**
     * Retrieves the weather information.
     *
     * @param array $array The array containing the weather data.
     * @return string The weather information.
     */
    public function get_weather(array $array): string
    {
        return rand(-10, 30) . 'c';
    }

}
