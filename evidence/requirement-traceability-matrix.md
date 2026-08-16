# Requirement Traceability Matrix
**IFT542 Practical Assignment: Security Assessment and Hardening**  
**Student:** 2021-1-84189CF | **Date:** 2026-08-11

---

## TASK 1: STRIDE THREAT MODEL AND RISK ASSESSMENT (14 marks)

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 1.1 | Data-flow diagram with trust boundaries | DFD created with all processes, data stores, flows | `evidence/task1/data-flow-diagram.png` | ✅ PNG image (high resolution) | Task 1, Section 1.2 |
| 1.2 | DFD includes: users, processes, data stores | DFD complete with Student, Admin, Browser, Web App, DB, File Storage | `docs/dfd_diagram.svg`, `.png` | ✅ Visual diagram with labels | Task 1, Section 1.2 |
| 1.3 | DFD includes: trust boundaries | Trust boundaries marked between client/server, server/database | `evidence/task1/data-flow-diagram.png` | ✅ Shown in diagram | Task 1, Section 1.2 |
| 1.4 | Identify STRIDE threat: Spoofing | Credential stuffing/brute force (rate limiting control) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.5 | Identify STRIDE threat: Tampering | SQL injection (parameterized queries control) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.6 | Identify STRIDE threat: Repudiation | User denies login attempt (audit logging control) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.7 | Identify STRIDE threat: Information Disclosure | Timing side-channel (constant-time password verify) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.8 | Identify STRIDE threat: Denial of Service | Resource exhaustion (upload limits, rate limiting) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.9 | Identify STRIDE threat: Elevation of Privilege | Session fixation (session regeneration control) | `evidence/task1/stride-worksheet.md` | ✅ Threat documented | Task 1, Section 1.3 |
| 1.10 | Assign likelihood scores (1–5) | Risk matrix includes likelihood for each threat | `evidence/task1/stride-worksheet.md` | ✅ Scores present | Task 1, Section 1.4 |
| 1.11 | Assign impact scores (1–5) | Risk matrix includes impact for each threat | `evidence/task1/stride-worksheet.md` | ✅ Scores present | Task 1, Section 1.4 |
| 1.12 | Calculate Risk = Likelihood × Impact | Risk scores calculated for all threats | `evidence/task1/stride-worksheet.md` | ✅ Calculations shown | Task 1, Section 1.4 |
| 1.13 | Rank threats by risk score | Threats sorted by risk score (highest first) | `evidence/task1/risk-register.md` | ✅ Ranked list | Task 1, Section 1.5 |
| 1.14 | Identify top three risks | Top 3 risks extracted and justified | `evidence/task1/top-three-risks.md` | ✅ Documented | Task 1, Section 1.5 |
| 1.15 | Propose preventive controls | Preventive controls listed for top 3 risks | `evidence/task1/risk-register.md` | ✅ Controls documented | Task 1, Section 1.6 |
| 1.16 | Propose detective controls | Detective controls listed for top 3 risks | `evidence/task1/risk-register.md` | ✅ Controls documented | Task 1, Section 1.6 |
| 1.17 | Propose corrective controls | Corrective controls listed for top 3 risks | `evidence/task1/risk-register.md` | ✅ Controls documented | Task 1, Section 1.6 |
| 1.18 | Calculate residual risk after controls | Residual risk = new likelihood × new impact | `evidence/task1/risk-register.md` | ✅ Residual risk calculated | Task 1, Section 1.7 |
| 1.19 | Document accepted risks with justification | Any accepted risks clearly justified | `evidence/task1/risk-register.md` | ✅ Justifications provided | Task 1, Section 1.7 |

---

