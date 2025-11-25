# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed
- Set timezone for date range of the chart data to remove DST delay [#8](https://github.com/Neluxx/enviro-hub/issues/8)

### Security

### Dependencies

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
