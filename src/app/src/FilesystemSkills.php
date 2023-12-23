<?php
namespace Mugen0815\OpenAIAssistant;

require_once __DIR__ . '/SkillsInterface.php';

use Mugen0815\OpenAIAssistant\SkillsInterface;


/**
 * Class FilesystemSkills
 * 
 * This class implements the SkillsInterface and provides functionality related to filesystem skills.
 */
class FilesystemSkills implements SkillsInterface
{
    private array $aAllowedDirectories;

    /**
     * Constructor for the FilesystemSkills class.
     */
    public function __construct()
    {
        $this->aAllowedDirectories = array(
            '/usr/src/app/public/uploads/',
            '/usr/src/app/ai_content/',
        );
    }

    /**
     * Returns an array of functions.
     *
     * @return array The array of functions.
     */
    public function getFunctions(): array
    {
        return array('read_dir', 'read_file', 'save_local_file', 'create_dir');
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
     * Sets the allowed directories.
     *
     * @param array $aAllowedDirectories The array of allowed directories.
     * @return void
     */
    public function setAllowedDirectories(array $aAllowedDirectories)
    {
        $this->aAllowedDirectories = $aAllowedDirectories;
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
            'read_dir' => array(
                'description' => 'list all files in given dir-path',
                'parameters' => array(
                    array('path', 'string', 'dir-path to list files'),
                    array('result', 'string', 'string containing all filenames in path'),
                ),
                'required' => array('path'),
            ),
            'read_file' => array(
                'description' => 'returns content of a file $filename',
                'parameters' => array(
                    array('path', 'string', 'filepath to read file'),
                    array('result', 'string', 'content of a file at $path'),
                ),
                'required' => array('path'),
            ),
            'save_local_file' => array(
                'description' => 'save content to file at $path',
                'parameters' => array(
                    array('path', 'string', 'full filepath to save file'),
                    array('content', 'string', 'content for file'),
                    array('size', 'string', 'filesize of saved file'),
                    array('fullpath', 'string', 'full filepath to save file'),
                ),
                'required' => array('path', 'content'),
            ),
            'create_dir' => array(
                'description' => 'creates directory under $dirname',
                'parameters' => array(
                    array('path', 'string', 'full path of dir to be ccrated'),
                    array('result', 'string', 'full path of created dir'),
                ),
                'required' => array('path')),
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
        $filepath = $this->sanitizePath($array['path']);
        $content = str_replace('\n', PHP_EOL, $array['content']);
        $result = file_put_contents($filepath, $content);

        file_put_contents("/usr/src/app/ai_content/log_file.log", PHP_EOL . date('Y-m-d H:i:s') . ' - ' . $filepath . ' - ' . $result . ' - ' . filesize($filepath) . PHP_EOL, FILE_APPEND);

        return filesize($filepath);
    }

    /**
     * Creates a directory.
     *
     * @param array $array The array containing the directory details.
     * @return string The path of the created directory.
     */
    public function create_dir(array $array): string
    {
        $dirname = $this->sanitizePath($array['path']);

        if (is_dir($dirname)) {
            return $dirname;
            return 'dir already exists: ' . $dirname;
        }

        try {
            mkdir($dirname, 0777, true);
        } catch (\Exception $e) {
            return 'error creating dir: ' . $dirname;
        }

        return $dirname;
    }

    /**
     * Reads the contents of a directory and returns it as a string.
     *
     * @param array $array The array containing the directory path.
     * @return string The contents of the directory.
     */
    public function read_dir(array $array): string
    {
        $dirname = $this->sanitizePath($array['path']);
        $files = [];
        if ($handle = opendir($dirname)) {

            while (false !== ($entry = readdir($handle))) {
                $files[] = $entry;
            }

            closedir($handle);
        }

        return json_encode($files);
    }

    /**
     * Reads a file and returns its contents as a string.
     *
     * @param array $array The array containing the file path and other optional parameters.
     * @return string The contents of the file as a string.
     */
    public function read_file(array $array): string
    {
        $filename = $this->sanitizePath($array['path']);

        if (file_exists($filename)) {
            $file = file_get_contents($filename);
        } else {
            return "File not found: " . $filename;
        }

        return json_encode($file);
    }

    /**
     * Sanitizes a given path.
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

}
