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
    public const TRANSFER_DOCUMENT= 'transfer_document';
    public const TRAVEL = 'travel';
    public const INVOICES = 'invoices';
    public const INFODOCS = 'infodocs';
    private $filesystem;
    private $privateFilesystem;
    private $publicAssetBaseUrl;

    public function __construct(
        private FilesystemOperator $publicUploadsFilesystem,
        private FileSystemOperator $privateUploadsFilesystem,
        private string $targetDirectory,
        private RequestStackContext $requestStackContext,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
        private string $uploadedAssetsBaseUrl,
        protected ParameterBagInterface $parameterBag,
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
        } catch (FileException) {
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
            } catch (FileNotFoundException) {
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
        dump($filesystem);
        dump($path);
        if ( ! file_exists($path)) {
            dump("file does not exist: " . $path);
            $this->logger->error(sprintf('File at %s does not exist', $path));
        } else {
            dump("file does exist: " . $path);
            $this->logger->notice(sprintf('File at %s does exist', $path));
        }
        try {
            unlink($path);
            return true;
        } catch (\Throwable $e) {
            dump($e);
            return false;
        }
        /* try {
            $result = $filesystem->delete($path);
            if ($result !== null) {
                if ($result) {
                    $this->logger->info(sprintf('File "%s" was deleted.', $path));
                } else {
                    $this->logger->warning(sprintf('File "%s" could not be deleted.', $path));
                }
                return $result;
            } else {
                throw new \RuntimeException(sprintf('Error deleting file "%s". Result is null.', $path));
            }
        } catch(\Throwable $e) {
            $this->logger->error(sprintf('An error occurred while deleting file "%s": %s', $path, $e->getMessage()));
            return false;
        } */
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function uploadDocument(File $file): string
    {
        return $this->uploadFile($file, self::DOCUMENT, false);
    }
    public function uploadTransferDocument(File $file): string
    {
        return $this->uploadFile($file, self::TRANSFER_DOCUMENT, false);
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