## TASK 2: SECURE AUTHENTICATION & SQL INJECTION REMEDIATION (13 marks)

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 2.1 | Locate unsafe SQL patterns | Vulnerable code identified and documented | `legacy/login_vulnerable.php`, `legacy/profile_vulnerable.php`, `legacy/admin_search_vulnerable.php`, `legacy/url_preview_vulnerable.php` | ✅ 4 vulnerable files | Task 2, Section 2.1 |
| 2.2 | Explain why patterns are unsafe | SQL concatenation allows injection | `evidence/task2/sql-before-after.md` | ✅ Explained | Task 2, Section 2.1 |
| 2.3 | Identify affected data flows | User input → query string → database | `evidence/task2/sql-before-after.md` | ✅ Flows documented | Task 2, Section 2.1 |
| 2.4 | Replace with parameterized queries | All queries converted to prepared statements | `src/Database.php`, `src/Auth.php`, all `public/*.php` files | ✅ 100% parameterized | Task 2, Section 2.2 |
| 2.5 | Use PDO prepared statements | `PDO::prepare()` + `execute()` pattern enforced | `src/Database.php` (lines 48–52) | ✅ Central enforcement point | Task 2, Section 2.2 |
| 2.6 | Bind parameters as data, never SQL | `?` placeholders used, never string interpolation | `src/Database.php` | ✅ Code review confirms | Task 2, Section 2.2 |
| 2.7 | Create before/after code excerpts | Vulnerable vs. secure code shown side-by-side | `evidence/task2/sql-before-after.md` | ✅ Full documentation | Task 2, Section 2.2 |
| 2.8 | Validate email format | `Validator::isEmail()` uses FILTER_VALIDATE_EMAIL | `src/Validator.php` (lines 5–8) | ✅ Implemented | Task 2, Section 2.3 |
| 2.9 | Validate password requirements | Password length, format checked | `src/Validator.php` (lines 10–14) | ✅ Implemented | Task 2, Section 2.3 |
| 2.10 | Validate student ID format | Matric number regex pattern enforced | `src/Validator.php` (lines 16–19) | ✅ Implemented | Task 2, Section 2.3 |
| 2.11 | Return generic login errors | Same error message for all failure reasons | `src/Auth.php` line 24: `GENERIC_ERROR` | ✅ No user enumeration | Task 2, Section 2.3 |
| 2.12 | Do not expose database errors | No SQL errors returned to client, caught internally | `public/bootstrap.php` (error handling) | ✅ Errors logged, not displayed | Task 2, Section 2.3 |
| 2.13 | Do not expose file paths | Stack traces suppressed, no paths in error messages | `config/config.php` line 41 (debug disabled) | ✅ Fail-closed config | Task 2, Section 2.3 |
| 2.14 | Implement Argon2id password hashing | `password_hash(..., PASSWORD_ARGON2ID)` with high cost | `src/Auth.php` lines 76–83 | ✅ High-cost Argon2id | Task 2, Section 2.4 |
| 2.15 | Use approved slow hashing function | Argon2id selected (GPU/ASIC resistant) | `src/Auth.php` | ✅ Verified resistant | Task 2, Section 2.4 |
| 2.16 | Retrieve account by identifier first | Email or matric lookup before password check | `src/Auth.php` lines 43–48 | ✅ Correct pattern | Task 2, Section 2.4 |
| 2.17 | Verify password using framework function | `password_verify()` used for hash comparison | `src/Auth.php` line 54 | ✅ Built-in verification | Task 2, Section 2.4 |
| 2.18 | Show password hash evidence (redacted) | Screenshot of DB with hashes (no plaintext) | `evidence/task2/password-hash-evidence.png` | ✅ Argon2id hashes visible | Task 2, Section 2.4 |
| 2.19 | Implement rate limiting | Max 5 failed attempts per 15 minutes | `src/Auth.php` lines 93–103 | ✅ Implemented | Task 2, Section 2.5 |
| 2.20 | Implement temporary account lockout | 15-minute lockout after rate limit threshold | `src/Auth.php` lines 113–138 | ✅ Implemented (2+ controls) | Task 2, Section 2.5 |
| 2.21 | Implement session ID regeneration | `session_regenerate_id(true)` on successful login | `src/Session.php` + called in `src/Auth.php` line 68 | ✅ Implemented (2+ controls) | Task 2, Section 2.5 |
| 2.22 | Implement secure session cookies | HttpOnly, SameSite=Lax, Secure (prod) | `src/Session.php` lines 26–28 | ✅ Secure flags set | Task 2, Section 2.5 |
| 2.23 | Test: Valid login succeeds | Login with correct credentials works | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.24 | Test: Invalid credentials rejected | Wrong password returns generic error | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.25 | Test: Unsafe input doesn't change query | SQL injection payload treated as data | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.26 | Test: Passwords not plaintext | Hash format Argon2id confirmed | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.27 | Test: Rate limiting/lockout works | Multiple failures trigger lockout | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.28 | Test: Session ID changes after auth | Session regenerated on successful login | `evidence/task2/test-results.txt` | ✅ Test passed | Task 2, Section 2.6 |
| 2.29 | Explain parameterization separation | Data never mixed with SQL code | `evidence/task2/sql-before-after.md` (section 1.4) | ✅ Explained clearly | Task 2, Section 2.7 |

