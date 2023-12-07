<?php
namespace Mugen0815\OpenAIAssistant;

require_once __DIR__ . '/SkillsInterface.php';

use Mugen0815\OpenAIAssistant\SkillsInterface;

/**
 * Class MailSkills
 * 
 * This class implements the SkillsInterface and is responsible for handling mail-related skills.
 */
class MailSkills implements SkillsInterface
{
    /**
     * Constructor for the MailSkills class.
     *
     * @param \PHPMailer\PHPMailer\PHPMailer $mailer The PHPMailer instance to be injected.
     */
    public function __construct(\PHPMailer\PHPMailer\PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Returns the mailer object used for sending emails.
     *
     * @return \PHPMailer\PHPMailer\PHPMailer The mailer object.
     */
    public function getMailer(): \PHPMailer\PHPMailer\PHPMailer
    {
        return $this->mailer;
    }

    /**
     * Retrieves the list of functions.
     *
     * @return array The list of functions.
     */
    public function getFunctions(): array
    {
        return array('send_mail');
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
            'send_mail' => array(
                'description' => 'send mail via smtp',
                'parameters' => array(
                    array('subject', 'string', 'subject of mail'),
                    array('message', 'string', 'message of mail'),
                    array('to', 'string', 'mail-address of recipient'),
                    array('result', 'string', 'true if mail was sent successfully, false otherwise'),
                ),
                'required' => array('subject', 'message', 'to'),
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
     * Sends an email using the provided array of data.
     *
     * @param array $array The array containing the email data.
     * @return bool Returns true if the email was sent successfully, false otherwise.
     */
    public function send_mail(array $array): string
    {
        $to      = $array['to'];
        $subject = $array['subject'];
        $message = $array['message'];
        $this->mailer->addAddress($to, $to);
        $this->mailer->Subject = $subject;
        $this->mailer->isHTML(TRUE);
        $this->mailer->Body = $message;
        try {
            $blReturn = $this->mailer->send();
        } catch (Exception $e) {
            $blReturn = false;
        }

        $sReturn = $blReturn ? 'true' : 'false';

        return $sReturn;
    }

}
