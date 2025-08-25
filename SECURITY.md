# Security Policy

## Supported Versions

Use this section to tell people about which versions of your project are currently being supported with security updates.

| Version | Supported          |
| ------- | ------------------ |
| 1.1.x   | :white_check_mark: |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability in MemberPress Gift Reporter, please follow these steps:

### **DO NOT** create a public GitHub issue for security vulnerabilities.

### Instead, please:

1. **Email the maintainer directly** at: omaraelhawary@gmail.com
2. **Use the subject line**: `[SECURITY] MemberPress Gift Reporter - [Brief Description]`
3. **Provide detailed information** about the vulnerability including:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if you have one)

### What to expect:

- **Response within 48 hours** acknowledging receipt
- **Investigation period** to assess the vulnerability
- **Timeline for fix** if confirmed
- **Credit in security advisory** (if you wish to be credited)

### Responsible Disclosure:

- We will work with you to coordinate disclosure
- We will credit you in the security advisory (unless you prefer to remain anonymous)
- We will provide a reasonable timeline for fixes

## Security Best Practices

When using this plugin:

1. **Keep WordPress updated** to the latest version
2. **Keep MemberPress updated** to the latest version
3. **Use strong passwords** for admin accounts
4. **Limit admin access** to trusted users only
5. **Regularly backup** your database
6. **Monitor logs** for suspicious activity

## Security Features

This plugin implements several security measures:

- **Nonce verification** for all forms and AJAX requests
- **Capability checks** to ensure only authorized users can access reports
- **Enhanced input sanitization** for all user inputs (email, dates, integers)
- **Prepared statements** for all database queries
- **Admin-only access** to sensitive functionality
- **XSS protection** with proper output escaping
- **SQL injection prevention** with improved query preparation

## Updates

Security updates will be released as patch versions (e.g., 1.0.1, 1.0.2) and should be applied as soon as possible.

Thank you for helping keep MemberPress Gift Reporter secure! 🔒
