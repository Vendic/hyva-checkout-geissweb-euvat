<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\HyvaCheckoutGeisswebEuvat\Model\Form\Modifier;

use Geissweb\Euvat\Helper\Configuration;
use Hyva\Checkout\Model\Form\EntityField\EavAttributeField;
use Hyva\Checkout\Model\Form\EntityField\EavEntityAddress\CountryAttributeField;
use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;

class AddressFormModifiers implements EntityFormModifierInterface
{
    public function __construct(
        private Configuration $configuration,
        private bool $isAlwaysShowVatField = false
    ) {
    }

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'addCountrySelectListener',
            'form:build',
            [$this, 'applyAddCountrySelectListener']
        );

        $form->registerModificationListener(
            'triggerEventOnVatIdChange',
            'form:build',
            [$this, 'applyTriggerEventOnVatIdChange']
        );

        $form->registerModificationListener(
            'hideVatIdFieldForMerchantCountry',
            'form:build',
            [$this, 'applyHideVatIdFieldForMerchantCountry']
        );

        $form->registerModificationListener(
            'hideVatIdField',
            'form:build',
            [$this, 'applyHideVatIdField']
        );

        return $form;
    }

    /**
     * Add @change event to country select
     *
     * @param EntityFormInterface $form
     * @return void
     */
    public function applyAddCountrySelectListener(EntityFormInterface $form): void
    {
        /** @var CountryAttributeField|null $countryField */
        $countryField = $form->getField('country_id');
        if (!$countryField) {
            return;
        }

        $countryField->setAttribute('@change', '$dispatch(\'country-id-changed\', $event.target.value)');
    }

    /**
     * Add @change event to country select
     *
     * @param EntityFormInterface $form
     * @return void
     */
    public function applyTriggerEventOnVatIdChange(EntityFormInterface $form): void
    {
        /** @var EavAttributeField|null $vatIdField */
        $vatIdField = $form->getField('vat_id');
        if (!$vatIdField) {
            return;
        }

        $vatIdField->setAttribute('@keydown', '$dispatch(\'close-vat-message\')');
        $vatIdField->setAttribute('@change.debounce', '$dispatch(\'vat-id-changed\', $event.target.value)');
    }

    /**
     * Hide vat_id field when the merchant country is selected.
     *
     * @param EntityFormInterface $form
     * @return void
     */
    public function applyHideVatIdFieldForMerchantCountry(EntityFormInterface $form): void
    {
        if ($this->isAlwaysShowVatField) {
            return;
        }
        
        $vatIdField = $form->getField('vat_id');
        /** @var CountryAttributeField|null $countryField */
        $countryField = $form->getField('country_id');

        if (!$vatIdField || !$countryField) {
            return;
        }

        if ($countryField->getValue() === $this->configuration->getMerchantCountryCode()) {
            $vatIdField->hide();
        }
    }

    /**
     * Hide vat_id field according to Geissweb EUVat configuration.
     */
    public function applyHideVatIdField(EntityFormInterface $form): void
    {
        $vatIdField = $form->getField('vat_id');
        /** @var CountryAttributeField|null $countryField */
        $countryField = $form->getField('country_id');

        if (!$vatIdField || !$countryField) {
            return;
        }

        if (in_array($countryField->getValue(), $this->configuration->getFieldVisibleCountries())) {
            return;
        }

        $vatIdField->hide();
    }
}
