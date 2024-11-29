<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeProvider;

class TransportResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportTypeProvider $transportTypeProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly TransportTypeProvider $transportTypeProvider,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Transport' => [
                'transportTypeCode' => function (Transport $transport) {
                    return $transport->getType();
                },
                'vat' => function (Transport $transport) {
                    return $transport->getVatForDomain($this->domain->getId());
                },
                'displayInCart' => function (Transport $transport) {
                    return in_array($transport->getType(), $this->transportTypeProvider->getAllEnabledInCartIndexedByTranslations(), true);
                },
            ],
        ];
    }
}
