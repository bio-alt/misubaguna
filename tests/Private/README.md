# tests/Private — tests that quote EJMA Standard data

Everything in this directory except this README is git-ignored (see `.gitignore`): these tests
and fixtures assert values published in the EJMA Standards (9th Ed., 2008), which are
copyrighted, and the repository is public. This README is tracked only so the directory always
exists for the `Private` PHPUnit suite in `phpunit.xml`; with nothing else here the suite is
simply empty.

What lives here (namespace `Tests\Private\...`, autoloaded through the `Tests\` PSR-4 mapping):

- `Ejma/Fixtures/BenchmarkResults.php` — the published §4.14 benchmark results (p098) for the
  inputs in `tests/Unit/Ejma/Fixtures/Benchmarks.php`.
- `Ejma/BenchmarkResultsTest.php` — the engine against those results.
- `Ejma/CoefficientTablesTest.php` — the installed Appendix I tables against published values.
- `Ejma/AppendixJTest.php` — the Appendix J worked examples.

The tests need the private data in `resources/ejma-private/` (see its README) and are skipped
without it. Keep a copy of this directory outside the repository; never `git add -f` anything
here other than this README. Public tests in `tests/Unit` and `tests/Feature` must not contain
published EJMA values — put such assertions here instead.
