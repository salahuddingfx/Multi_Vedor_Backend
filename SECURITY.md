# 🛡️ Security Policy

## Supported Versions
We are committed to the security of our users. Only the following versions of the platform are currently receiving security updates:

| Version | Supported |
| ------- | --------- |
| v1.0.x  | ✅         |
| < v1.0  | ❌         |

## Our Commitment
As the central engine of the multi-vendor ecosystem, the Nexus Core Backend implements rigorous security protocols to ensure data integrity and system availability.

## Reporting a Vulnerability
If you discover a security vulnerability within this project, please **do not disclose it publicly**. We take all reports seriously and will work to resolve them as quickly as possible.

### How to report:
1.  Send a detailed email to **salauddinkaderappy@gmail.com**.
2.  Include a description of the vulnerability, steps to reproduce, and potential impact.
3.  Expect a response within 24-48 hours.

## Security Practices
- **Sanctum Authentication**: Multi-layered token authentication with scope-based permissions.
- **SQL Injection Prevention**: Exclusive use of Eloquent ORM and Query Builder with prepared statements.
- **Data Isolation**: Multi-tenant architecture ensures strict logical separation of site-specific data.
- **CORS Protection**: Fine-grained Cross-Origin Resource Sharing policies to prevent unauthorized API access.
- **Rate Limiting**: Integrated throttle layers to prevent brute-force and DDoS attempts on critical endpoints.
