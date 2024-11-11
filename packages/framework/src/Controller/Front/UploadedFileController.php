<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $uploadedFile = $this->getByIdSlugAndExtension($uploadedFilename, $uploadedFileId);
        $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new DownloadFileResponse(
            $uploadedFile->getNameWithExtension(),
            $this->filesystem->read($filePath),
            $this->filesystem->mimeType($filePath),
        );
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function viewAction(int $uploadedFileId, string $uploadedFilename): StreamedResponse
    {
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
