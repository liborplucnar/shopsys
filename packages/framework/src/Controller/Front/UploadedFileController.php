<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadedFileController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse
     */
    public function downloadAction(int $uploadedFileId, string $uploadedFilename): DownloadFileResponse
    {
        try {
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFilename, $uploadedFileId);
            $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new DownloadFileResponse(
                $uploadedFile->getNameWithExtension(),
                $this->filesystem->read($filePath),
                $this->filesystem->mimeType($filePath),
            );
        } catch (Exception $e) {
            $message = sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId);

            throw new NotFoundHttpException($message, $e, 0, ['X-Accel-Redirect' => '@storefront']);
        }
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function viewAction(int $uploadedFileId, string $uploadedFilename): StreamedResponse
    {
        try {
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFilename, $uploadedFileId);
            $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new StreamedResponse(function () use ($filePath) {
                $stream = $this->filesystem->readStream($filePath);
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $this->filesystem->mimeType($filePath),
                'Content-Disposition' => sprintf('inline; filename="%s"', $uploadedFile->getNameWithExtension()),
            ]);
        } catch (Exception $e) {
            $message = sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId);

            throw new NotFoundHttpException($message, $e, 0, ['X-Accel-Redirect' => '@storefront']);
        }
    }

    /**
     * @param string $uploadedFilename
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    protected function getByIdSlugAndExtension(
        string $uploadedFilename,
        int $uploadedFileId,
    ): UploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        $uploadedFile = $this->uploadedFileFacade->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
        );

        return $uploadedFile;
    }
}
