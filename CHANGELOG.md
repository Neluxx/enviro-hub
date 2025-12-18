# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed
- Remove annotations for all charts

### Deprecated

### Removed

### Fixed
- Change title color according to cards background color [#19](https://github.com/Neluxx/enviro-hub/issues/19)

### Security

### Dependencies

## [v1.3.0](https://github.com/Neluxx/enviro-hub/releases/tag/v1.3.0) - 2025-11-27

### Changed
- Changed style to dark mode [#12](https://github.com/Neluxx/enviro-hub/issues/12)
- Changed date ranges to last 24 hours and last week [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Update thresholds for temperature and CO2 status [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Improve chart responsiveness for mobile devices [#14](https://github.com/Neluxx/enviro-hub/issues/14)

### Removed
- Removed air pressure chart from dashboard [#14](https://github.com/Neluxx/enviro-hub/issues/14)
- Removed chart data aggregation [#14](https://github.com/Neluxx/enviro-hub/issues/14)

## [v1.2.0](https://github.com/Neluxx/enviro-hub/releases/tag/v1.2.0) - 2025-11-25

### Added
- Added color-coded status indicators for sensor values based on optimal ranges [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Added horizontal reference lines on charts to display optimal ranges [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Added release version display in dashboard [#10](https://github.com/Neluxx/enviro-hub/issues/10)

### Changed
- Migrated dashboard UI to Bootstrap 5 for improved responsive design [#10](https://github.com/Neluxx/enviro-hub/issues/10)
- Optimized chart data aggregation with time range-specific limits [#10](https://github.com/Neluxx/enviro-hub/issues/10)

### Dependencies
- Added Chart.js annotation plugin v3.0.1 for reference line visualization

## [v1.1.1](https://github.com/Neluxx/enviro-hub/releases/tag/v1.1.1) - 2025-11-25

### Fixed
- Set timezone for date range of the chart data to remove DST delay [#8](https://github.com/Neluxx/enviro-hub/issues/8)

## [v1.1.0](https://github.com/Neluxx/enviro-hub/releases/tag/v1.1.0) - 2025-11-24

### Added

- Implemented interactive Chart.js visualizations for environmental sensor data [#1](https://github.com/Neluxx/enviro-hub/issues/1)

## [v1.0.0](https://github.com/Neluxx/enviro-hub/releases/tag/v1.0.0) - 2025-10-27

### Added

- Integrated OpenWeather API to fetch real-time weather data for environmental monitoring
- Implemented Environmental data API integration for collecting comprehensive atmospheric metrics
- Added automated email notification system for CO2 levels exceeding configured thresholds
  - Configurable alert thresholds
  - HTML email templates with detailed CO2 level information
