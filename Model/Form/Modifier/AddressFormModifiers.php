<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\HyvaCheckoutGeisswebEuvat\Model\Form\Modifier;

use Geissweb\Euvat\Helper\Configuration as EuVatConfiguration;
use Hyva\Checkout\Model\Form\EntityField\EavAttributeField;
use Hyva\Checkout\Model\Form\EntityField\EavEntityAddress\CountryAttributeField;
use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Vendic\HyvaCheckoutGeisswebEuvat\Model\Config;

class AddressFormModifiers implements EntityFormModifierInterface
{
    private const VAT_ID_FIELD_NAME = 'vat_id';

    private const EUVAT_VAT_ID_FIELD_TOOLTIP_XML_PATH = 'euvat/integration/field_tooltip';
    private const EUVAT_VAT_ID_FIELD_PLACEHOLDER_XML_PATH = 'euvat/integration/field_placeholder';

    public function __construct(
        private EuVatConfiguration $euvatConfiguration,
        private Config $config,
        private bool $isAlwaysShowVatField = false
    ) {
    }

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'applyVatIdFieldConfigs',
            'form:build',
            [$this, 'applyVatIdFieldConfigs']
        );

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

        $form->registerModificationListener(
            'removeSpacesFromVatId',
            sprintf('form:%s:updated', self::VAT_ID_FIELD_NAME),
            [$this, 'removeSpacesFromVatId']
        );

        return $form;
    }

    public function applyVatIdFieldConfigs(EntityFormInterface $form): void
    {
        /** @var EavAttributeField|null $vatIdField */
        $vatIdField = $form->getField(self::VAT_ID_FIELD_NAME);
        if (!$vatIdField) {
            return;
        }

        if ($vatIdPlaceholder = $this->euvatConfiguration->getConfig(self::EUVAT_VAT_ID_FIELD_PLACEHOLDER_XML_PATH)) {
            $vatIdField->setAttribute('placeholder', $vatIdPlaceholder);
        }

        if ($vatIdTooltip = $this->euvatConfiguration->getConfig(self::EUVAT_VAT_ID_FIELD_TOOLTIP_XML_PATH)) {
            $vatIdField->setData('tooltip', $vatIdTooltip);
        }
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
        $vatIdField = $form->getField(self::VAT_ID_FIELD_NAME);
        if (!$vatIdField) {
            return;
        }

        $vatIdField->setAttribute(
            '@keydown.debounce.300ms',
            '$dispatch(\'close-vat-message\'); $event.target.value = $event.target.value.replaceAll(" ", ""); $dispatch(\'vat-id-changed\', $event.target.value)'
        );
        $vatIdField->setAttribute(
            '@change.debounce',
            '$event.target.value = $event.target.value.replaceAll(" ", ""); $dispatch(\'vat-id-changed\', $event.target.value)'
        );
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

        $vatIdField = $form->getField(self::VAT_ID_FIELD_NAME);
        /** @var CountryAttributeField|null $countryField */
        $countryField = $form->getField('country_id');

        if (!$vatIdField || !$countryField) {
            return;
        }

        $isVatIdHidden = $countryField->getValue() === $this->euvatConfiguration->getMerchantCountryCode() &&
            !$this->config->isVatIdFieldVisibleForMerchantCountry();

        if ($isVatIdHidden) {
            $vatIdField->hide();
        }
    }

    /**
     * Hide vat_id field according to Geissweb EUVat configuration.
     */
    public function applyHideVatIdField(EntityFormInterface $form): void
    {
        $vatIdField = $form->getField(self::VAT_ID_FIELD_NAME);
        /** @var CountryAttributeField|null $countryField */
        $countryField = $form->getField('country_id');

        if (!$vatIdField || !$countryField) {
            return;
        }

        if (in_array($countryField->getValue(), $this->euvatConfiguration->getFieldVisibleCountries())) {
            return;
        }

        $vatIdField->hide();
    }

    public function removeSpacesFromVatId(EntityFormInterface $form): void
    {
        /** @var EavAttributeField|null $vatIdField */
        $vatIdField = $form->getField(self::VAT_ID_FIELD_NAME);
        if (!$vatIdField) {
            return;
        }

        $vatIdField->setValue(str_replace(' ', '', (string)$vatIdField->getValue()));
    }
}
