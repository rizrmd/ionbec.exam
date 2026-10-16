## Exam navigation regression

After installing the repository's npm dependencies, compile the actual Vue component with its isolated harness:

```sh
node tests/browser/compile-exam-navigation.cjs /tmp/ionbec-navigation-test
NAV_TEST_BUNDLE=/tmp/ionbec-navigation-test/test.js node tests/browser/exam-navigation.cjs
```

The test runner requires Playwright Core (set `PLAYWRIGHT_MODULE` to its installed module path if it is provided outside this repository) and Chromium (`CHROMIUM_PATH` when using a system binary). `NAV_TEST_RESULTS` optionally saves JSON results.

The harness never calls application APIs. It covers narrow/short screens, orientation changes, scrolling through 120 question numbers, Previous/Next and boundary states, explicit panel closure, a transformed/clipped ancestor, visual-viewport resize simulation, and an additional action event without scoring. Browser toolbar/keyboard viewport changes are simulated; physical mobile browser behavior still needs device coverage.