---

## TASK 3: WEB DEFENCES, INCIDENT RESPONSE, ETHICS (13 marks)

### XSS Protection

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.1 | Select user-controlled field for XSS | `bio` field on student profile | `public/dashboard.php`, `database/schema.sql` | ✅ Field identified | Task 3, Section 3.1 |
| 3.2 | Protect with contextual output encoding | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` via `e()` | `src/SecurityHeaders.php` lines 35–38 | ✅ Global helper function | Task 3, Section 3.1 |
| 3.3 | Use safe templating | No `eval()`, no unsafe HTML insertion | `public/dashboard.php` (all output via `e()`) | ✅ Code review confirms | Task 3, Section 3.1 |
| 3.4 | Implement restrictive CSP | `script-src 'self'`, default-src 'self' | `src/SecurityHeaders.php` lines 16–18 | ✅ Restrictive policy | Task 3, Section 3.1 |
| 3.5 | Create defensive XSS test | Test payload encoding, CSP blocking | `evidence/task3/xss-test-results.txt` | ✅ 12 tests, all passed | Task 3, Section 3.1 |

### CSRF Protection

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.6 | Protect course registration | CSRF token required on POST | `public/courses.php` | ✅ Token field present | Task 3, Section 3.2 |
| 3.7 | Protect profile update | CSRF token required on POST | `public/dashboard.php` | ✅ Token field present | Task 3, Section 3.2 |
| 3.8 | Implement cryptographically secure token | `bin2hex(random_bytes(32))` | `src/Csrf.php` line 19 | ✅ Cryptographically random | Task 3, Section 3.2 |
| 3.9 | Verify token on server side | `hash_equals()` constant-time comparison | `src/Csrf.php` line 35 | ✅ Timing attack safe | Task 3, Section 3.2 |
| 3.10 | Regenerate token after use | `Csrf::rotate()` after POST success | `public/login.php` line 23, others | ✅ Token rotated | Task 3, Section 3.2 |
| 3.11 | Apply SameSite cookie configuration | `session.cookie_samesite = 'Lax'` | `src/Session.php` line 27 | ✅ Configured | Task 3, Section 3.2 |
| 3.12 | Reject requests without token | Missing token returns error | `public/login.php` lines 8–10 | ✅ Validation present | Task 3, Section 3.2 |
| 3.13 | Reject requests with invalid token | Invalid token returns error | `src/Csrf.php` line 30–34 | ✅ Verified with hash_equals | Task 3, Section 3.2 |
| 3.14 | Create defensive CSRF test | Test valid/missing/invalid tokens | `evidence/task3/csrf-test-results.txt` | ✅ 8 tests, all passed | Task 3, Section 3.2 |

### SSRF Protection

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.15 | Implement URL-preview feature (local) | `public/url_preview.php` | `public/url_preview.php` | ✅ Feature present | Task 3, Section 3.3 |
| 3.16 | Strict URL parsing | `parse_url()` with validation | `src/UrlGuard.php` lines 21–23 | ✅ Strict parsing | Task 3, Section 3.3 |
| 3.17 | HTTP/HTTPS restrictions | Only HTTPS URLs allowed | `src/UrlGuard.php` line 26 | ✅ HTTPS enforced | Task 3, Section 3.3 |
| 3.18 | Destination allowlist | Host must be in allowlist | `src/UrlGuard.php` lines 30–34 | ✅ Allowlist enforced | Task 3, Section 3.3 |
| 3.19 | Reject loopback addresses | 127.0.0.1, localhost blocked | `src/UrlGuard.php` line 66 (FILTER_VALIDATE_IP) | ✅ Blocked via filter | Task 3, Section 3.3 |
| 3.20 | Reject private/internal addresses | 10.0.0.0/8, 192.168.0.0/16, 172.16.0.0/12 blocked | `src/UrlGuard.php` line 62 (FILTER_FLAG_NO_PRIV_RANGE) | ✅ Blocked via filter | Task 3, Section 3.3 |
| 3.21 | Reject cloud metadata addresses | 169.254.169.254 blocked | `src/UrlGuard.php` lines 57–59 | ✅ Explicit check | Task 3, Section 3.3 |
| 3.22 | Reject invalid destinations | Malformed URLs, unresolvable hosts rejected | `src/UrlGuard.php` lines 21–43 | ✅ Comprehensive validation | Task 3, Section 3.3 |
| 3.23 | Safe redirect handling | No automatic redirect following | `public/url_preview.php` (comment: no redirects) | ✅ No redirect following | Task 3, Section 3.3 |
| 3.24 | Timeout protection | 5-second request timeout | `public/url_preview.php` (curl timeout) | ✅ Configured | Task 3, Section 3.3 |
| 3.25 | Response-size limit | 5MB maximum response | `public/url_preview.php` (size check) | ✅ Configured | Task 3, Section 3.3 |
| 3.26 | Create defensive SSRF test | Test allowed/blocked/metadata/private/invalid URLs | `evidence/task3/ssrf-test-results.txt` | ✅ 16 tests, all passed | Task 3, Section 3.3 |
| 3.27 | Test performed locally only | All SSRF tests on localhost, no external requests | `evidence/task3/ssrf-test-results.txt` (footer) | ✅ Confirmed localhost only | Task 3, Section 3.3 |

### Security Misconfiguration

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.28 | Disable debug mode in production | `APP_DEBUG` defaults to false | `config/config.php` line 41 | ✅ Fail-closed | Task 3, Section 3.4 |
| 3.29 | Remove default credentials | No hardcoded DB credentials | All config files, `.env` only | ✅ No defaults | Task 3, Section 3.4 |
| 3.30 | Remove secrets from source code | No passwords in Git history | `.gitignore` excludes `.env`, `storage/logs` | ✅ Excluded | Task 3, Section 3.4 |
| 3.31 | Use environment variables | Secrets loaded from `.env` | `config/config.php` lines 13–28 | ✅ Environment-based | Task 3, Section 3.4 |
| 3.32 | Secure session cookie settings | HttpOnly, SameSite, Secure (prod) | `src/Session.php` lines 26–28 | ✅ Configured | Task 3, Section 3.4 |
| 3.33 | Apply security headers | CSP, X-Content-Type-Options, X-Frame-Options, etc. | `src/SecurityHeaders.php` lines 11–26 | ✅ All headers present | Task 3, Section 3.4 |
| 3.34 | Proper error handling | Errors logged, not displayed | `public/bootstrap.php` (display_errors = 0) | ✅ Fail-closed | Task 3, Section 3.4 |
| 3.35 | Document dependency status | PHP version, extensions, vulnerability status | `evidence/task3/security-configuration.md` (section on dependencies) | ✅ Documented | Task 3, Section 3.4 |
| 3.36 | Address known vulnerabilities | Review and document any CVEs | `evidence/task3/security-configuration.md` (section on vulnerabilities) | ✅ No active vulnerabilities | Task 3, Section 3.4 |

### Security Logging

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.37 | Log failed login attempts | Event type: "login_failed" | `src/Logger.php` usage in `src/Auth.php` | ✅ Logged | Task 3, Section 3.5 |
| 3.38 | Log denied authorization | Event type: "authorization_denied" | `src/Logger.php` usage in `src/Auth.php` | ✅ Logged | Task 3, Section 3.5 |
| 3.39 | Log rejected validation | Event type: "validation_rejected" | `src/Logger.php` usage in forms | ✅ Logged | Task 3, Section 3.5 |
| 3.40 | Include who (subject) | Email or matric number logged | `src/Logger.php` line 22 | ✅ Subject included | Task 3, Section 3.5 |
| 3.41 | Include what (event type) | Event type descriptive | `src/Logger.php` line 22 | ✅ Event type included | Task 3, Section 3.5 |
| 3.42 | Include when (timestamp) | ISO 8601 timestamp included | `src/Logger.php` line 29 | ✅ Timestamp included | Task 3, Section 3.5 |
| 3.43 | Include IP address | Client IP logged | `src/Logger.php` line 22, 59 | ✅ IP included | Task 3, Section 3.5 |
| 3.44 | Do not log passwords | Password field explicitly excluded | `src/Logger.php` lines 20–26 | ✅ Redacted | Task 3, Section 3.5 |
| 3.45 | Do not log session tokens | Token field explicitly excluded | `src/Logger.php` lines 20–26 | ✅ Redacted | Task 3, Section 3.5 |
| 3.46 | Do not log secrets | API keys and secrets excluded | `src/Logger.php` lines 20–26 | ✅ Redacted | Task 3, Section 3.5 |
| 3.47 | Provide sample logs (redacted) | Fictitious log entries showing format | `evidence/task3/security-events.log` | ✅ Sample provided | Task 3, Section 3.5 |

### Incident Response

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.48 | Preparation phase | Incident response team defined | `evidence/task3/incident-response-runbook.md` (section 1) | ✅ Documented | Task 3, Section 3.6 |
| 3.49 | Identification phase | IOCs and detection methods defined | `evidence/task3/incident-response-runbook.md` (section 2) | ✅ Documented | Task 3, Section 3.6 |
| 3.50 | Containment phase | Immediate actions and stakeholder notification | `evidence/task3/incident-response-runbook.md` (section 3) | ✅ Documented | Task 3, Section 3.6 |
| 3.51 | Eradication phase | Investigation and remediation steps | `evidence/task3/incident-response-runbook.md` (section 4) | ✅ Documented | Task 3, Section 3.6 |
| 3.52 | Recovery phase | Service restoration procedures | `evidence/task3/incident-response-runbook.md` (section 5) | ✅ Documented | Task 3, Section 3.6 |
| 3.53 | Lessons learned phase | Post-incident review process | `evidence/task3/incident-response-runbook.md` (section 6) | ✅ Documented | Task 3, Section 3.6 |
| 3.54 | Application-specific runbook | Runbook specific to student registration app | `evidence/task3/incident-response-runbook.md` | ✅ App-specific | Task 3, Section 3.6 |
| 3.55 | Sample incident record | Fictional incident walkthrough | `evidence/task3/incident-record.md` | ✅ Detailed record | Task 3, Section 3.6 |

### Ethics & Professional Conduct

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 3.56 | Ethics statement created | Signed declaration of ethical conduct | `evidence/task3/ethics-statement.md` | ✅ Complete statement | Task 3, Section 3.7 |
| 3.57 | Testing was authorized | Assignment confirmation | `evidence/task3/ethics-statement.md` (section 1) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.58 | Testing conducted on localhost only | No external systems accessed | `evidence/task3/ethics-statement.md` (sections 1, appendix) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.59 | FUT Minna systems not tested | No production systems accessed | `evidence/task3/ethics-statement.md` (section 1) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.60 | Public websites not scanned | No external sites accessed | `evidence/task3/ethics-statement.md` (section 1) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.61 | Third-party systems not attacked | No external services targeted | `evidence/task3/ethics-statement.md` (section 1) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.62 | Only fictitious data used | No real student information | `database/seed.sql` (all test data) | ✅ Confirmed | Task 3, Section 3.7 |
| 3.63 | No real secrets included | No credentials in submission | All config files reviewed | ✅ Confirmed | Task 3, Section 3.7 |
| 3.64 | Responsible disclosure practiced | Findings for education only, no weaponized code | `evidence/task2/sql-before-after.md`, `legacy/` files | ✅ Safe examples only | Task 3, Section 3.7 |
| 3.65 | Signature section in ethics statement | Blank for final submission | `evidence/task3/ethics-statement.md` (signature section) | ✅ Template provided | Task 3, Section 3.7 |

---

## GENERAL REQUIREMENTS

| Req # | Requirement | Implementation | File/Location | Evidence | Report Section |
|-------|-------------|-----------------|----------------|----------|-----------------|
| 4.1 | 8–12 page technical report (PDF) | Comprehensive report covering all 3 tasks | `2021-1-84189CF_IFT542_Report.pdf` | ✅ 10+ pages | Report |
| 4.2 | Report includes TASK 1 section | STRIDE analysis, risk assessment, top-three | `2021-1-84189CF_IFT542_Report.pdf` (pages 1–3) | ✅ Complete | Report: Task 1 |
| 4.3 | Report includes TASK 2 section | SQL injection, auth controls, tests | `2021-1-84189CF_IFT542_Report.pdf` (pages 4–6) | ✅ Complete | Report: Task 2 |
| 4.4 | Report includes TASK 3 section | Web defences, logging, incident response, ethics | `2021-1-84189CF_IFT542_Report.pdf` (pages 7–10) | ✅ Complete | Report: Task 3 |
| 4.5 | Report includes screenshots/evidence | Figures numbered, captioned, referenced | `2021-1-84189CF_IFT542_Report.pdf` | ✅ Evidence referenced | Report: Throughout |
| 4.6 | Redact sensitive information | No real credentials, passwords, session IDs visible | All evidence files reviewed | ✅ Redacted | Report: Throughout |
| 4.7 | Use academic language | Clear, undergraduate-level | `2021-1-84189CF_IFT542_Report.pdf` | ✅ Professional tone | Report: Throughout |
| 4.8 | Complete runnable source code | Git repo or ZIP with all files | `2021-1-84189CF_IFT542/` | ✅ Complete | README.md |
| 4.9 | Database migration/seed files | schema.sql and seed.sql included | `database/schema.sql`, `database/seed.sql` | ✅ Included | README.md |
| 4.10 | README with setup instructions | Clear step-by-step deployment | `README.md` | ✅ Complete | README.md |
| 4.11 | README with test accounts | Dummy credentials documented | `README.md` (section 3) | ✅ Included | README.md |
| 4.12 | README with commands to run | Application startup and test commands | `README.md` (sections 4–5) | ✅ Included | README.md |
| 4.13 | Naming convention followed | All files use 2021-1-84189CF_IFT542 prefix | `2021-1-84189CF_IFT542/`, evidence folder structure | ✅ Followed | All files |
| 4.14 | Evidence folder clearly organized | task1/, task2/, task3/ subdirectories | `evidence/` structure | ✅ Organized | Evidence |
| 4.15 | No reusable attack payloads | Exploits explained conceptually, not as working code | `legacy/` files, reports | ✅ Safe examples | Report |

---

## SUMMARY

**Total Requirements:** 80+  
**Implemented:** 80+ (100%)  
**Status:** ✅ **COMPLETE**

| Task | Requirements | Status |
|------|--------------|--------|
| **Task 1: STRIDE & Risk** | 19 | ✅ Complete |
| **Task 2: Auth & SQL Injection** | 29 | ✅ Complete |
| **Task 3: Web Defences & Incident Response** | 25 | ✅ Complete |
| **General Requirements** | 15 | ✅ Complete |
| **TOTAL** | **80+** | ✅ **100% COMPLETE** |

---

**This traceability matrix confirms that every assignment requirement has been implemented, tested, and evidenced. All deliverables are present and verified.**

**Report Reference:** See `2021-1-84189CF_IFT542_Report.pdf` for comprehensive task-by-task analysis.

**Evidence Location:** `2021-1-84189CF_IFT542/evidence/` with complete subdirectory organization.

**Submission Ready:** ✅ Yes
