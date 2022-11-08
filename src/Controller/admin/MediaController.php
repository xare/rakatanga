<?php

namespace App\Controller\admin;

use App\Entity\Media;
use App\Entity\Travel;
use App\Entity\Category;
use App\Form\MediaType;
use App\Controller\admin\MainadminController;
use App\Repository\ArticlesRepository;
use App\Repository\CategoryRepository;
use App\Repository\MediaRepository;
use App\Repository\PagesRepository;
use App\Repository\TravelRepository;
use App\Repository\PopupsRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as FileFile;

/**
 * @Route("/admin/media")
 */
class MediaController extends MainadminController
{
    protected $parameterBag;
    private $liipCache;
    private $entityManager;

    public function __construct(
        ParameterBagInterface $parameterBag,
        CacheManager $liipCache,
        EntityManagerInterface $entityManager
    ) {
        $this->parameterBag = $parameterBag;
        $this->imagineCacheManager = $liipCache;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="media_index", methods={"GET","POST"})
     */
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        UploadHelper $uploadHelper
    ): Response {
        $medium = new Media();
        if (!$pageNumber = $request->query->get('page')) {
            $pageNumber = 0;
        }
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        $travelRepository = $this->entityManager->getRepository(Travel::class);
        $categoryRepository = $this->entityManager->getRepository(Category::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $travel = new Travel();
            $medium = $form->getData();
        
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['file']->getData();
        
            if ($uploadedFile) {
                $newFilename = $uploadHelper->uploadMedia($uploadedFile);
                $medium->setFilename($newFilename);
            }

            $medium->setPath($this->getParameter('kernel.project_dir') . '/public' . $uploadHelper->getPublicPath($medium->getMediaPath()));
            $medium->addTravel($travel);

            $this->entityManager->persist($medium);
            $this->entityManager->flush();
            // return a blank form after success
            if ($request->isXmlHttpRequest()) {
                return $this->render('admin/media/_mediaRow.html.twig', [
                    'medium' => $medium
                ]);
            }

            $this->addFlash('notice', 'Media Uploaded!!');
            return $this->redirectToRoute('media_index');
        }

        $query = $mediaRepository->listAll();
        $media = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $travels = $travelRepository->findAll();
        $countries = $categoryRepository->findBy([
            'type' => 'country'
        ]);

        if ($request->isXmlHttpRequest()) {
            $html = $this->renderView('admin/media/_dynamic_form.html.twig', [
                'form' => $form->createView()
            ]);
            return new Response($html, 400);
        }
        return $this->render('admin/media/index.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
            'pageNumber' => $pageNumber,
            'travels' => $travels,
            'countries' => $countries
        ]);
    }

    /**
     * @Route("/new", name="media_new", methods={"GET","POST"})
     */
    public function new(Request $request, UploadHelper $uploadHelper): Response
    {
        $media = $request->files->get('media');
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['file']->getData();
            if ($uploadedFile) {
                $newFilename = $uploadHelper->uploadMedia($uploadedFile);

                $medium->setFilename($newFilename);
            }
            $medium->setPath($this->getParameter('kernel.project_dir') . '/public' . $uploadHelper->getPublicPath($medium->getMediaPath()));

            $this->entityManager->persist($medium);
            $this->entityManager->flush();

            //return $this->redirectToRoute('media_index');
            return $this->render('admin/media/edit.html.twig', [
                'medium' => $medium,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('admin/media/new.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/loadMultiplFiles", name="load_files", methods={"GET","POST"})
     */
    public function loadMultipleFiles()
    {

        return $this->render('admin/media/_form_dropzone.html.twig', []);
    }

    /**
     * @Route("/uploadMultipleFiles" , name="upload_multiple_files", methods={"POST"})
     */
    public function uploadMultipleFiles(
        Request $request,
        UploadHelper $uploadHelper,
        ValidatorInterface $validator,
        CategoryRepository $categoryRepository,
        TravelRepository $travelRepository,
        MediaRepository $mediaRepository,
        EntityManagerInterface $em,
        PagesRepository $pagesRepository,
        PopupsRepository $popupsRepository,
        ArticlesRepository $articlesRepository
    ) {
        $uploadedFile = $request->files->get('files');

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload'
                ]),
                new File([
                    'maxSize' => '50M',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf'
                    ]
                ])
            ]
        );
        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            /* $violation = $violations[0];
            $this->addFlash('error', $violation->getMessage()); */

            return $this->json($violations, 400);
        }
        $renderArray = [];
        $filename = $uploadHelper->uploadMedia($uploadedFile, '');

        $medium = new Media();
        $medium->setType($request->get('type'));
        $medium->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $medium->setPath($filename);
        $medium->setFilename($filename);
        $medium->setIsGallery(true);
        $medium->setIsYTVideo(false);
        $entity = $request->get('type');

        $repository = $entity . 'Repository';
        $entityRepository = $$repository;
        $element = $entityRepository->find($request->get($entity));
        if ($request->get('dropzone_upload') == "yes") {
            if ($entity == "travel") {
                $medium->addTravel($element);
            }
            if ($entity == "category") {
                $medium->addCategory($element);
            }
            if ($entity == "pages") {
                $medium->addPage($element);
            }
            if ($entity == "articles") {
                $medium->addArticle($element);
            }
            if ($entity == "popups") {
                $medium->addPopup($element);
            }
            
            $em->persist($medium);
            $em->flush();
            $renderArray['count'] = count($element->getMedia()) - 1;
            $renderArray[$entity] = $element;
            $renderArray['medium'] = $medium;
            return $this->render('admin/shared/forms/_add_media_item.html.twig', $renderArray);
        }



        return $this->json($medium, 200, [], ['groups' => 'main']);
    }

    /**
     * @Route("uploadYoutubeThumbnail", 
     * name="upload-youtube-thumbnail",
     * options = { "expose" = true },
     * methods = {"POST"} )
     */
    public function uploadYoutubeThumbnail(
        Request $request,
        UploadHelper $uploadHelper,
        PagesRepository $PagesRepository,
        TravelRepository $TravelRepository,
        ArticlesRepository $ArticlesRepository,
        EntityManagerInterface $em){
        $ytCode = $request->request->get('ytcode');
        $uploadedYtThumb = $uploadHelper->uploadYoutubeThumb($ytCode);
        $medium = new Media();
        $medium->setType($request->request->get('entityType'));
        $medium->setName(pathinfo($ytCode, PATHINFO_FILENAME));
        $medium->setPath(pathinfo($uploadedYtThumb, PATHINFO_FILENAME)
                            .'.'
                            .pathinfo($uploadedYtThumb, PATHINFO_EXTENSION));
        $medium->setFilename(pathinfo($uploadedYtThumb, PATHINFO_FILENAME)
                            .'.'
                        .pathinfo($uploadedYtThumb, PATHINFO_EXTENSION));
        $medium->setIsGallery(true);
        $medium->setIsYTVideo(true);
        $entity = $request->request->get('entityType');
        $repository = $entity . 'Repository';
        $entityRepository = $$repository;
        $element = $entityRepository->find($request->request->get('entityId'));
        if ($entity == "Travel") {
            $medium->addTravel($element);
        }
        if ($entity == "Category") {
            $medium->addCategory($element);
        }
        if ($entity == "Pages") {
            $medium->addPage($element);
        }
        if ($entity == "Articles") {
            $medium->addArticle($element);
        }
        $em->persist($medium);
        $em->flush();
        $renderArray = [];
        $renderArray['count'] = count($element->getMedia()) - 1;
        $renderArray[$entity] = $element;
        $renderArray['medium'] = $medium;
        $html =  $this->renderView('admin/shared/forms/_add_media_item.html.twig', $renderArray);
        return $this->json([
            'code' => 200,
            'html'=>$html
        ],200);
        //return new response ('uploaded');
    }
    /**
     * @Route("/{id}/assign",
     *  options = { "expose" = true }, 
     *  name="assign_main_photo_entity", 
     *  methods={"POST"})
     */
    public function assign(
        Request $request,
        Media $media,
        MediaRepository $mediaRepository,
        TravelRepository $travelRepository,
        PagesRepository $pagesRepository,
        ArticlesRepository $articleRepository,
        EntityManagerInterface $em
    ): Response {

        $entityId = $request->request->get('entityId');
        $entityType = $request->request->get('entityType');
        //$media = $mediaRepository->find($media->getId());
        if($entityType=='Travel') {
            $travel = $travelRepository->find($entityId);
            $travel->setMainPhoto($media);
            $em->persist($travel);
        }
        if($entityType=='Pages') {
            $page = $pagesRepository->find($entityId);
            $page->setMainPhoto($media);
            $em->persist($page);
        }
        if($entityType=='Articles') {
            $article = $articleRepository->find($entityId);
            $article->setMainPhoto($media);
            $em->persist($article);
        }

        
        $em->flush();
        return new Response($entityId . ', ' . $media->getId());
    }
    /**
     * @Route("/items", methods="GET", name="media_items")
     */
    public function getMediaItems(Request $request, MediaRepository $mediaRepository, PaginatorInterface $paginator)
    {

        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allMedia = $mediaRepository->listIndex();
        $count = count($allMedia);
        $properties = [
            'id',
            'type',
            'name',
            'path',
            'filename',
            'mediaPath'
        ];
        /* $query = $mediaRepository->listAll(); */
        $defaultPage = 1;
        $itemsPerPage = 10;
        /* $media = $paginator->paginate(
            $query,
            $request->query->getInt('page',$defaultPage),
            $itemsPerPage
        ); */

        $resolvedPath = $this->imagineCacheManager->getBrowserPath('/public/uploads/media/bullet-60c6172b9e8fa.jpg', 'squared_thumbnail_small');
        $items = [];
        $i = 0;
        foreach ($allMedia as $mediaItem) {
            $items[$i] = $mediaItem;
            $items[$i]['totalPath'] = $this->imagineCacheManager->getBrowserPath('media/' . $mediaItem['path'], 'squared_thumbnail_small');
            //$items[$i]['totalPath'] = '/uploads/media/cache/squared_thumbnail_small/media/'.$mediaItem['path'];

            $i++;
        }

        $data = [
            'items' => $items,
            'count' => $count,
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
            'resolvedPath' => $resolvedPath
        ];
        return $this->json($data, 200, [], ['groups' => 'main']);
    }
    /**
     * @Route("/show/{id}", name="media_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Media $medium): Response
    {
        return $this->render('admin/media/show.html.twig', [
            'medium' => $medium,
        ]);
    }
    /**
     * @Route("/removeMedia",
     * options = { "expose" = true }, 
     * name="remove-media", 
     * methods={"POST"})
     */

    public function removeMedia(
        Request $request,
        EntityManagerInterface $em,
        TravelRepository $TravelRepository,
        PagesRepository $PagesRepository,
        MediaRepository $mediaRepository
    ) {
        $mediaId = $request->request->get('mediaId');
        $entityId = $request->request->get('entityId');
        $entity = $request->request->get('entityType');

        $repository = $entity . 'Repository';
        $entityRepository = $$repository;
        $element = $entityRepository->find($entityId);
        $medium = $mediaRepository->find($mediaId);

        $element->removeMedium($medium);

        $em->persist($element);
        $em->flush();

        return new Response('test ' . $mediaId . ' - ' . $entityId);
    }

    /**
     * @Route("/gallerifyMedia",
     * options= { "expose" = true },
     * name="gallerify_media",
     * methods={"POST"})
     */
    public function gallerifyMedia(
        Request $request,
        EntityManagerInterface $em,
        TravelRepository $travelRepository,
        MediaRepository $mediaRepository
    ) {
        $mediaId = $request->request->get('mediaId');
        $entityId = $request->request->get('entityId');

        $travel = $travelRepository->find($entityId);
        $medium = $mediaRepository->find($mediaId);

        $medium->setIsGallery(true);
        $em->persist($medium);
        $em->flush();

        return new Response('gallerifyMedia');
    }
    /**
     * @Route("/unGallerifyMedia",
     * options= { "expose" = true },
     * name="ungallerify_media",
     * methods={"POST"})
     */
    public function unGallerifyMedia(
        Request $request,
        EntityManagerInterface $em,
        TravelRepository $travelRepository,
        MediaRepository $mediaRepository
    ) {
        $mediaId = $request->request->get('mediaId');
        $entityId = $request->request->get('entityId');

        $travel = $travelRepository->find($entityId);
        $medium = $mediaRepository->find($mediaId);

        $medium->setIsGallery(false);
        $em->persist($medium);
        $em->flush();

        return new Response('ungallerifyMedia');
    }

    /**
     * @Route("/{id}/edit", name="media_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Media $medium, UploadHelper $uploadHelper): Response
    {
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['file']->getData();
            if ($uploadedFile) {
                $newFilename = $uploadHelper->uploadMedia($uploadedFile);
                $medium->setFilename($newFilename);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('media_index');
        }

        return $this->render('admin/media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="media_delete", methods={"POST"},requirements={"id"="\d+"})
     */
    public function delete(
        Request $request,
        Media $medium,
        UploadHelper $uploadHelper,
        EntityManagerInterface $em
    ): Response {

        /*  if (
            $this->isCsrfTokenValid(
                'delete'.$medium->getId(), 
                $request->request->get('_token')
                )) { */
        $em->remove($medium);
        $em->flush();
        $uploadHelper->deleteFile($medium->getPath(), true);
        /* } */
        return new Response(null);
        //return $this->redirectToRoute('media_index'); 
    }

    
    /**
     * @Route("/api/delete/{id}", name="api_media_delete", methods={"DELETE"})
     */
    public function apiDeleteMedia(Media $media)
    {
        $this->entityManager->remove($media);
        $this->entityManager->flush();
        return new Response(null, 204);
    }

    /**
     * @Route("/api/new", name="api_media_new", methods={"POST"})
     */
    public function apiNewMedia(Request $request, UploadHelper $uploadHelper)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $form = $this->createForm(MediaType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);
        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);

            return $this->createApiResponse([
                'errors' => $errors
            ], 400);
        }

        /** @var Media $medium */
        $medium = $form->getData();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form['file']->getData();
        if ($uploadedFile) {
            $newFilename = $uploadHelper->uploadMedia($uploadedFile);

            $medium->setFilename($newFilename);
        }
        $medium->setPath($this->getParameter('kernel.project_dir') . '/public' . $uploadHelper->getPublicPath($medium->getMediaPath()));
        $this->entityManager->persist($medium);
        $this->entityManager->flush();

        $apiModel = $this->createMediaApiModel($medium);

        $response = new Response(null, 204);

        $response->headers->set(
            'Location',
            $this->generateUrl('media_get', ['id' => $medium->getId()])
        );

        return $response;
    }

    /**
     * @Route("/ajax/openVideoForm/",
     * name="open-video-form",
     * options= { "expose" = true },
     * methods={"GET"}
     * )
     */
    public function openVideoForm(Request $request){
        
        $html = $this->renderView('admin/media/_open_video_form.html.twig');
        return $this->json( [
            'code' => 200,
            'message' => $html,
            'html' => $html
        ] );
    }
    /**
     * @Route("/addVideo/",
     * name="addVideo",
     * options= { "expose" = true },
     * methods={"POST"}
     * )
     */
    public function addVideo(Request $request){
        
        return $this->json( [
            'code' => 200,
            'message' => $request->request->get('id'),
            'imgSrc' => "https://img.youtube.com/vi/".$request->request->get('id')."/sddefault.jpg"
        ] );
    }
}
