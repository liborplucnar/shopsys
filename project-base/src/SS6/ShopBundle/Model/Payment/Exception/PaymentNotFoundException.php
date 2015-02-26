<?php

namespace SS6\ShopBundle\Model\Payment\Exception;

use SS6\ShopBundle\Model\Payment\Exception\PaymentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentNotFoundException extends NotFoundHttpException implements PaymentException {

}
