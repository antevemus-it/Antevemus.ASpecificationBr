# Tests

```bash
composer install
vendor/bin/phpunit
```

| Folder | What |
| :--- | :--- |
| `Unit/` | Fixed cases of each specification (`TaxpayerIdSpecificationsTest`, `AddressAndContactSpecificationsTest`, `VehicleAndCourtSpecificationsTest`, `RegistrationNumberSpecificationsTest`, `PixKeySpecificationTest`), the check-digit routines on their own (`CheckDigitsTest`), the facade, the codec and the secrecy of the failures over a pool of every specification (`BrSpecAndCodecTest`), and the documentation (`DocumentationTreeTest`, `DocumentationBlocksTest`). |
| `Property/` | Properties over generated documents, with fixed seeds (`DocumentPropertyTest`, `RegistrationAndPixPropertyTest`). |
| `Support/` | `DocumentGenerator`: fictitious documents whose check digits are computed by a route of its own, apart from `src/`. |

1.0.0: 250 tests. 1.1.0: 541 tests. Mutation testing: see `infection.json5`.
