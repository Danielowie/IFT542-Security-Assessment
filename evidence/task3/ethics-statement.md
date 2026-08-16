# Ethics Statement: IFT542 Practical Assignment

**Student Name:** Daniel Dan O 
**Matric Number:** 2021-1-84189CF  
**Course:** IFT 542 Web Application Security  
**Institution:** Federal University of Technology, Minna

---

## ETHICAL COMMITMENT

I, the undersigned, hereby declare that all security assessment and testing activities conducted for this IFT542 practical assignment have been performed in accordance with the highest professional and ethical standards. I confirm the following:

### Testing Authorization & Scope

✅ **Testing was authorized:** This assignment has been assigned as part of the coursework for IFT 542.

✅ **Testing conducted on authorized systems only:** All security testing was performed exclusively on localhost (127.0.0.1:8000) using the provided starter application running locally on my own computer/lab environment. No testing was conducted on any external systems without explicit permission.

✅ **FUT Minna production systems NOT tested:** I did not, at any point, conduct any form of security testing, scanning, probing, port enumeration, or vulnerability analysis on any production systems belonging to the Federal University of Technology, Minna. This includes:
- FUT Minna web servers
- FUT Minna databases
- FUT Minna network infrastructure
- Any FUT Minna email or authentication systems
- Any other FUT Minna IT assets

✅ **Public websites and third-party systems NOT tested:** I did not scan, probe, attack, or test any public websites or third-party services. All testing was confined to the locally-running student registration application.

### Data & Credentials

✅ **Only fictitious data used:** The application was seeded with completely fictitious student and administrator data. No real student information, email addresses, or personal details from FUT Minna were used in any way. All test accounts use dummy credentials (examples: student1@example.test, admin@example.test, password: Tr0ub4dor&3).

✅ **No real credentials included:** The submission contains no real passwords, API keys, session tokens, database credentials, or any other sensitive secrets. All configuration uses environment variables with safe placeholder values. The `.env` file is excluded from version control and contains no hardcoded secrets.

✅ **No hardcoded secrets in source code:** A thorough review confirms that no passwords, database credentials, API keys, or other sensitive information are present in any source file, configuration file, or documentation submitted with this assignment.

### Responsible Disclosure & Ethical Conduct

✅ **Findings presented for educational purposes only:** All vulnerabilities documented in this assignment (including before/after code examples in `legacy/` folder) are presented strictly for educational purposes to demonstrate understanding of security concepts. These examples are not intended to and cannot be used as practical attack tools.

✅ **Reusable attack payloads NOT included:** The technical report and evidence do not contain complete, functional, copy-paste-ready attack payloads. Conceptual examples are explained at a level suitable for academic discussion but cannot be directly weaponized without substantial additional work.

✅ **No malicious intent:** This assignment represents an honest, good-faith effort to understand and practice web application security principles as required by the course. No testing or analysis was conducted with any intent to:
- Compromise system availability
- Access unauthorized data
- Disrupt services
- Harm individuals or the institution
- Enable future unauthorized access

✅ **Responsible attitude toward security:** I understand that security testing can cause real harm if misused. I commit to:
- Only test systems I have explicit permission to test
- Always obtain written authorization before any security assessment
- Never use security knowledge to harm others or gain unauthorized access
- Report vulnerabilities responsibly (not publicly, to authorized parties only)
- Follow all applicable laws and regulations

### Integrity of Work

✅ **Original work:** This assignment represents my own work. All code, analysis, and documentation are either original work I created or are properly attributed to external sources (e.g., PHP documentation, assignment specification).

✅ **No plagiarism:** I have not copied security analysis, code explanations, or incident response procedures from other students or sources without attribution.

✅ **Honest assessment:** I have documented the security controls as they actually are implemented, not as I wish them to be. Any limitations or residual risks are clearly noted.

### Compliance with Assignment Requirements

✅ **Adhered to all restrictions:** I have strictly followed all restrictions outlined in the assignment:
- ✅ Testing on localhost only
- ✅ No FUT Minna systems accessed
- ✅ No public websites probed
- ✅ No third-party services attacked
- ✅ No real credentials used
- ✅ No reusable attack code included
- ✅ All testing ethical and documented

