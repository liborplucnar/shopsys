<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository as BaseMailTemplateRepository;

/**
 * @method \App\Model\Mail\MailTemplate|null findByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate getByNameAndDomainId(string $templateName, int $domainId)
 * @method \App\Model\Mail\MailTemplate[] getAllByDomainId(int $domainId)
 * @method \App\Model\Mail\MailTemplate getById(int $mailTemplateId)
 */
class MailTemplateRepository extends BaseMailTemplateRepository
{
}
