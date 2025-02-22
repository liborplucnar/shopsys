<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class CustomerMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail $registrationMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerActivationMail $customerActivationMail
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly RegistrationMail $registrationMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly CustomerActivationMail $customerActivationMail,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendRegistrationMail(CustomerUser $customerUser): void
    {
        $mailTemplate = $this->mailTemplateFacade->get(
            MailTemplate::REGISTRATION_CONFIRM_NAME,
            $customerUser->getDomainId(),
        );
        $this->sendRegistrationMailTemplate($mailTemplate, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendActivationMail(CustomerUser $customerUser): void
    {
        $mailTemplate = $this->mailTemplateFacade->get(CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $customerUser->getDomainId());
        $this->sendActivationMailTemplate($mailTemplate, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $forceSendTo
     */
    public function sendRegistrationMailTemplate(
        MailTemplate $mailTemplate,
        CustomerUser $customerUser,
        ?string $forceSendTo = null,
    ): void {
        $messageData = $this->registrationMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }
        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface $customerUser
     * @param string|null $forceSendTo
     */
    public function sendActivationMailTemplate(
        MailTemplate $mailTemplate,
        ResetPasswordInterface $customerUser,
        ?string $forceSendTo = null,
    ): void {
        $messageData = $this->customerActivationMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }
        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
