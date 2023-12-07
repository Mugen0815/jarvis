<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Mugen0815\OpenAIAssistant\OpenAIAssistant;
use Mugen0815\OpenAIAssistant\DefaultSkills;
use Mugen0815\OpenAIAssistant\PortainerSkills;
use Mugen0815\OpenAIAssistant\FilesystemSkills;
use Mugen0815\OpenAIAssistant\DalleSkills;
use Mugen0815\OpenAIAssistant\MailSkills;


/**
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractController
{
    #[Route('/api')]
    public function index(): Response
    {
        $yourApiKey   = getenv('APIKEY');
        if( !$yourApiKey || '' === $yourApiKey ){
            return new JsonResponse(['error'=>'APIKEY missing. Please set environment variable APIKEY']);
        }

        $aThreads = $this->getThreads();
        if( $aThreads === null ){
            $aThreads = array();
        }
        if( count($aThreads) === 0){
            $app = $this->getAssistant();
            $sResponse1 = $app->postAThreads();
            $aResponse1 = json_decode($sResponse1,true);
            $sThread = $aResponse1['id'];
            $output= $sThread;
            $aThreads = $this->getThreads();
            $aThreads[] = $sThread;
            $this->setThreads($aThreads);
        }
 
        if( count( $aThreads ) === 0 ){
            return new JsonResponse(['error'=>'no threads']);
        }

        return new JsonResponse(['result'=>array('status'=>'ok','threads'=>$aThreads)]);
    }


    #[Route('/api/chat')]
    public function chat(Request $request): Response
    {
        $sMessage = $request->request->get('message');
        $sThread  = $request->request->get('thread');
        $app = $this->getAssistant();
        $response = $app->chat($sThread,$sMessage);

        return new JsonResponse($this->addHtmlToOpenAIResponseText($response));
    }


    #[Route('/api/messages')]
    public function messages(Request $request): Response
    {
        $app = $this->getAssistant();
        $sThread = $request->get('thread');
        $sResponse = $app->getAMessages($sThread);
        $aResponse = json_decode($sResponse,true);
        if( isset( $aResponse['data'] )){
            $aMessages = $aResponse['data'];
        }
        else{
            $aMessages = array();
        }
        foreach( $aMessages as $key => $aMessage ){
            $aMessages[$key]['content'][0]['text']['value'] = $this->addHtmlToOpenAIResponseText($aMessage['content'][0]['text']['value']);
        }

        return new JsonResponse($aMessages);
    }


    #[Route('/api/skills')]
    public function skills(): Response
    {
        $app = $this->getAssistant();
        $aFunctions = $app->getFncJson();

        return $this->render('api/functions.html.twig', [
            'output' => $aFunctions,
        ]);
    }


    #[Route('/api/createAssistant')]
    public function createAssistant(): Response
    {
        $app = $this->getAssistant();
        $response = $app->createAssistant();
        $json=json_decode( $response, true );
        $assistantId=$json['id'];
        $this->storeAssistantId( $assistantId );

        return new JsonResponse($json); 
    }


    #[Route('/api/newthread')]
    public function newthread(): Response
    {
        $app = $this->getAssistant();
        $sResponse = $app->postAThreads();
        $aResponse = json_decode( $sResponse, true );
        $sThread = $aResponse['id'];
        $output= $sThread;
        $aThreads = $this->getThreads();
        $aThreads[] = $sThread;
        $this->setThreads( $aThreads );

        return new JsonResponse([$sThread]);
    }


    #[Route('/api/deletethread')]
    public function deletethread(Request $request): Response
    {
        $app = $this->getAssistant();
        $sThread = $request->get('thread');
        if( is_null( $sThread )){
            return new JsonResponse(['error'=>'threadId missing']);
        }

        $sResponse = $app->deleteThread( $sThread );
        $aResponse = json_decode( $sResponse, true );
        if(isset($aResponse1['error'])){
            return new JsonResponse(['error'=>$aResponse['error']]);
        }

        $aThreads = $this->getThreads();
        $aThreads2 = array();
        foreach( $aThreads as $sThread2 ){
            if( $sThread2 !== $sThread ){
                $aThreads2[] = $sThread2;
            }
        }
        $this->setThreads($aThreads2);

        return new JsonResponse( $aThreads2 );
    }


    #[Route('/api/threads')]
    public function threads(Request $request): Response
    {
        $aThreads = $this->getThreads();
        $sThreads = json_encode( $aThreads, true );

        return new JsonResponse( $aThreads );
    }


    /**
     * Sets the threads.
     *
     * @param array $aThreads The array of threads.
     * @return void
     */
    protected function setThreads(array $aThreads)
    {
        $path = '/usr/src/app/ai_content/threads.json';
        file_put_contents( $path ,json_encode( $aThreads, true ));
    }


    /**
     * Retrieves the threads.
     *
     * @return array The threads.
     */
    protected function getThreads()
    {
        $path = '/usr/src/app/ai_content/threads.json';
        if( file_exists( $path ) ){
            $aThreads = json_decode(file_get_contents($path),true);
        }
        else{
            $aThreads = array();
        }

        return $aThreads;
    }


    /**
     * Retrieves the assistant ID.
     *
     * @return int The assistant ID.
     */
    public function getAssistantId()
    {
        $assistantId = $this->getStoredAssistantId();
        if( $assistantId === '' ){
            $app = $this->getAssistant();
            $response = $app->createAssistant();
            $json = json_decode($response,true);
            $assistantId=$json['id'];
            $this->storeAssistantId($assistantId);
        }

        return  $assistantId;
    }


    /**
     * Retrieves the stored assistant ID.
     *
     * @return int The stored assistant ID.
     */
    public function getStoredAssistantId()
    {
        $path = '/usr/src/app/ai_content/assistantId.txt';
        if(file_exists($path))
        {
            $assistantId = file_get_contents($path);
        }
        else
        {
            $assistantId = '';
        }

        return $assistantId;
    }


    /**
     * Sets the assistant ID.
     *
     * @param string $assistantId The ID of the assistant.
     */
    public function storeAssistantId(string $assistantId)
    {
        $path = '/usr/src/app/ai_content/assistantId.txt';
        file_put_contents( $path ,$assistantId);
    }
    

    /**
     * Retrieves the assistant object.
     *
     * @return OpenAIAssistant The assistant object.
     */
    private function getAssistant()
    {
        $sApiKey          = getenv('APIKEY');
        $sModel           = getenv('MODEL');
        $sAssistantId     = $this->getStoredAssistantId();
        $DefaultSkills    = new DefaultSkills();
        $sPortainerUser   = getenv('PORTAINER_USERNAME');
        $sPortainerPass   = getenv('PORTAINER_PASSWORD');
        $sPortainerUrl    = getenv('PORTAINER_URL');
        $PortainerSkills  = new PortainerSkills($sPortainerUser,$sPortainerPass,$sPortainerUrl);
        $FilesystemSkills = new FilesystemSkills();
        $DalleSkills      = new DalleSkills($sApiKey);
        $MailSkills       = new MailSkills( $this->getMailer() );

        $Assistant = new OpenAIAssistant($sApiKey); // must be set in environment variable APIKEY
        $Assistant->setSkills([$DefaultSkills,$PortainerSkills,$FilesystemSkills,$DalleSkills,$MailSkills]); // add own skills here
        //$Assistant->enableLogToFile(); // optional, default is disabled

        if( false !== $sModel && '' !== $sModel){ 
            $Assistant->setModel( $sModel );
        }
        if( '' === $sAssistantId ){ // no assistant ID stored yet in '/usr/src/app/ai_content/assistantId.txt', so create a new assistant and store the ID
            $sResponse   = $Assistant->createAssistant();
            $aResponse   = json_decode($sResponse,true);
            $sAssistantId = $aResponse['id'];
            $this->storeAssistantId( $sAssistantId );
        }
        $Assistant->setAssistantId( $sAssistantId );

        return $Assistant; // Assistant is now ready to use
    }

    /**
     * Retrieves the mailer instance.
     *
     * @return Mailer The mailer instance.
     */
    private function getMailer()
    {
        $sMailHost    = getenv('MAIL_HOST');
        $sMailUser    = getenv('MAIL_USERNAME');
        $sMailPass    = getenv('MAIL_PASSWORD');
        $sMailPort    = getenv('MAIL_PORT');
        $sMailFrom    = getenv('MAIL_FROM');
        $mailer = new PHPMailer(1);
        $mailer->isSMTP();
        if($sMailHost){
            $mailer->Host = $sMailHost;
        }
        if($sMailUser){
            $mailer->Username = $sMailUser;
        }
        if($sMailPass){
            $mailer->Password = $sMailPass;
        }
        if($sMailPort){
            $mailer->Port = $sMailPort;
        }
        if($sMailFrom){
            $mailer->setFrom($sMailFrom);
        }
        $mailer->SMTPDebug = 0;
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        return $mailer;
    }

    /**
     * Adds HTML to the OpenAI response text.
     *
     * @param string $sResponseText The response text to add HTML to.
     * @return string The modified response text with HTML added.
     */
    private function addHtmlToOpenAIResponseText(string $sResponseText): string
    {
        $sResponseText = str_replace("\n", "<br>", $sResponseText);
        $sResponseText = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $sResponseText);
        $sResponseText = str_replace("\r", "", $sResponseText);
        $sResponseText = str_replace("  ", "&nbsp;&nbsp;", $sResponseText);
        $pattern = '/(?<!href=")http[s]?:\/\/[^\s"<\)]+/i';
        $replacement = '<a href="$0">$0</a>';
        $sResponseText = preg_replace($pattern, $replacement, $sResponseText);
        $sResponseText = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $sResponseText);

        return $sResponseText;
    }

}
