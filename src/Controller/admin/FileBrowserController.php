<?php

namespace App\Controller\admin;

use App\Entity\Media;
use App\Entity\Travel;
use App\Repository\ArticlesRepository;
use App\Repository\CategoryRepository;
use App\Repository\MediaRepository;
use App\Repository\PagesRepository;
use App\Repository\PopupsRepository;
use App\Repository\TravelRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Context\RequestStackContext;

class FileBrowserController extends AbstractController
{
    private $requestStackContext;

    public function __construct(RequestStackContext $requestStackContext)
    {
        $this->requestStackContext = $requestStackContext;
    }
    #[Route(path: '/filebrowser/{entity}/{id}', name: 'file-browser')]
    public function index(
        Request $request,
        string $entity,
        int $id,
        PagesRepository $pagesRepository,
        TravelRepository $travelRepository,
        CategoryRepository $categoryRepository,
        PopupsRepository $popupsRepository,
        ArticlesRepository $articlesRepository
    ) {
        $fieldName = $request->query->get('CKEditor');
        $array = explode("_", $fieldName);
        $section = end($array);
        $size = "travel_thumbnail_" . $section;
        if ($array[0] == "pages") {
            $repository = $pagesRepository;
        }
        if ($array[0] == "travel") {
            $repository = $travelRepository;
        }
        if ($array[0] == "category") {
            $repository = $categoryRepository;
        }
        if ($array[0] == "articles") {
            $repository = $articlesRepository;
        }
        if ($array[0] == "popups") {
            $repository = $popupsRepository;
        }
        $element = $repository->find($id);

        return $this->render('admin/filebrowser/index.html.twig', [
            'element' => $element,
            'type' => $array[0],
            'size' => $size
        ]);
    }

    #[Route(path: '/filebrowser/upload/{entity}/{id}', name: 'file-browser-upload')]
    public function upload(
        Request $request,
        UploadHelper $uploadHelper,
        MediaRepository $mediaRepository,
        EntityManagerInterface $em,
        $entity,
        Travel $travel
    ) {
        //dd($request);
        $uploadedFile = $request->files->get('upload');
        $filename = $uploadHelper->uploadTravelImage($uploadedFile);
        $function_number = $request->query->get('CKEditorFuncNum');

        $url = $request->getSchemeAndHttpHost() . '/uploads/travel/' . $filename;

        $medium = new Media();
        $medium->setType("travel");
        $medium->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $medium->setPath($filename);
        $medium->setFilename($filename);
        $medium->addTravel($travel);
        $em->persist($medium);
        $em->flush();

        $message = "error?";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');

        if ($function_number == 0) {
            $number = '0';
        } else {
            $number = $function_number;
        }

        $content = $this->renderView('admin/filebrowser/upload.html.twig', [
            'function_number' => $number,
            'url' => $url,
            'message' => ""
        ]);

        $response->setContent($content);

        return $response;
    }
}
