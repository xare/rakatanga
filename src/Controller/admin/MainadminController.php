<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Api\MediaApiModel;
use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class MainadminController extends AbstractController
{
    public function __construct(private \Symfony\Component\Serializer\Serializer $serializer)
    {
    }
    public function redirectToLogin()
    {
        $user = $this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_login');
        } else
        {
            return;
        }
    }

    /**
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function createApiResponse($data, $statusCode = 200)
    {
        $json = $this->serializer
            ->serialize($data, 'json');

        return new JsonResponse($json, $statusCode, [], true);
    }

    /**
     * Returns an associative array of validation errors
     *
     * {
     *     'firstName': 'This value is required',
     *     'subForm': {
     *         'someField': 'Invalid value'
     *     }
     * }
     *
     * @param FormInterface $form
     * @return array|string
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        foreach ($form->getErrors() as $error) {
            // only supporting 1 error per field
            // and not supporting a "field" with errors, that has more
            // fields with errors below it
            return $error->getMessage();
        }

        $errors = array();
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childError = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childError;
                }
            }
        }

        return $errors;
    }

    /**
     * Turns a Media into a MediaApiModel for the API.
     *
     * This could be moved into a service if it needed to be
     * re-used elsewhere.
     *
     * @param Media $media
     * @return MediaApiModel
     */
    protected function createMediaApiModel(Media $media)
    {
        $model = new MediaApiModel();
        $model->id = $media->getId();
        $model->name = $media->getName();
        /* $model->itemLabel = $this->get('translator')
            ->trans($media->getItemLabel());
        $model->totalItems = $media->getTotalItems(); */

        $selfUrl = $this->generateUrl(
            'media_get',
            ['id' => $media->getId()]
        );
        $model->addLink('_self', $selfUrl);

        return $model;
    }

    /**
     * @return MediaApiModel[]
     */
    protected function findAllTypeMediaModels(MediaRepository $mediaRepository)
    {
        $media = $mediaRepository->findBy( ['type' => $this->getType()] );

        $models = [];
        foreach ($media as $medium) {
            $models[] = $this->createMediaApiModel($medium);
        }

        return $models;
    }
}