✅ **Fulfilled all requirements:** I have completed all required practical activities:
- ✅ STRIDE threat model with 6 application-specific threats
- ✅ Risk assessment with likelihood/impact scoring
- ✅ SQL injection vulnerability identification and remediation
- ✅ Secure authentication implementation (Argon2id + rate limiting)
- ✅ XSS protection with output encoding and CSP
- ✅ CSRF protection with per-session tokens
- ✅ SSRF protection with URL validation and IP checks
- ✅ Security misconfiguration hardening
- ✅ Security event logging
- ✅ Incident response runbook
- ✅ Automated security test suite

---

## ACKNOWLEDGMENT OF RESPONSIBILITY

I understand that:

1. **Security testing can cause harm** if misused or conducted without authorization. I accept responsibility for ensuring my actions remain ethical and legal.

2. **Unauthorized access is illegal** under computer fraud and cybercrime laws in Nigeria and internationally. I will not conduct any testing outside the scope explicitly authorized by this assignment.

3. **Confidentiality is critical.** Any vulnerabilities discovered in real systems should never be disclosed publicly or used without the organization's knowledge and consent.

4. **Professional ethics matter.** Security professionals have a duty to use their knowledge responsibly. This assignment is an opportunity to demonstrate that commitment.

---

## DECLARATION

I hereby declare, under my own signature (below), that:

1. All security testing conducted was authorized (as part of IFT542 coursework)
2. All testing was conducted on localhost only
3. No FUT Minna production systems were accessed or tested
4. No public websites were scanned or attacked
5. No third-party services were targeted
6. Only fictitious data was used
7. No real credentials or secrets are included in this submission
8. No reusable attack code is included in documentation
9. All work is original and properly attributed
10. This assignment represents an honest demonstration of security knowledge and ethical conduct

I understand that submission of this assignment constitutes affirmation of this ethics statement. Any false declarations may result in academic consequences and disciplinary action.

---

## SIGNATURE SECTION

**Student Declaration:**

By signing below, I confirm that I have read this ethics statement and agree to comply with all terms and conditions outlined above.

**Name:** Owie  

**Matric Number:** 2021-1-84189CF  

**Date:** _________________ (To be completed upon final submission)

**Signature:** ________________________  

---

## WITNESS (Optional)

**Witnessed By (e.g., Dr. Bashir or laboratory assistant):**

**Name:** _________________________________

**Title:** _________________________________

**Date:** _________________________________

**Signature:** _____________________________

---

**This ethics statement is an integral part of the IFT542 practical assignment submission. The student's signature above indicates understanding and acceptance of all ethical principles outlined herein.**

---

## APPENDIX: PROOF OF LOCALHOST-ONLY TESTING

### Network Configuration

All testing was performed using:
- **Server:** PHP built-in development server (`php -S localhost:8000`)
- **Database:** Local MySQL instance (127.0.0.1:3306)
- **Network Interface:** Loopback (127.0.0.1) / Local network only
- **Access:** No external connectivity required or utilized

### .env Configuration (Localhost Only)

```
APP_ENV=testing
APP_DEBUG=false
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=student_registration
DB_USER=root
DB_PASS=(local password, no external credentials)
SESSION_NAME=srapp_session
URL_PREVIEW_ALLOWLIST=example.com,trusted-site.test
```

**Note:** The URL preview feature (for SSRF testing) is configured with example.com and trusted-site.test, which are:
- Fictitious test domains (no real requests made)
- Used for local testing only
- Not contacted by the application
- Evidence shows only localhost testing occurred

### Test Evidence

All test results in `evidence/task3/*.txt` show:
- Testing conducted on `127.0.0.1` (localhost)
- No external IP addresses contacted
- No external services queried
- No real domains accessed
- All testing was simulated/isolated

### Running the Application

Setup and execution instructions in README.md confirm localhost-only operation:

```bash
# Database setup (local)
mysql -u root -p < database/schema.sql
mysql -u root -p student_registration < database/seed.sql

# Start server (localhost only)
php -S localhost:8000 -t public

# Access via browser (localhost)
http://localhost:8000/

# Run tests (localhost)
php tests/security_tests.php
```

No external connectivity is required or established at any point.

---

**This ethics statement certifies that the IFT542 practical assignment has been completed in accordance with the highest standards of academic integrity and professional ethics.**
