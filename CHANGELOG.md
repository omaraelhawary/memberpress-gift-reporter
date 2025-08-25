# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial plugin structure and functionality
- Gift report generation and display
- CSV export functionality (all data)
- REST API endpoints
- Admin interface integration
- WordPress coding standards compliance
- GitHub Actions for PHPCS
- Comprehensive documentation
- Improved gift transaction detection (now shows ALL gifts)
- Enhanced query structure for better performance

### Changed
- Updated gift transaction detection logic to be more inclusive
- Improved query structure to match MemberPress dev team's approach

### Deprecated
- N/A

### Removed
- Filtering functionality from report generation and CSV export
- Filter parameters from REST API endpoints
- Frontend filter handling in JavaScript

### Fixed
- N/A

### Security
- Implemented nonce verification for all forms
- Added capability checks for admin access
- Sanitized all user inputs
- Used prepared statements for database queries

## [1.0.0] - 2024-01-XX

### Added
- Initial release of MemberPress Gift Reporter
- Complete gift tracking and reporting functionality
- CSV export functionality (all data)
- REST API for programmatic access
- Admin dashboard integration
- Comprehensive documentation
- WordPress coding standards compliance
- Security best practices implementation

### Features
- **Gift Purchase Tracking**: Track all gift purchases with transaction details
- **Recipient Information**: Link gift givers to recipients
- **Status Monitoring**: Track claimed, unclaimed, and invalid gifts
- **Revenue Reporting**: Calculate total revenue from gift sales
- **CSV Export**: Download reports in spreadsheet format (all data)
- **REST API**: Programmatic access to report data
- **Admin Interface**: Clean, user-friendly dashboard

### Technical Implementation
- WordPress plugin architecture
- MemberPress integration
- Database optimization for large datasets
- Responsive admin interface
- Error handling and logging
- Internationalization support
- Security hardening

---

## Version History

- **1.0.0**: Initial release with core functionality
- **Future versions**: Will follow semantic versioning

## Contributing

To contribute to this changelog, please follow the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format and add entries under the appropriate version section.
