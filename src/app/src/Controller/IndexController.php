<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class IndexController
 * 
 * This class is responsible for handling requests related to the index page.
 * It extends the AbstractController class.
 * 
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    #[Route('/' , name: 'index')]
    #[Route('/jarvis-{view}', name: 'jarvis')]
    public function index(): Response
    {
        return $this->render('index/jarvisvue.html.twig', []);
    }

    #[Route('/basic', name: 'basic')]
    public function basic(): Response
    {
        return $this->render('index/jarvis.html.twig', []);
    }

}
