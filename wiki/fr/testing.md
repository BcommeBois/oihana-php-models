# Tests & couverture

La bibliothèque est livrée avec une suite PHPUnit complète tournant en **mode
strict** et couvrant **100 % des lignes** (356 tests).

## Lancer la suite

```bash
composer test
```

Lancer un seul cas de test :

```bash
./vendor/bin/phpunit --filter DocumentsTraitTest
```

## Mesurer la couverture

La couverture nécessite **Xdebug** ou **PCOV**.

```bash
composer coverage        # texte + Clover + HTML sous build/coverage/
composer coverage:md     # résumé Markdown lisible (build/coverage/COVERAGE.md)
```

`build/` est **gitignoré** : la couverture est un instantané qui se périme au
commit suivant, on la régénère donc à la demande plutôt que de la committer.
`composer coverage:md` tient aussi un petit journal de tendance local
(`build/coverage/history.json`) pour afficher le delta depuis la fois
précédente.

## Mode strict

`phpunit.xml` active `failOnRisky`, `failOnWarning`, `failOnSkipped`,
`failOnIncomplete` et `failOnEmptyTestSuite`. Autrement dit : avertissements,
tests risqués (sans assertion) et tests ignorés font tous **échouer** la suite.
Un test qui ne vérifie rien ne protège rien.

## Philosophie de test

- La couverture mesure quelles lignes ont **été exécutées**, pas quels
  comportements sont **vérifiés** — 100 % de couverture ≠ zéro bug.
- Quand vous découvrez un comportement surprenant, **figez-le dans un test**
  avant de toucher à quoi que ce soit : d'autres bibliothèques peuvent en
  dépendre.
- Testez tout ce qui est atteignable ; n'annotez une ligne
  `@codeCoverageIgnore` que lorsqu'elle est réellement impossible à atteindre
  (par exemple une garde défensive que la surface publique ne peut déclencher).

## Intégration continue

Chaque push et chaque pull request lance la suite sur PHP 8.4 via GitHub
Actions (`.github/workflows/ci.yml`) ; la documentation d'API est construite et
déployée sur GitHub Pages par `.github/workflows/docs.yml`.
