<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\DefaultSeoPageCannotBeDeletedException;

class SeoPageFacade
{
    public const string IMAGE_TYPE_OG = 'og';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository $seoPageRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFactory $seoPageFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly EntityManagerInterface $em,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly SeoPageRepository $seoPageRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly SeoPageFactory $seoPageFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function create(SeoPageData $seoPageData): SeoPage
    {
        $seoPage = $this->seoPageFactory->create($seoPageData);

        $this->em->persist($seoPage);
        $this->em->flush();

        $this->imageFacade->manageImages($seoPage, $seoPageData->seoOgImage, self::IMAGE_TYPE_OG);

        return $seoPage;
    }

    /**
     * @param int $seoPageId
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function edit(int $seoPageId, SeoPageData $seoPageData): SeoPage
    {
        $seoPage = $this->seoPageRepository->getById($seoPageId);

        $seoPage->edit($seoPageData);

        $this->em->flush();

        $this->imageFacade->manageImages($seoPage, $seoPageData->seoOgImage, self::IMAGE_TYPE_OG);

        return $seoPage;
    }

    /**
     * @param int $seoPageId
     */
    public function delete(int $seoPageId): void
    {
        $seoPage = $this->seoPageRepository->getById($seoPageId);

        if ($seoPage->isDefaultPage()) {
            throw new DefaultSeoPageCannotBeDeletedException();
        }

        $this->em->remove($seoPage);
        $this->em->flush();
    }

    /**
     * @param int $seoPageId
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function getById(int $seoPageId): SeoPage
    {
        return $this->seoPageRepository->getById($seoPageId);
    }

    /**
     * @param int $domainId
     * @param string $pageSlug
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function getByDomainIdAndPageSlug(int $domainId, string $pageSlug): SeoPage
    {
        return $this->seoPageRepository->getByDomainIdAndPageSlug($domainId, $pageSlug);
    }

    /**
     * @param int $domainId
     * @param string $pageSlug
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null
     */
    public function findByDomainIdAndPageSlug(int $domainId, string $pageSlug): ?SeoPage
    {
        return $this->seoPageRepository->findByDomainIdAndPageSlug($domainId, $pageSlug);
    }
}
