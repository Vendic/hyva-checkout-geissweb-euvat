# Geissweb EU VAT compatibility module for Hyvä Checkout

This module adds EU VIES VAT number validation to the Hyvä Checkout. It needs a valid license for [Geissweb EUVAT](https://www.geissweb.de/eu-vat-enhanced-for-magento-2.html) and [Hyvä checkout](https://www.hyva.io/hyva-checkout.html) to work.

This module is an alternative to [hyva-themes/magento2-hyva-checkout-geissweb-euvat](https://gitlab.hyva.io/hyva-checkout/checkout-integrations/magento2-hyva-checkout-geissweb-euvat) (you need a Hyvä license to visit the link). `vendic/hyva-checkout-geissweb-euvat` is build differently using Hyvä checkout [form modifiers](https://docs.hyva.io/checkout/hyva-checkout/devdocs/form-customization/entity-form-modifiers.html). The original vat-id phtml template remains untouched with this module.

## Installation
```bash
composer require vendic/hyva-checkout-geissweb-euvat
```

## Configuration
None at this moment. The module follows the configuration of the Geissweb EUVAT module.

## Compatibility
Tested with:
- Magento 2.4.6
- Hyvä Checkout 1.1.4
- Geissweb EUVAT 1.19.1

## See it in action


https://github.com/Vendic/hyva-checkout-geissweb-euvat/assets/14849044/3af7bfeb-d3fe-493f-8538-9a8cdb3a3266




## License
[MIT](https://github.com/Vendic/hyva-checkout-geissweb-euvat/blob/develop/LICENSE)
