<?php

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

declare(strict_types=1);

namespace Vendic\HyvaCheckoutGeisswebEuvat\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const DISPLAY_VAT_ID_FOR_DOMESTIC_COUNTRY_PATH = 'euvat/hyva_checkout/display_vat_id_for_domestic_country';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isVatIdFieldVisibleForMerchantCountry(int $store = 0): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::DISPLAY_VAT_ID_FOR_DOMESTIC_COUNTRY_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
