<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonalDataAccessMail implements MessageFactoryInterface
{
    public const VARIABLE_EMAIL = '{email}';
    public const VARIABLE_URL = '{url}';
    public const VARIABLE_DOMAIN = '{domain}';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        Domain $domain,
        Setting $setting,
        DomainRouterFactory $domainRouterFactory
    ) {
        $this->domain = $domain;
        $this->setting = $setting;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $personalDataAccessRequest): \Shopsys\FrameworkBundle\Model\Mail\MessageData
    {
        return new MessageData(
            $personalDataAccessRequest->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $this->domain->getId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $this->domain->getId()),
            $this->getBodyValuesIndexedByVariableName(
                $this->getVariablePersonalDataAccessUrl(
                    $personalDataAccessRequest->getHash()
                ),
                $personalDataAccessRequest->getEmail(),
                $this->domain->getName()
            ),
            $this->getSubjectValuesIndexedByVariableName($this->domain->getName())
        );
    }

    /**
     * @param string $url
     * @param string $email
     * @param string $domainName
     * @return array
     */
    protected function getBodyValuesIndexedByVariableName(string $url, string $email, string $domainName): array
    {
        return [
            self::VARIABLE_URL => $url,
            self::VARIABLE_EMAIL => htmlspecialchars($email, ENT_QUOTES),
            self::VARIABLE_DOMAIN => htmlspecialchars($domainName, ENT_QUOTES),
        ];
    }

    /**
     * @param string $domainName
     * @return array
     */
    protected function getSubjectValuesIndexedByVariableName(string $domainName): array
    {
        return [
            self::VARIABLE_DOMAIN => $domainName,
        ];
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function getVariablePersonalDataAccessUrl(string $hash): string
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $hash,
        ];

        return $router->generate(
            'front_personal_data_access',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
