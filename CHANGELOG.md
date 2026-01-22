
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Add default values for not existing sensor data in dashboard template

### Changed
- Standardize titles of all templates

### Deprecated

### Removed

### Fixed
- Change the API route pattern to enable dynamic chart data loading [#36](https://github.com/Neluxx/enviro-hub/issues/36)

### Security

### Dependencies

## [v2.0.1 Venus](https://github.com/Neluxx/enviro-hub/releases/tag/v2.0.1) - 2026-01-21

### Changed
- Swap position of version number and timestamp in dashboard template

### Fixed
- Add index to node UUID to prevent memory exhaustion
- Retrieve only the latest entry of the specified node UUIDs

## [v2.0.0 Venus](https://github.com/Neluxx/enviro-hub/releases/tag/v2.0.0) - 2026-01-20

### Added
- Add Multi-Node support [#18](https://github.com/Neluxx/enviro-hub/issues/18)
- Implement Progressive Web App (PWA) functionality [#16](https://github.com/Neluxx/enviro-hub/issues/16)
- Add email notification for CO2 levels exceeding configured threshold [#17](https://github.com/Neluxx/enviro-hub/issues/17)
- Add Home and Node entity models with relationships [#27](https://github.com/Neluxx/enviro-hub/issues/27)
- Add home controller and template to display all available homes [#27](https://github.com/Neluxx/enviro-hub/issues/27)
- Add node controller and template to display all nodes of a home [#27](https://github.com/Neluxx/enviro-hub/issues/27)

### Changed
- Migrated Bootstrap from CDN to AssetMapper
- Use Symfony UX Chart.js and Stimulus controller instead of CDN
- Update routes to include home identifier for multi-home support [#27](https://github.com/Neluxx/enviro-hub/issues/27)
- Update dashboard to display sensor data for a specific node of a home [#27](https://github.com/Neluxx/enviro-hub/issues/27)

### Removed
- Remove annotations for all charts
- Remove OpenWeather Endpoint [#22](https://github.com/Neluxx/enviro-hub/issues/22)

### Fixed
- Change title color according to cards background color [#19](https://github.com/Neluxx/enviro-hub/issues/19)
- Fix chart data fetching with newly added route parameters

### Dependencies
- Added doctrine/doctrine-fixtures-bundle for test fixtures [#27](https://github.com/Neluxx/enviro-hub/issues/27)

## [v1.3.0 Mercury](https://github.com/Neluxx/enviro-hub/releases/tag/v1.3.0) - 2025-11-27

### Changed
- Changed style to dark mode [#12](https://github.com/Neluxx/enviro-hub/issues/12)
- Changed date ranges to last 24 hours and last week [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Update thresholds for temperature and CO2 status [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Improve chart responsiveness for mobile devices [#14](https://github.com/Neluxx/enviro-hub/issues/14)

### Removed
- Removed air pressure chart from dashboard [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Removed chart data aggregation [#14](https://github.com/Neluxx/enviro-hub/issues/14)

## [v1.2.0 Mercury](https://github.com/Neluxx/enviro-hub/releases/tag/v1.2.0) - 2025-11-25

### Added
- Added color-coded status indicators for sensor values based on optimal ranges [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Added horizontal reference lines on charts to display optimal ranges [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Added release version display in dashboard [#10](https://github.com/Neluxx/enviro-hub/issues/10)

### Changed
- Migrated dashboard UI to Bootstrap 5 for improved responsive design [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Optimized chart data aggregation with time range-specific limits [#10](https://github.com/Neluxx/enviro-hub/issues/10)

### Dependencies
- Added Chart.js annotation plugin v3.0.1 for reference line visualization

## [v1.1.1 Mercury](https://github.com/Neluxx/enviro-hub/releases/tag/v1.1.1) - 2025-11-25

### Fixed
- Set timezone for date range of the chart data to remove DST delay [#8](https://github.com/Neluxx/enviro-hub/issues/8)

## [v1.1.0 Mercury](https://github.com/Neluxx/enviro-hub/releases/tag/v1.1.0) - 2025-11-24

### Added

- Implemented interactive Chart.js visualizations for environmental sensor data [#1](https://github.com/Neluxx/enviro-hub/issues/1)

## [v1.0.0 Mercury](https://github.com/Neluxx/enviro-hub/releases/tag/v1.0.0) - 2025-10-27

### Added

- Integrated OpenWeather API to fetch real-time weather data for environmental monitoring
- Implemented Environmental data API integration for collecting comprehensive atmospheric metrics
- Added automated email notification system for CO2 levels exceeding configured thresholds
  - Configurable alert thresholds
  - HTML email templates with detailed CO2 level information
