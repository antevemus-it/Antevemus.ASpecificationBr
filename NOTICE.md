Antevemus ASpecification BR
Copyright (c) 2026 Antevemus Soluções Inovadoras em TI Ltda.
License: MIT (SPDX: MIT) — see LICENSE.

Website: https://antevemus.com.br
Email: contato@antevemus.com.br

-------------------------------------------------------------------------------
1. SCOPE & PURPOSE
-------------------------------------------------------------------------------

Antevemus ASpecification BR is the Brazilian documents satellite of Antevemus
ASpecification (antevemus/aspecification) for PHP 8.2+. Its leaf
specifications check that a string is written as a CPF, a CNPJ (numeric or
alphanumeric), a postal code (CEP), a phone number, a vehicle plate (legacy or
Mercosur), a unified process number of the CNJ, a driver's license number
(CNH), a PIS/PASEP number, a voter registration number, a RENAVAM or a PIX key,
with its check digits, and register under their own names in the core's
specification codec.

-------------------------------------------------------------------------------
2. PROVENANCE — NOTHING PORTED
-------------------------------------------------------------------------------

No source code of any third-party project was copied, ported or translated
into this package. It is an independent implementation, written by Antevemus
Soluções Inovadoras em TI Ltda. on the public API of its runtime dependency
(section 5) and on the public rules of the Brazilian documents (section 4);
the only code it did not write for this package is first-party code of the
same author, copied on purpose (section 3).

-------------------------------------------------------------------------------
3. FIRST-PARTY CODE COPIED ON PURPOSE AND RELICENSED
-------------------------------------------------------------------------------

The CPF and CNPJ check-digit routines of src/Internal/CheckDigits.php (the
modulo 11 rule, the repetition checks and the alphanumeric CNPJ rule) were
first written by the copyright holder for one of its internal products, under
a proprietary license. The copyright holder copied them into this package on
2026-11-12 and releases them here under the MIT License, with only their
names, their documentation and the form of the repetition checks changed. The
cases of their original tests are repeated in
tests/Unit/TaxpayerIdSpecificationsTest.php with the same expected answers.

The documentation guards tests/Unit/DocumentationTreeTest.php and
tests/Unit/DocumentationBlocksTest.php are copied from Antevemus ASpecification
Revolt (antevemus/aspecification-revolt 1.0.0), by the same author and under
the same MIT License.

    Origin: DocumentationTreeTest, DocumentationBlocksTest (antevemus/aspecification-revolt 1.0.0)
    Originator: Antevemus Soluções Inovadoras em TI Ltda.
    License: MIT License
    Website: https://github.com/antevemus-it/Antevemus.ASpecificationRevolt

-------------------------------------------------------------------------------
4. PUBLIC RULES IMPLEMENTED
-------------------------------------------------------------------------------

The rules are public and were implemented from their descriptions (official
ones where a norm is named; for the rules no norm spells out, the public
descriptions say so below), not by porting any implementation:

- CPF and CNPJ check digits, including the alphanumeric CNPJ: Receita Federal
  do Brasil.
- Unified process number of the judiciary: CNJ Resolution 65/2008 (check
  digits by ISO 7064 MOD 97-10).
- Area codes and the mobile and landline prefixes: the numbering plan of
  Anatel.
- Postal code range: Correios.
- Legacy and Mercosur vehicle plates: Contran.
- Driver's license registration number (CNH): Contran Resolution 886/2021
  (nine characters and two security check digits; it does not publish their
  algorithm), with the check-digit routine attributed to Denatran and used
  by the public validators, followed literally.
- PIS/PASEP (NIS/NIT) check digit: the routine of the Caixa as public
  descriptions give it.
- Voter registration number: TSE Resolution 20.132/1998 (what each check
  digit is computed over), with the weights and the rule of SP and MG of
  public descriptions of the TSE routine.
- RENAVAM check digit: the routine of Denatran as public descriptions give
  it.
- PIX keys: the Manual Operacional do DICT of the Banco Central (section 1,
  version 8.5) and the patterns of the DICT API specification (2.13.0);
  RFC 4122 for the random key and RFC 5321 for the length of an e-mail local part.

-------------------------------------------------------------------------------
5. THIRD-PARTY COMPONENTS & LICENSES
-------------------------------------------------------------------------------

Runtime: antevemus/aspecification (MIT, by the same author). Development only:
phpunit/phpunit (BSD-3-Clause) and infection/infection (BSD-3-Clause), with
their own dependencies; none of them is distributed with the package.
