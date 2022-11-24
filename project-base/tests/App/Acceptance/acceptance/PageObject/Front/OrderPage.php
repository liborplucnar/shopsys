<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\FrameworkBundle\Test\Codeception\FrontCheckbox;

class OrderPage extends AbstractPage
{
    private const FIRST_NAME_FIELD_NAME = 'order_personal_info_form[firstName]';

    /**
     * @param string $transportTitle
     */
    public function assertTransportIsNotSelected(string $transportTitle): void
    {
        $translatedTransportTitle = t($transportTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSeeCheckboxIsCheckedByLabel($translatedTransportTitle);
    }

    /**
     * @param string $transportTitle
     */
    public function assertTransportIsSelected(string $transportTitle): void
    {
        $translatedTransportTitle = t($transportTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->seeCheckboxIsCheckedByLabel($translatedTransportTitle);
    }

    /**
     * @param int $transportPosition
     */
    public function selectTransport(int $transportPosition): void
    {
        $frontCheckboxClicker = FrontCheckbox::createByCss(
            $this->tester,
            '#transport_and_payment_form_transport_' . $transportPosition
        );
        $frontCheckboxClicker->check();
        $this->tester->waitForAjax();
    }

    /**
     * @param string $paymentTitle
     */
    public function assertPaymentIsNotSelected(string $paymentTitle): void
    {
        $this->scrollToPaymentForm();
        $translatedPaymentTitle = t($paymentTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSeeCheckboxIsCheckedByLabel($translatedPaymentTitle);
    }

    /**
     * @param string $paymentTitle
     */
    public function assertPaymentIsSelected(string $paymentTitle): void
    {
        $this->scrollToPaymentForm();
        $translatedPaymentTitle = t($paymentTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->seeCheckboxIsCheckedByLabel($translatedPaymentTitle);
    }

    /**
     * @param int $paymentPosition
     */
    public function selectPayment(int $paymentPosition): void
    {
        $this->scrollToPaymentForm();
        $frontCheckboxClicker = FrontCheckbox::createByCss(
            $this->tester,
            '#transport_and_payment_form_payment_' . $paymentPosition
        );
        $frontCheckboxClicker->check();
        $this->tester->waitForAjax();
    }

    /**
     * @param string $firstName
     */
    public function fillFirstName(string $firstName): void
    {
        $this->tester->fillFieldByName(self::FIRST_NAME_FIELD_NAME, $firstName);
    }

    /**
     * @param string $firstName
     */
    public function assertFirstNameIsFilled(string $firstName): void
    {
        $this->tester->seeInFieldByName($firstName, self::FIRST_NAME_FIELD_NAME);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $telephone
     */
    public function fillPersonalInfo(string $firstName, string $lastName, string $email, string $telephone): void
    {
        $this->fillFirstName($firstName);
        $this->tester->fillFieldByName('order_personal_info_form[lastName]', $lastName);
        $this->tester->fillFieldByName('order_personal_info_form[email]', $email);
        $this->tester->fillFieldByName('order_personal_info_form[telephone]', $telephone);
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $postcode
     */
    public function fillBillingAddress(string $street, string $city, string $postcode): void
    {
        $this->tester->fillFieldByName('order_personal_info_form[street]', $street);
        $this->tester->fillFieldByName('order_personal_info_form[city]', $city);
        $this->tester->fillFieldByName('order_personal_info_form[postcode]', $postcode);

        $this->tester->waitForAjax();
    }

    /**
     * @param string $note
     */
    public function fillNote(string $note): void
    {
        $this->tester->fillFieldByName('order_personal_info_form[note]', $note);
    }

    public function acceptLegalConditions(): void
    {
        $frontCheckboxClicker = FrontCheckbox::createByCss(
            $this->tester,
            '#order_personal_info_form_legalConditionsAgreement'
        );
        $frontCheckboxClicker->check();
        $this->tester->waitForAjax();
        $this->tester->wait(1);
    }

    private function scrollToPaymentForm(): void
    {
        $this->tester->scrollTo(['css' => '#transport_and_payment_form_payment']);
    }

    public function clickGoToCartInPopUpWindow(): void
    {
        $this->tester->clickByTranslationFrontend(
            'Go to cart',
            'messages',
            [],
            WebDriverBy::cssSelector('#window-main-container')
        );
    }

    public function continueToSecondStep(): void
    {
        $this->tester->clickByTranslationFrontend('Order [verb]');
    }

    public function continueToThirdStep(): void
    {
        $this->tester->clickByTranslationFrontend('Continue in order');
    }

    public function goBackToSecondStep(): void
    {
        $this->tester->clickByTranslationFrontend('Back to shipping and payment selection');
    }

    public function finishOrder(): void
    {
        $this->tester->clickByTranslationFrontend('Finish the order');
    }

    public function checkOrderFinishedSuccessfully(): void
    {
        $this->tester->seeTranslationFrontend('Order sent');
    }
}
