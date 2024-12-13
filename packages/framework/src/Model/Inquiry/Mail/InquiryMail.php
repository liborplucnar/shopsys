<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Mailer\MailerHelper;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Inquiry\Inquiry;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InquiryMail
{
    public const string CUSTOMER_MAIL_TEMPLATE_NAME = 'product_inquiry_customer';
    public const string ADMIN_MAIL_TEMPLATE_NAME = 'product_inquiry_admin';

    public const string VARIABLE_FULL_NAME = '{fullName}';
    public const string VARIABLE_EMAIL = '{email}';
    public const string VARIABLE_TELEPHONE = '{telephone}';
    public const string VARIABLE_COMPANY_NAME = '{companyName}';
    public const string VARIABLE_COMPANY_NUMBER = '{companyNumber}';
    public const string VARIABLE_COMPANY_TAX_NUMBER = '{companyTaxNumber}';
    public const string VARIABLE_NOTE = '{note}';

    public const string VARIABLE_PRODUCT_NAME = '{productName}';
    public const string VARIABLE_PRODUCT_CATALOG_NUMBER = '{productCatnum}';
    public const string VARIABLE_PRODUCT_URL = '{productUrl}';
    public const string VARIABLE_PRODUCT_IMAGE = '{productImageUrl}';

    public const string VARIABLE_ADMIN_INQUIRY_DETAIL_URL = '{adminInquiryDetailUrl}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade $productImageFacade
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Domain $domain,
        protected readonly ProductImageFacade $productImageFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessageForAdmin(MailTemplate $template, Inquiry $inquiry): MessageData
    {
        return new MessageData(
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $inquiry->getDomainId()),
            $this->getBodyVariablesReplacementsForAdmin($inquiry),
            $this->getSubjectVariablesReplacements($inquiry),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessageForCustomer(MailTemplate $template, Inquiry $inquiry): MessageData
    {
        return new MessageData(
            $inquiry->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $inquiry->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $inquiry->getDomainId()),
            $this->getBodyVariablesReplacements($inquiry),
            $this->getSubjectVariablesReplacements($inquiry),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return array<string, string>
     */
    protected function getSubjectVariablesReplacements(Inquiry $inquiry): array
    {
        return [
            self::VARIABLE_FULL_NAME => htmlspecialchars($inquiry->getFullName(), ENT_QUOTES),
            self::VARIABLE_EMAIL => htmlspecialchars($inquiry->getEmail(), ENT_QUOTES),
            self::VARIABLE_TELEPHONE => htmlspecialchars($inquiry->getTelephone(), ENT_QUOTES),
            self::VARIABLE_COMPANY_NAME => MailerHelper::escapeOptionalString($inquiry->getCompanyName()),
            self::VARIABLE_COMPANY_NUMBER => MailerHelper::escapeOptionalString($inquiry->getCompanyNumber()),
            self::VARIABLE_COMPANY_TAX_NUMBER => MailerHelper::escapeOptionalString($inquiry->getCompanyTaxNumber()),
            self::VARIABLE_PRODUCT_NAME => MailerHelper::escapeOptionalString($inquiry->getProduct()?->getName()),
            self::VARIABLE_PRODUCT_CATALOG_NUMBER => htmlspecialchars($inquiry->getProductCatnum(), ENT_QUOTES),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return array<string, string>
     */
    protected function getBodyVariablesReplacements(Inquiry $inquiry): array
    {
        return [
            ...$this->getSubjectVariablesReplacements($inquiry),
            self::VARIABLE_NOTE => MailerHelper::escapeOptionalString($inquiry->getNote()),
            self::VARIABLE_PRODUCT_URL => $this->getProductUrl($inquiry),
            self::VARIABLE_PRODUCT_IMAGE => $this->productImageFacade->getProductImageUrl($inquiry->getProduct(), $inquiry->getDomainId()),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return array<string, string>
     */
    protected function getBodyVariablesReplacementsForAdmin(Inquiry $inquiry): array
    {
        return [
            ...$this->getBodyVariablesReplacements($inquiry),
            self::VARIABLE_ADMIN_INQUIRY_DETAIL_URL => $this->domainRouterFactory->getRouter(Domain::MAIN_ADMIN_DOMAIN_ID)->generate(
                'admin_inquiry_detail',
                ['id' => $inquiry->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     * @return string
     */
    protected function getProductUrl(Inquiry $inquiry): string
    {
        if ($inquiry->getProduct() === null) {
            return '';
        }

        return $this->domainRouterFactory->getRouter($inquiry->getDomainId())->generate(
            'front_product_detail',
            ['id' => $inquiry->getProduct()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
