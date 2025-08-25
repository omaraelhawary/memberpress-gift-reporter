# Contributing to MemberPress Gift Reporter

Thank you for your interest in contributing to MemberPress Gift Reporter! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites

- WordPress 5.0+
- PHP 7.4+
- MemberPress plugin (active)
- MemberPress Gifting add-on (active)
- Git

### Development Setup

1. **Fork the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/memberpress-gift-reporter.git
   cd memberpress-gift-reporter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up WordPress development environment**
   - Install WordPress locally
   - Install and activate MemberPress
   - Install and activate MemberPress Gifting add-on
   - Symlink or copy the plugin to your WordPress plugins directory

## 📝 Development Guidelines

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use PHP 7.4+ features appropriately
- Follow PSR-12 for general PHP formatting
- Use meaningful variable and function names
- Add proper PHPDoc comments

### File Structure

```
memberpress-gift-reporter/
├── memberpress-gift-reporter.php    # Main plugin file
├── includes/                        # Core functionality
│   ├── class-gift-report.php       # Report generation
│   └── class-admin.php             # Admin interface
├── assets/                         # Frontend assets
│   ├── css/
│   └── js/
├── languages/                      # Translation files
└── tests/                         # Unit tests (future)
```

### Database Considerations

- Use WordPress database functions (`$wpdb`)
- Always use prepared statements
- Consider performance for large datasets
- Add appropriate indexes for queries

## 🧪 Testing

### Manual Testing

1. **Install the plugin** in a test WordPress environment
2. **Create test gift transactions** using MemberPress Gifting
3. **Verify report generation** works correctly
4. **Test advanced filtering system** with all 8 filters
5. **Test CSV export** functionality (filtered and unfiltered)
6. **Check admin interface** responsiveness and mobile optimization
7. **Test smart no-data detection** with various filter combinations
8. **Verify filter clearing** functionality works correctly

### Code Quality

Run PHP CodeSniffer to check code quality:

```bash
composer run phpcs
```

To automatically fix some issues:

```bash
composer run phpcbf
```

## 🔧 Making Changes

### Branch Strategy

1. **Create a feature branch** from `main`
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following the coding standards

3. **Test thoroughly** before submitting

4. **Commit with clear messages**
   ```bash
   git commit -m "Add feature: brief description of changes"
   ```

### Commit Message Format

Use clear, descriptive commit messages:

- `Add feature: advanced filtering system with 8 filters`
- `Add feature: smart no-data detection`
- `Fix bug: incorrect date formatting in reports`
- `Update UI: modern responsive design with grid layout`
- `Update docs: add installation troubleshooting`
- `Refactor: improve database query performance`
- `Enhance UX: mobile optimization and touch-friendly interface`

## 📤 Submitting Changes

### Pull Request Process

1. **Push your branch** to your fork
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create a Pull Request** on GitHub

3. **Fill out the PR template** with:
   - Description of changes
   - Testing performed
   - Screenshots (if UI changes)
   - Related issues

4. **Wait for review** and address feedback

### PR Requirements

- [ ] Code follows WordPress coding standards
- [ ] PHPCS passes without errors
- [ ] Manual testing completed
- [ ] Documentation updated (if needed)
- [ ] No breaking changes (or clearly documented)

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

1. **WordPress version**
2. **PHP version**
3. **MemberPress version**
4. **Plugin version**
5. **Steps to reproduce**
6. **Expected vs actual behavior**
7. **Error messages** (if any)
8. **Screenshots** (if applicable)

### Feature Requests

For feature requests:

1. **Clear description** of the feature
2. **Use case** and benefits
3. **Proposed implementation** (if you have ideas)
4. **Priority level** (low/medium/high)

## 📚 Documentation

### Code Documentation

- Add PHPDoc comments for all functions and classes
- Include parameter types and return types
- Document any complex logic or business rules

### User Documentation

- Update README.md for user-facing changes
- Update INSTALL.md for installation changes
- Add inline comments for complex features

## 🔒 Security

### Security Guidelines

- Never commit sensitive data (API keys, passwords)
- Validate and sanitize all user inputs
- Use WordPress nonces for forms
- Check user capabilities before actions
- Use prepared statements for database queries

### Reporting Security Issues

If you find a security vulnerability:

1. **Do NOT create a public issue**
2. **Email the maintainer** directly
3. **Provide detailed information** about the vulnerability
4. **Wait for response** before public disclosure

## 🤝 Community Guidelines

### Be Respectful

- Be kind and respectful to other contributors
- Provide constructive feedback
- Help newcomers learn and contribute

### Communication

- Use clear, professional language
- Ask questions if something is unclear
- Share knowledge and best practices

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the same license as the project (GPL v2 or later).

## 🆘 Need Help?

If you need help contributing:

1. **Check existing issues** and discussions
2. **Ask questions** in GitHub discussions
3. **Contact the maintainer** directly

Thank you for contributing to MemberPress Gift Reporter! 🎁
