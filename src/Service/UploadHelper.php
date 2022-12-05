<?php

namespace App\Service;

use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadHelper
{
    public const MEDIA_FOLDER = 'media';
    public const USER_FILE = 'user_file';
    public const DOCUMENT = 'document';
    public const TRAVEL = 'travel';
    public const INVOICES = 'invoices';
    public const INFODOCS = 'infodocs';
    private $requestStackContext;
    private $slugger;
    private $filesystem;
    private $logger;
    private $uploadedAssetsBaseUrl;
    protected $parameterBag;

    public function __construct(
        FilesystemOperator $publicUploadsFilesystem,
        FileSystemOperator $privateUploadsFilesystem,
        string $targetDirectory,
        RequestStackContext $requestStackContext,
        SluggerInterface $slugger,
        LoggerInterface $logger,
        string $uploadedAssetsBaseUrl,
        ParameterBagInterface $parameterBag
        ) {
        $this->filesystem = $publicUploadsFilesystem;
        $this->privateFilesystem = $privateUploadsFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->slugger = $slugger;
        $this->targetDirectory = $targetDirectory;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
        $this->parameterBag = $parameterBag;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function uploadMedia(File $file, ?string $existingFilename): string
    {
        $newFilename = $this->uploadFile($file, self::MEDIA_FOLDER, true);

        if ($existingFilename) {
            try {
                $result = $this->filesystem->delete(self::MEDIA_FOLDER.'/'.$existingFilename);
                if ($result === false) {
                    throw new \Exception(sprintf('Could not delete old uploaded file "%s"', $existingFilename));
                }
            } catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('Old uploaded file "%s" was missing when trying to delete', $existingFilename));
            }
        }

        return $newFilename;
    }

    public function uploadYoutubeThumb(string $ytCode)
    {
        $ytThumb = file_get_contents('https://img.youtube.com/vi/'.$ytCode.'/sddefault.jpg');

        $destination = str_replace('\\', '/', $this->parameterBag->get('kernel.project_dir').'/public'.$this->publicAssetBaseUrl);

        $fullPath = $destination.'/'.self::MEDIA_FOLDER.'/'.$ytCode.'-'.uniqid().'.jpg';
        file_put_contents($fullPath, $ytThumb);

        return $fullPath;
    }

    public function getPublicPath(string $path): string
    {
        // needed if you deploy under a subdirectory
        return $this->requestStackContext
        ->getBasePath().$this->publicAssetBaseUrl.'/'.$path;
    }

    /**
     * Undocumented deleteFile.
     */
    public function deleteFile(string $path, bool $isPublic): bool
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $result = $filesystem->delete($path);

        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        } else {
            return true;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function uploadDocument(File $file): string
    {
        return $this->uploadFile($file, self::DOCUMENT, false);
    }

    public function uploadTravelImage(File $file): string
    {
        return $this->uploadFile($file, self::TRAVEL, true);
    }

    public function uploadInfodocs(File $file): string
    {
        return $this->uploadFile($file, self::INFODOCS, true);
    }

    private function uploadFile(File $file, string $directory, bool $isPublic)
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $stream = fopen($file->getPathname(), 'r');

        $result = $filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }

    /**
     * @return resource
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $resource = $filesystem->readStream($path);

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }
}
