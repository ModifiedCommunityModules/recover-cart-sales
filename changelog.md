# Änderungsprotokoll
Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) und vewendet [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
Unveröffentlichte Features und Fixes können auf GitHub eingesehen werden. Klicke hierfür auf [Unreleased].

## [0.1.2] - 2022-07-21

### Changed
- Modul ist jetzt mit modified 2.0.7.0, 2.0.7.1, 2.0.7.2 und PHP 7.4 kompatible.
- Fix: Fehler behoben, dass keine E-Mails versendet werden konnten.
- Fix: Fehler behoben, dass die Namen und E-Mail Adressen in der Listendarstellen nicht korrekt angezeigt wurden.
- Fix: "Bestellung abschließen" Button ausblenden, da dieser nurzeit nicht korrekt funktioniert.

## [0.1.1] - 2020-10-16

### Changed
- **BREAKING CHANGE**: Die Datenbanktabellenspalten `customers_basket.checkout_site` und `customers_basket.language` wurden umbenannt
in `customers_basket.mcm_checkout_site` und `customers_basket.mcm_language`. Sofern Verion 0.1.0 nicht installiert wurde
kann dieser Punkt ignoriert werden. Ansonsten können die beiden Datenbanktabellenspalten manuell umbenannt werden.


## [0.1.0] - 2020-10-15
Dieses ist die erste Version vom Modul **Offene Warenkörbe Plus** für den MMLC. Dabei wurde das Modul aus dem Modified Forum genommen
und für die Bereitstellung über den MMLC angepasst. Bei der Veröffentlichung des Moduls im MMLC erhält das Modul neue Versionsnummern,
die nicht mit anderen Versionsnummern von anderen Quellen übereinstimmen müssen.

### Added
- Feature: Initiale Version.

[Unreleased]: https://github.com/ModifiedCommunityModules/recover-cart-sales/compare/0.1.2...HEAD
[0.1.2]: https://github.com/ModifiedCommunityModules/recover-cart-sales/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/ModifiedCommunityModules/recover-cart-sales/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/ModifiedCommunityModules/recover-cart-sales/releases/tag/0.1.0