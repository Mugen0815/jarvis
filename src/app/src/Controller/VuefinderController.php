<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Ozdemir\VueFinder\VueFinder;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;


/**
 * Class VuefinderController
 * 
 * This class is responsible for handling requests related to the index page.
 * It extends the AbstractController class.
 * 
 * @package App\Controller
 */
class VuefinderController extends AbstractController
{
    #[Route('/vuefinder')]
    public function index(): Response
    {
        $adapter = new LocalFilesystemAdapter(
            '/usr/src/app/ai_content/'
        );

        $adapter2 = new LocalFilesystemAdapter(
            '/usr/src/app/public/'
        );

        $adapter3 = new LocalFilesystemAdapter(
            '/usr/src/app/public/uploads/'
        );

        $vuefinder2 = new VueFinder([
            'uploads' => $adapter3,
            'local' => $adapter2,
            'ai_content' => $adapter
        ]);
        
        $config = [
            'publicLinks' => [
                'local://uploads' => 'http://localhost:8000/uploads/',
                'local://' => 'http://localhost:8000/',
            ]
        ];
    


        $response = $vuefinder2->init($config); //TODO: return $response
        die();
    }
}
