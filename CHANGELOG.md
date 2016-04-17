# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.1.0 - 2016-04-17

Initial release.

### Added

- `React2Psr7\ReactRequestHandler`, which:
  - accepts PSR-7 middleware to the constructorr;
  - can be attached as a request listener to a React HTTP server instance;
  - on dispatch, marshals a PSR-7 request from the React request, and
  - emits a React response marshaled from the returned PSR-7 response.
- `React2Psr7\StaticFiles`, which is middleware that can serve static
  files from a configured directory which satisfy a content-type whitelist.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
