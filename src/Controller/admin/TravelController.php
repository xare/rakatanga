<?php

namespace App\Controller\admin;

use App\Entity\Travel;
use App\Entity\Infodocs;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\admin\MainadminController;
use App\Entity\Category;
use App\Entity\Lang;
use App\Entity\Media;
use App\Entity\TravelTranslation;
use App\Form\LoadTravelCsvType;
use App\Repository\CategoryRepository;
use App\Repository\InfodocsRepository;
use App\Repository\LangRepository;
use App\Repository\MediaRepository;
use App\Service\slugifyHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Reader;
use Symfony\Component\Form\FileUploadError;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;

#[Route(path: '/admin/travel')]
class TravelController extends MainadminController
{
    private EntityManagerInterface $entityManager;
    private TravelRepository $travelRepository;
    public function __construct(EntityManagerInterface $entityManager, TravelRepository $travelRepository, private string $csvDirectory )
    {
        $this->entityManager = $entityManager;
        $this->travelRepository = $travelRepository;
    }
    #[Route(path: '/', name: 'travel_index', methods: ['GET'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        TravelRepository $travelRepository
    ): Response {
        $this->redirectToLogin($request);
        $count = count($travelRepository->findAll());
        $query = $travelRepository->listAll();
        $travels = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/travel/index.html.twig', [
            'travels' => $travels,
            'count' => $count

        ]);
    }

    #[Route(path: '/search/{categoryName}', name: 'travel_by_category', methods: ['GET', 'POST'])]
    public function searchByCategoryName(
        Request $request,
        string $categoryName,
        TravelRepository $travelRepository,
        PaginatorInterface $paginator
    ) {
        $travels = $paginator->paginate(
            $travelRepository->listTravelByCategory($categoryName),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/travel/index.html.twig', [
            'travels' => $travels,
            'count' => 10
        ]);
    }

    #[Route(path: '/new', name: 'travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LangRepository $langRepository, MediaRepository $mediaRepository): Response
    {
        $plantilla = $this->travelRepository->findBy(['main_title' => "PLANTILLA"]);
        return $this->redirectToRoute('travel_new_from_template',['travel' => $plantilla[0]->getId()]);
        $this->redirectToLogin($request);
        $langs = $langRepository->findAll();
        $media = $mediaRepository->findAll();
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime();
            $travel->setDate($now);
            $this->entityManager->persist($travel);
            $this->entityManager->flush();

            return $this->redirectToRoute('travel_index');
        }

        return $this->render('admin/travel/new.html.twig', [
            'travel' => $travel,
            'langs' => $langs,
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/new_from_template/{travel}', name: 'travel_new_from_template', methods: ['GET', 'POST'])]
     public function newFromTemplate(
        Travel $travel,
        TravelRepository $travelRepository, 
        slugifyHelper $slugifyHelper, 
        LangRepository $langRepository,
        CategoryRepository $categoryRepository ){
        $langs = $langRepository->findAll();
        $travelData = $travelRepository->findBy(['main_title' => "PLANTILLA"]);

        $newTravel = new Travel();
        $newTravel->setMainTitle("NUEVO VIAJE DESDE PLANTILLA");
        $newTravel->setStatus($travel->getStatus());
        $now = new \DateTime();
        $newTravel->setDate($now);
        $newTravel->setDuration($travel->getDuration());
        $newTravel->setCategory($travel->getCategory());
        foreach ($travel->getTravelTranslation() as $travelTranslation){
            $newTravelTranslation = new TravelTranslation();
            $newTravelTranslation->setLang($travelTranslation->getLang());
            $newTravelTranslation->setTitle("Nuevo viaje desde Plantilla");
            $newTravelTranslation->setIntro($travelTranslation->getIntro());
            $newTravelTranslation->setDescription($travelTranslation->getDescription());
            $newTravelTranslation->setUrl($slugifyHelper->slugify($travelTranslation->getTitle()));
            $newTravel->addTravelTranslation($newTravelTranslation);
            $this->entityManager->persist($newTravelTranslation);
        }
        $this->entityManager->persist($newTravel);
        $this->entityManager->flush();
        return $this->redirectToRoute('travel_edit', [ 'id' => $newTravel->getId() ]);
     }

    #[Route(path: '/items', methods: 'GET', name: 'travel_items')]
    public function getItems(Request $request, PaginatorInterface $paginator, TravelRepository $travelRepository)
    {

        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allTravels = $travelRepository->findAll();

        $count = count($allTravels);
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
        $data = [
            'items' => $allTravels,
            'count' => $count,
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
        ];
        return $this->json($data, 200, [], ['groups' => 'main']);
        /* return $this->json($mediaRepository->findAll()); */
    }

    #[Route(path: '/loadFromCsv', name: 'load_travels', methods: ['GET', 'POST'])]
    public function LoadFromCsv(Request $request, UploadHelper $uploadHelper, LangRepository $langRepository)
    {
        $form = $this->createForm(LoadTravelCsvType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csvFile')->getData();
            if ($csvFile) {
                $csvUploadedFile = $uploadHelper->upload($csvFile);
                $reader = Reader::createFromPath($this->csvDirectory . '/' . $csvUploadedFile, 'r');
                $reader->setHeaderOffset(0);
                $records = $reader->getRecords();

                $langArray = $langRepository->findAll();
                foreach ($records as $record) {
                    $travel = new Travel();
                    $travel->setDuration($record["duree"]);
                    $travel->setLevel($record["niveau"]);
                    $travel->setStatus($record["statut"]);
                    $travel->setDuration($record["duree"]);
                    $travel->setDate(new \DateTime());
                    $travel->setMainTitle($record["titre_en"]);
                    foreach ($langArray as $lang) {
                        $travelTranslation = new TravelTranslation();
                        $travelTranslation->setLang($lang);

                        $travelTranslation->setTitle($record['titre_' . $lang->getIsoCode()]);

                        $travelTranslation->setUrl($record["url_" . $lang->getIsoCode()]);
                        $travelTranslation->setSummary($record["resume_" . $lang->getIsoCode()]);
                        $travelTranslation->setIntro($record["intro_" . $lang->getIsoCode()]);
                        $travelTranslation->setDescription($record["description_" . $lang->getIsoCode()]);
                        $travelTranslation->setItinerary($record["itineraire_" . $lang->getIsoCode()]);
                        $travelTranslation->setPracticalInfo($record["pratique_" . $lang->getIsoCode()]);
                        $travel->addTravelTranslation($travelTranslation);
                    }


                    $this->entityManager->persist($travel);
                    $this->entityManager->flush();
                }
            }
            //return $this->redirectToRoute('travel_index');
        }
        // 1. If no file has been submitted show form.
        return $this->render('admin/travel/_form_add_from_csv.html.twig', [
            'form' => $form->createView()
        ]);


        // 2. When a file has been submitted process it.
    }
    #[Route(path: '/{id}', name: 'travel_show', methods: ['GET'])]
    public function show(Travel $travel): Response
    {
        return $this->render('admin/travel/show.html.twig', [
            'travel' => $travel,
        ]);
    }

    #[Route(path: '/{id}/assign', options: ['expose' => true], name: 'assign_main_photo_travel', methods: ['POST'])]
    public function assign(
        Request $request,
        Travel $travel,
        MediaRepository $mediaRepository,
    ): Response {

        $mediaId = $request->request->get('mediaId');
        $media = $mediaRepository->find($mediaId);
        
        $travel->setMainPhoto($media);
        $this->entityManager->persist($travel);
        $this->entityManager->flush();
        return new Response($travel->getId() . ', ' . $mediaId);
    }

    #[Route(path: '/{id}/edit', name: 'travel_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Travel $travel,
        LangRepository $langRepository,
        slugifyHelper $slugifyHelper
    ): Response {

        $langs = $langRepository->findAll();
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $travelData = $form->getData();
            foreach ($travelData->getTravelTranslation() as $travelTranslation) {
                $travelTranslation->setUrl($slugifyHelper->slugify($travelTranslation->getTitle()));
            }
            foreach ($travel->getMedia() as $medium) {
                $travel->addMedium($medium);
            }
            $this->entityManager->persist($travel);
            $this->entityManager->flush();

            //return $this->redirectToRoute('travel_index');
        }

        return $this->render('admin/travel/edit.html.twig', [
            'langs' => $langs,
            'travel' => $travel,
            'media' => $travel->getMedia(),
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/{id}', name: 'travel_delete', methods: ['POST'])]
    public function delete(Request $request, Travel $travel): Response
    {
        if ($this->isCsrfTokenValid('delete' . $travel->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($travel);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('travel_index');
    }

    #[Route(path: '/uploadocumentationToTravel/{travel}/', name: 'upload_documentation_to_travel', methods: ['POST'])]
    public function uploadocumentationToTravel(
        Request $request,
        UploadHelper $uploadHelper,
        ValidatorInterface $validator,
        Travel $travel,
        TravelRepository $travelRepository,
        InfodocsRepository $infodocsRepository
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
        $filename = $uploadHelper->uploadInfodocs($uploadedFile);

        $infodoc = new Infodocs();
        $infodoc->setTitle(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $infodoc->setFilename($filename);
        $infodoc->setOriginalfilename($filename);
        $infodoc->addTravel($travel);
        $this->entityManager->persist($infodoc);
        $this->entityManager->flush();

        return $this->render('admin/travel/_list_infodocs.html.twig',['travel' => $travel]);
        
    }

    #[Route(path: '/ajax/removeInfoDocs/{travel}/{infodoc}', options: ['expose' => true], name: 'ajax-remove-infodocs', methods: 'GET')]
    public function removeInfodocs(
        Travel $travel, 
        Infodocs $infodoc,
        UploadHelper $uploadHelper
        ){
            $uploadHelper->deleteFile($infodoc->getPath(), true);
            $travel->removeInfodoc($infodoc);
            $this->entityManager->remove($infodoc);
            $this->entityManager->flush();
            
            return $this->render('admin/travel/_list_infodocs.html.twig',['travel' => $travel]);
    }
}
