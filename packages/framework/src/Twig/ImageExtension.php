<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Sinergi\BrowserDetector\Browser;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    protected const NOIMAGE_FILENAME = 'noimage.png';
    protected const PLACEHOLDER_FILENAME = 'placeholder.gif';
    protected const BROWSERS_WITHOUT_NATIVE_LAZY_LOAD = [
        Browser::SAFARI,
        Browser::IE,
        Browser::OPERA_MINI,
    ];
    protected const NON_HTML_ATTRIBUTES = [
        'type',
        'lazy',
    ];

    protected string $frontDesignImageUrlPrefix;

    protected Browser $browser;

    /**
     * @param string $frontDesignImageUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Twig\Environment $twigEnvironment
     * @param bool $isLazyLoadEnabled
     */
    public function __construct(
        $frontDesignImageUrlPrefix,
        protected readonly Domain $domain,
        protected readonly ImageLocator $imageLocator,
        protected readonly ImageFacade $imageFacade,
        protected readonly Environment $twigEnvironment,
        protected readonly bool $isLazyLoadEnabled = false,
    ) {
        $this->frontDesignImageUrlPrefix = rtrim($frontDesignImageUrlPrefix, '/');
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('image', [$this, 'getImageHtml'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param string|null $type
     * @return bool
     */
    public function imageExists($imageOrEntity, ?string $type = null): bool
    {
        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $type);
        } catch (ImageNotFoundException $e) {
            return false;
        }

        return $this->imageLocator->imageExists($image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param string|null $type
     * @return string
     */
    protected function getImageUrl($imageOrEntity, ?string $type = null): string
    {
        try {
            return $this->imageFacade->getImageUrl(
                $this->domain->getCurrentDomainConfig(),
                $imageOrEntity,
                $type,
            );
        } catch (ImageNotFoundException $e) {
            return $this->getEmptyImageUrl();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param array $attributes
     * @return string
     */
    public function getImageHtml($imageOrEntity, array $attributes = [])
    {
        $this->preventDefault($attributes);

        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $attributes['type']);
            $entityName = $image->getEntityName();
            $attributes['src'] = $this->getImageUrl($image, $attributes['type']);
            $attributes['alt'] = $image->getName();

            return $this->getImageHtmlByEntityName($attributes, $entityName);
        } catch (ImageNotFoundException $e) {
            return $this->getNoimageHtml($attributes);
        }
    }

    /**
     * @param array $attributes
     * @return string
     */
    protected function getNoimageHtml(array $attributes = []): string
    {
        $this->preventDefault($attributes);

        $entityName = 'noimage';
        $attributes['src'] = $this->getEmptyImageUrl();

        return $this->getImageHtmlByEntityName($attributes, $entityName);
    }

    /**
     * @return string
     */
    protected function getEmptyImageUrl(): string
    {
        return $this->domain->getUrl() . $this->frontDesignImageUrlPrefix . '/' . static::NOIMAGE_FILENAME;
    }

    /**
     * @return string
     */
    protected function getImagePlaceholder(): string
    {
        return $this->domain->getUrl() . $this->frontDesignImageUrlPrefix . '/' . static::PLACEHOLDER_FILENAME;
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @return string
     */
    protected function getImageCssClass(string $entityName, ?string $type): string
    {
        $allClassParts = [
            'image',
            $entityName,
            $type,
        ];
        $classParts = array_filter($allClassParts);

        return implode('-', $classParts);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_extension';
    }

    /**
     * @param array $attributes
     */
    protected function preventDefault(array &$attributes): void
    {
        Utils::setArrayDefaultValue($attributes, 'type');
        Utils::setArrayDefaultValue($attributes, 'alt', '');
        Utils::setArrayDefaultValue($attributes, 'title', $attributes['alt']);
    }

    /**
     * @param array $attributes
     * @param string $entityName
     * @return string
     */
    protected function getImageHtmlByEntityName(array $attributes, string $entityName): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        if ($this->isLazyLoadEnabled($attributes) === true) {
            $htmlAttributes = $this->makeHtmlAttributesLazyLoaded($htmlAttributes);
        }

        return $this->twigEnvironment->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type']),
        ]);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    protected function isLazyLoadEnabled(array $attributes): bool
    {
        if ($attributes['src'] === $this->getEmptyImageUrl()) {
            return false;
        }

        return array_key_exists('lazy', $attributes) ? (bool)$attributes['lazy'] : $this->isLazyLoadEnabled;
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function extractHtmlAttributesFromAttributes(array $attributes): array
    {
        $htmlAttributes = $attributes;

        foreach (static::NON_HTML_ATTRIBUTES as $nonHtmlAttribute) {
            unset($htmlAttributes[$nonHtmlAttribute]);
        }

        return $htmlAttributes;
    }

    /**
     * @param array $htmlAttributes
     * @return array
     */
    protected function makeHtmlAttributesLazyLoaded(array $htmlAttributes): array
    {
        $htmlAttributes['loading'] = 'lazy';
        $htmlAttributes['data-src'] = $htmlAttributes['src'];

        if (!$this->isNativeLazyLoadSupported()) {
            $htmlAttributes['src'] = $this->getImagePlaceholder();
        }

        return $htmlAttributes;
    }

    /**
     * @return bool
     */
    protected function isNativeLazyLoadSupported(): bool
    {
        if (!isset($this->browser)) {
            $this->browser = new Browser();
        }

        return !in_array($this->browser->getName(), static::BROWSERS_WITHOUT_NATIVE_LAZY_LOAD, true);
    }
}
