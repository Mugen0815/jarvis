<?php
namespace Mugen0815\OpenAIAssistant;

require_once __DIR__ . '/SkillsInterface.php';

use Mugen0815\OpenAIAssistant\SkillsInterface;

/**
 * Class DalleSkills
 * 
 * TODO: add description
 *       add parameter baseUrl like eg 'https://api-inference.huggingface.co/models/EleutherAI/gpt-neo-2.7B'
 *       add parameter apiKey
 *       add parameter prompt
 *       add parameter filepath
 * 
 * 
 * This class implements the SkillsInterface and represents a service for managing Dalle skills.
 */
class DalleSkills implements SkillsInterface
{
    public string $openAiKey;
    private array $aAllowedDirectories;

    public function __construct(string $openAiKey)
    {
        $this->openAiKey = $openAiKey;
        $this->aAllowedDirectories = array(
            '/usr/src/app/ai_content/',
            '/usr/src/app/public/uploads/',
        );
    }

    /**
     * Returns an array of functions.
     *
     * @return array The array of functions.
     */
    public function getFunctions(): array
    {
        return array('generate_dalle_image');
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

        return $aFunctions[$sFncName];
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
            'generate_dalle_image' => array
            (
                'description' => 'generate image with DALL-E and saves it locally. Size is 1024x1024.',
                'parameters' => array(
                    array('prompt', 'string', 'prompt for image generation'),
                    array('filepath', 'string', 'filepath to save image locally'),
                    array('result', 'string', 'url with original image on dalle-site if success, else error message'),
                ),
                'required' => array('prompt', 'filepath'),
            ),
        );

        $obj = new \stdClass();
        $obj->name = $sFncName;
        $obj->description = $aFunctions[$sFncName]['description'];
        $obj->parameters = new \stdClass();
        $obj->parameters->type = 'object';
        $obj->parameters->properties = new \stdClass();
        #var_dump($aFunctions[$sFncName]['parameters']);die();
        foreach ($aFunctions[$sFncName]['parameters'] as $aParam) {
            $obj->parameters->properties->{$aParam[0]} = new \stdClass();
            $obj->parameters->properties->{$aParam[0]}->type = $aParam[1];
            if (isset($aParam[2])) {
                $obj->parameters->properties->{$aParam[0]}->description = $aParam[2];
            }

        }
        $obj->parameters->required = $aFunctions[$sFncName]['required'];

        return $obj;
    }

    /**
     * Generates a Dalle image based on the given array.
     *
     * @param array $array The array used to generate the Dalle image.
     * @return string The generated Dalle image.
     */
    public function generate_dalle_image(array $array): string
    {
        $aReturn = array();

        $sLog = date('Y-m-d H:i:s') . ' - ' . $array['prompt'] . ' - ' . $array['filepath'];
        file_put_contents("/usr/src/app/ai_content/log_dalle.log", PHP_EOL . $sLog . PHP_EOL, FILE_APPEND);
        $endpoint = 'https://api.openai.com/v1/images/generations';
        $json_data = '{
        "model": "dall-e-3",
        "prompt": "' . $array['prompt'] . '",
        "n": 1,
        "size": "1024x1024"
      }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1',
            'Authorization: Bearer ' . $this->openAiKey,
        ));
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo "Error: " . curl_error($ch);
            die();
        }

        curl_close($ch);

        $json = json_decode($response, true);

        if (isset($json['data'][0]["url"])) {
            $url = $json['data'][0]["url"];
            $aReturn[1] = $url;
        } else {
            return "Error: " . $response;
            $aReturn[1] = "Error: " . $response;
        }

        file_put_contents("/usr/src/app/ai_content/log_dalle.log", PHP_EOL . 'URL:' . $url . PHP_EOL, FILE_APPEND);

        $sImageContent = $this->download_image($url);
        $sFilepath = $this->sanitizePath($array['filepath']);
        file_put_contents("/usr/src/app/ai_content/log_dalle.log", PHP_EOL . 'PATH:' . $sFilepath . PHP_EOL, FILE_APPEND);
        if (file_put_contents($sFilepath, $sImageContent, FILE_APPEND)) {
            $aReturn[0] = $sFilepath;
        } else {
            $aReturn[0] = 'Error: could not save image to ' . $sFilepath;
        }

        return json_encode($aReturn);
    }

    /**
     * Sanitizes the given path.
     *
     * @param string $sPath The path to sanitize.
     * @return string The sanitized path.
     */
    private function sanitizePath(string $sPath): string
    {
        $sDefaultDir = $this->aAllowedDirectories[0];

        foreach ($this->aAllowedDirectories as $sAllowedDirectory) {
            if (0 === strpos($sPath, $sAllowedDirectory)) {
                $sSanitizedPath = $sPath;
                break;
            }
        }

        if (!isset($sSanitizedPath)) {
            $sSanitizedPath = $sDefaultDir . '/' . $sPath;
        }

        $sSanitizedPath = str_replace('..', '', $sSanitizedPath);
        $sSanitizedPath = str_replace('//', '/', $sSanitizedPath);
        $sSanitizedPath = str_replace('//', '/', $sSanitizedPath);

        return $sSanitizedPath;
    }

    /**
     * Downloads an image from the given URL.
     *
     * @param string $url The URL of the image to download.
     * @return string The path to the downloaded image.
     */
    public function download_image(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $image = curl_exec($ch);
        curl_close($ch);

        return $image;
    }

    /**
     * Reads a file and returns its contents as a string.
     *
     * @param array $array The array containing the file path.
     * @return string The contents of the file as a string.
     */
    public function read_file(array $array): string
    {
        $filename = $array[0];
        $filename = str_replace('..', '', $filename);
        $filename = str_replace('//', '', $filename);

        if (file_exists('/usr/src/app/ai_content/' . $filename)) {
            $file = file_get_contents('/usr/src/app/ai_content/' . $filename);
        } else {
            return "File not found: " . '/usr/src/app/ai_content/' . $filename;
        }

        return json_encode($file);
    }

}
