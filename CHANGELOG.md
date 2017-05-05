# Change Log

All notable changes to Fig will be documented in this file.

## Unreleased
### Fixed
- Validation of `file:source` values

## [0.3.1] - 2017-05-05
### Fixed
- Always setting `ignore_errors`, `ignore_output` to true
- Variable replacement in `file:source` values
- Handling of unexpected YAML values

### Removed
- Experimental privilege escalation support

## [0.3.0] - 2017-01-02
### Added
- `install` command
- man page, fig.1
- `upgrade` command
- Expanded `show` options
- `action` and `path` properties to File action syntax

### Changed
- Renamed `update` to `snapshot`

### Deprecated
- `action` and `path`-less File action syntax

### Fixed
- Skip asset deletion for profiles with no assets

### Changed
- Switched to Cranberry\CLI framework

## [0.2.1] - 2016-12-21
### Added
- Support for creating Fig apps by cloning Git repositories

## [0.2.0] - 2016-12-17
### Added
- Support for profile variables

## [0.1.1] - 2016-12-13
### Fixed
- Sanitize command strings
