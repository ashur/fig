# Change Log

All notable changes to Fig will be documented in this file.

## Unreleased
### Fixed
- [#21](https://github.com/ashur/fig/issues/21) – Variable replacement not performed on ‘defaults:write’ result output
- [#25](https://github.com/ashur/fig/issues/25) – Trailing slash prevents `show` from displaying profiles belonging to valid app
- [#26](https://github.com/ashur/fig/issues/26) – defaults.write: `ignore_output` is ignored

## [0.4.2] - 2017-06-21
### Fixed
- [#22](https://github.com/ashur/fig/issues/22) – Cranberry\CLI\Format\String class is incompatible with PHP 7

## [0.4.1] - 2017-06-04
### Added
- `file:replace_string`
- [#19](https://github.com/ashur/fig/issues/19) – Add `--extend` option to `add` command

### Fixed
- [#17](https://github.com/ashur/fig/issues/17) – `show` no longer highlights extending profiles
- [#18](https://github.com/ashur/fig/issues/18) – Invalid YAML is not correctly handled

## [0.4.0] - 2017-05-23
### Added
- Support for nested variable definitions in multi-level `include`s

### Changed
- Lazy-load profile definition files

### Fixed
- Validation of profile and action definitions
- Prevent profile from including itself

### Removed
- `action` and `path`-less File action syntax
- `install` command
- `upgrade` command

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
