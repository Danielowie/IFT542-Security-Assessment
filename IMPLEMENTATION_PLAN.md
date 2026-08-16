# IMPLEMENTATION PLAN
# IFT 542 Practical Assignment: Security Assessment and Hardening
# Student: Owie (Matric: 2021-1-84189CF)

---

## EXECUTIVE SUMMARY

The IFT542 practical assignment requires the creation of a secure student registration web application demonstrating three major security engineering activities:

1. **TASK 1 (14 marks)**: STRIDE threat model and risk assessment
2. **TASK 2 (13 marks)**: Secure authentication and SQL injection remediation
3. **TASK 3 (13 marks)**: Web defences (XSS, CSRF, SSRF), incident response, and ethics

The provided starter application (PHP 8.1+ / MySQL) is **well-structured and already contains most required security controls**. This plan outlines:
- Existing application structure and implemented controls
- Identified gaps and completion tasks
- Evidence generation strategy
- Testing and validation approach

---

## PART 1: EXISTING APPLICATION STRUCTURE

### 1.1 Technology Stack

| Component | Technology |
|-----------|-------------|
| **Language** | PHP 8.1+ (strict types, OOP) |
| **Database** | MySQL 8+ (InnoDB, foreign keys) |
| **Web Framework** | Custom lightweight MVC (no external framework dependency) |
| **Sessions** | PHP native (securely configured) |
| **Authentication** | Argon2id password hashing, rate limiting, lockout |
| **Testing** | Plain PHP unit test suite (no Composer dependency) |

### 1.2 Project Structure

```
2021-1-84189CF_IFT542/
├── README.md                              # Setup and usage guide
├── IMPLEMENTATION_PLAN.md                 # This file
├── config/
│   └── config.php                         # Environment-based configuration (no hardcoded secrets)
├── database/
│   ├── schema.sql                         # Full MySQL schema with security tables
│   └── seed.sql                           # Fictitious test data
├── public/                                # Web root
│   ├── bootstrap.php                      # Session init, security headers, autoloader
│   ├── index.php                          # Landing page
│   ├── login.php                          # Secure authentication
│   ├── register.php                       # Student registration
│   ├── dashboard.php                      # Student dashboard
│   ├── courses.php                        # Course registration
│   ├── upload.php                         # Document upload
│   ├── url_preview.php                    # SSRF-guarded link preview
│   ├── password_reset_request.php         # Password reset initiation
│   ├── password_reset_confirm.php         # Password reset verification
│   ├── logout.php                         # Logout handler
│   ├── admin/                             # Admin-only section
│   │   ├── index.php                      # Admin dashboard
│   │   ├── courses.php                    # Course management
│   │   └── students.php                   # Student management
│   └── assets/
│       └── style.css                      # Basic styling
├── src/                                   # Core security classes
│   ├── Database.php                       # PDO wrapper (parameterized queries only)
│   ├── Auth.php                           # Authentication + rate limiting + lockout
│   ├── Session.php                        # Secure session management
│   ├── Csrf.php                           # CSRF token generation and verification
│   ├── Validator.php                      # Input validation
│   ├── Logger.php                         # Security event logging
│   ├── UrlGuard.php                       # SSRF protection
│   └── SecurityHeaders.php                # Output encoding + security headers
├── storage/
│   ├── logs/                              # Security event logs (JSON lines + DB)
│   └── uploads/                           # Document uploads (outside web root)
├── tests/
│   ├── README.md                          # Manual test plan for screenshots
│   └── run_tests.php                      # Automated unit test suite
├── legacy/                                # VULNERABLE baseline (comparison only)
│   ├── login_vulnerable.php               # SQL injection + weak auth example
│   ├── profile_vulnerable.php             # Stored XSS example
│   ├── url_preview_vulnerable.php         # SSRF example
│   └── admin_search_vulnerable.php        # Authorization bypass example
├── docs/                                  # Evidence and documentation
│   ├── dfd_diagram.png                    # Data-flow diagram (visual)
│   ├── dfd_diagram.svg                    # Data-flow diagram (editable)
│   ├── dfd.dot                            # GraphViz source
│   ├── stride_worksheet.md                # STRIDE analysis with mitigations
│   ├── risk_register.md                   # Risk rankings and controls
│   └── sample_logs/
│       └── security.log.sample            # Redacted log example
├── scripts/
│   └── generate_hash.php                  # CLI utility: Argon2id hash generator
├── SECURITY_NOTES.md                      # Before/after code explanations
└── .env.example                           # Environment variable template

```

### 1.3 Database Schema Overview

| Table | Purpose | Security Feature |
|-------|---------|------------------|
| **users** | Student and admin accounts | Argon2id hashes, no plaintext passwords |
| **login_attempts** | Append-only audit log | Rate limiting decisions, no password storage |
| **account_lockouts** | Temporary lockout state | Account lockout mechanism (activity 14) |
| **password_resets** | Single-use reset tokens | Hashed tokens only, time-limited |
| **courses** | Course records | Capacity checks, referential integrity |
| **enrolments** | Student-course relationships | Serialised writes prevent race conditions |
| **documents** | Upload metadata | Random filenames, MIME validation logged |
| **security_events** | Structured audit trail | Queryable mirror of security.log |

---

## PART 2: IMPLEMENTED SECURITY CONTROLS

### 2.1 Task 2 Controls: Secure Authentication & SQL Injection

✅ **Already Implemented:**

| Control | Implementation | Code Location |
|---------|----------------|----------------|
| **Parameterized Queries** | PDO prepared statements, `PDO::ATTR_EMULATE_PREPARES=false` | `src/Database.php` |
| **Argon2id Password Hashing** | `password_hash(..., PASSWORD_ARGON2ID)` with high cost factors | `src/Auth.php` (line 76-82) |
| **Rate Limiting** | Max 5 failed attempts per 15-minute window (IP + identifier) | `src/Auth.php` (lines 93-102) |
| **Temporary Account Lockout** | 15-minute lockout after 5 failed attempts | `src/Auth.php` (lines 113-138) |
| **Session ID Regeneration** | `session_regenerate_id(true)` on successful login | `src/Session.php`, called from `src/Auth.php` line 68 |
| **Generic Login Errors** | Same error message for all auth failures | `src/Auth.php` line 24 |
| **Timing Attack Defence** | `password_verify()` always runs (dummy hash if user not found) | `src/Auth.php` lines 50-54 |
| **Input Validation** | Email and matric format checks | `src/Validator.php` |
| **Secure Password Reset** | Single-use hashed tokens, time-limited | `public/password_reset_*.php` |

### 2.2 Task 3 Controls: Web Defences & Incident Response

✅ **Already Implemented:**

| Control | Implementation | Code Location |
|---------|----------------|----------------|
| **Output Encoding (XSS)** | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` via `e()` helper | `src/SecurityHeaders.php` line 35-38 |
| **Content Security Policy** | `script-src 'self'`, restrictive by default | `src/SecurityHeaders.php` line 16-18 |
| **CSRF Protection** | Per-session tokens, `hash_equals()` verification, SameSite=Lax | `src/Csrf.php`, `src/Session.php` |
| **CSRF Token Rotation** | Token regenerated after successful use | `src/Csrf.php` line 39-42 |
| **SSRF Protection** | Host allowlist, HTTPS-only, DNS-rebinding check, no redirect-following | `src/UrlGuard.php` |
| **Metadata Address Rejection** | `169.254.169.254` blocked explicitly | `src/UrlGuard.php` line 58 |
| **Private IP Rejection** | `FILTER_FLAG_NO_PRIV_RANGE` + `FILTER_FLAG_NO_RES_RANGE` | `src/UrlGuard.php` line 62-66 |
| **Security Headers** | CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS | `src/SecurityHeaders.php` line 11-26 |
| **Secure Session Cookies** | HttpOnly, Secure (prod), SameSite=Lax | `src/Session.php` |
| **Role-Based Authorization** | Server-side re-check on every admin page | `src/Auth.php` lines 185-195 |
| **Document Upload Security** | MIME sniffing (finfo), random filenames, storage outside web root | `public/upload.php` |
| **File Upload Validation** | Allowlist (PDF, DOC, DOCX), 5MB size limit | `public/upload.php` |
| **Security Event Logging** | Failed login, denied auth, validation rejection to file + DB | `src/Logger.php` |
| **No Sensitive Data in Logs** | Passwords, tokens, secrets filtered (redaction list) | `src/Logger.php` line 20 |
| **Debug Mode Disabled** | `APP_DEBUG` defaults to `false`, only `"true"` activates | `config/config.php` line 41 |

### 2.3 Documentation Already Present

✅ **STRIDE Worksheet**: `docs/stride_worksheet.md`
   - 6 STRIDE categories with application-specific threats
   - Data flows mapped to flows in DFD
   - Mitigations documented

✅ **Risk Register**: `docs/risk_register.md`
   - Threat ID, likelihood, impact, risk scores
   - Controls and residual risk
   - Top-three risk justification

✅ **Data-Flow Diagram**: `docs/dfd_diagram.png` (+ SVG source)
   - Processes, data stores, external entities
   - Trust boundaries marked

✅ **Before/After Code**: `legacy/` folder + `SECURITY_NOTES.md`
   - Vulnerable baselines for comparison
   - Non-executable (for report use only)

✅ **Incident Response Runbook**: `docs/incident_response_runbook.md`
   - Six stages: Preparation → Identification → Containment → Eradication → Recovery → Lessons Learned
   - Specific to student registration app

---

## PART 3: GAPS & COMPLETION TASKS

### 3.1 Critical Path to Completion

The following tasks must be completed to satisfy the assignment:

#### **Gap 1: Structured Evidence Organization**

**Current State:** Documentation exists but is scattered.

**Required:** Organize into the exact structure demanded by the assignment:

```
evidence/
├── task1/
│   ├── data-flow-diagram.png           [COPY from docs/ + ensure high quality]
│   ├── stride-worksheet.md             [COPY from docs/ + expand if needed]
│   ├── risk-register.md                [COPY from docs/]
│   ├── risk-register.csv               [CONVERT from markdown]
│   └── top-three-risks.md              [EXTRACT from risk register + justify]
├── task2/
│   ├── sql-before-after.md             [CREATE: vulnerable vs. secure code side-by-side]
│   ├── password-hash-evidence.png      [CREATE: screenshot of DB showing hashed passwords]
│   ├── authentication-controls.md      [CREATE: list all controls + config]
│   └── test-results.txt                [CREATE: automated test output]
└── task3/
    ├── xss-test-results.txt            [CREATE: defensive XSS test output]
    ├── csrf-test-results.txt           [CREATE: CSRF token validation test]
    ├── ssrf-test-results.txt           [CREATE: SSRF rejection test]
    ├── security-configuration.md       [CREATE: debug off, headers, dependencies]
    ├── security-events.log             [SAMPLE: redacted security log]
    ├── incident-record.md              [CREATE: fictional incident walkthrough]
    ├── incident-response-runbook.md    [COPY from docs/ but ensure readable]
    └── ethics-statement.md             [CREATE: signed declaration with matric]
```

**Action:** Create all missing evidence files (in progress below).

#### **Gap 2: Technical Report**

**Current State:** No formal report document exists.

**Required:** 8–12 page PDF technical report structured as:
- **TASK 1 section**: Threat model, STRIDE analysis, risk register, top-three risks, controls, residual risk
- **TASK 2 section**: SQL injection remediation, parameterized queries, auth controls, session security, test results
- **TASK 3 section**: XSS/CSRF/SSRF defences, security misconfiguration, logging, incident response, ethics
- **Appendices**: Screenshots, code excerpts, evidence references

**Action:** Generate comprehensive report (detailed in Part 4).

#### **Gap 3: Requirement Traceability Matrix**

**Current State:** No explicit mapping of requirements to implementation.

**Required:** `evidence/requirement-traceability-matrix.md` mapping all 30+ assignment requirements to:
- Implementation location (file/class)
- Test/evidence location
- Report section reference

**Action:** Create comprehensive traceability document.

#### **Gap 4: Automated Test Suite Completeness**

**Current State:** Basic tests exist in `tests/run_tests.php`.

**Required:** Comprehensive test suite covering:
- ✅ Valid login / invalid credentials
- ✅ Password hashing verification
- ✅ Rate limiting / lockout
- ✅ Session regeneration
- ✅ CSRF token validation (missing/invalid token rejection)
- ✅ XSS encoding verification
- ✅ SSRF rejection (loopback, private IPs, metadata)
- ✅ Authorization checks (student vs. admin)
- ✅ Input validation (email, file size, etc.)
- ✅ Security logging (no secrets in logs)

**Action:** Expand `tests/run_tests.php` with additional test cases and capture results.

#### **Gap 5: Environment & Dependency Documentation**

**Current State:** README mentions dependencies but lacks formal security review.

**Required:** `evidence/task3/security-configuration.md` detailing:
- PHP version, extensions, configuration
- MySQL version and secure defaults
- Dependencies and their versions
- Known vulnerabilities in dependencies (if any)
- Mitigation/justification

**Action:** Generate dependency documentation with security posture.

#### **Gap 6: Ethics Statement**

**Current State:** No ethics declaration exists.

**Required:** `evidence/task3/ethics-statement.md` confirming:
- Testing was authorized and conducted on localhost only
- No FUT Minna production systems were tested
- No public websites were scanned
- Only fictitious data was used
- Security testing was conducted responsibly

**Signature section left blank for submission.**

**Action:** Create ethics statement template.

### 3.2 Quality Assurance Checklist

**Before final submission, verify:**

- [ ] Application starts without errors
- [ ] Database connects and seeds correctly
- [ ] All test accounts work (student + admin)
- [ ] Security logs write correctly
- [ ] All 8 security classes load and function
- [ ] Automated tests execute successfully
- [ ] No PHP warnings/errors in logs
- [ ] No hardcoded secrets in config
- [ ] All required evidence files created
- [ ] Report is readable and comprehensive (8-12 pages)
- [ ] Requirement traceability matrix is complete
- [ ] README gives clear setup/test instructions
- [ ] Ethics statement signed (in submission version)

---

## PART 4: EXECUTION STRATEGY & TIMELINE

### 4.1 Immediate Actions (This Session)

1. ✅ Create `evidence/` directory structure
2. ✅ Copy Task 1 documentation (STRIDE, risk register, DFD)
3. ✅ Create `sql-before-after.md` for Task 2
4. ✅ Generate `password-hash-evidence.png` (screenshot)
5. ✅ Capture `authentication-controls.md`
6. ✅ Run and save test results
7. ✅ Create XSS/CSRF/SSRF test evidence
8. ✅ Generate security configuration document
9. ✅ Create ethics statement
10. ✅ Create incident record
11. ✅ Create requirement traceability matrix
12. ✅ Draft technical report

### 4.2 Manual Testing & Screenshot Capture

Follow `tests/README.md` section 2 for:
- Successful login attempt
- Failed login with lockout
- CSRF token rejection (missing/invalid)
- XSS input encoding
- SSRF rejection scenarios
- Authorization denial (student accessing admin)

### 4.3 Report Generation

- Write task-by-task sections
- Reference evidence and code
- Include screenshots and diagrams
- Maintain undergraduate academic language
- Ensure 8-12 page count (excluding appendices)

---

## PART 5: DELIVERABLES CHECKLIST

### Final Submission Must Include:

✅ **Source Code**
- Complete PHP/MySQL application in `2021-1-84189CF_IFT542/`
- Runnable from README instructions
- All security classes functional
- Database migrations + seed data
- No real secrets or API keys
- Localhost-only configuration

✅ **Evidence Folder**
- `evidence/task1/` — STRIDE, risk register, DFD
- `evidence/task2/` — SQL injection remediation, auth tests
- `evidence/task3/` — Web defences tests, logs, incident response, ethics

✅ **Documentation**
- README.md (setup, test accounts, commands)
- IMPLEMENTATION_PLAN.md (this file, updated)
- Technical report (8–12 pages, PDF)
- Ethics statement (signed by student)
- Requirement traceability matrix

✅ **Naming Convention**
- All files/folders use `2021-1-84189CF_IFT542` prefix
- Evidence clearly labeled and referenced

---

## PART 6: KNOWN LIMITATIONS & SCOPE BOUNDARIES

The following are **intentionally out of scope** and documented in the risk register as accepted risks:

- ❌ Multi-factor authentication (MFA) — documented as recommended future control
- ❌ Web Application Firewall (WAF) / network-level DDoS protection
- ❌ Automated dependency vulnerability scanning (manual review only)
- ❌ IP reputation or CAPTCHA layers on login (rate limiting + lockout used instead)
- ❌ Geographic IP restrictions
- ❌ Email verification (fictitious data only)

These do not affect the assignment grade; they are documented as residual risks.

---

## PART 7: SUCCESS CRITERIA

The assignment is **complete and ready for submission** when:

1. ✅ All 3 task sections are fully implemented and tested
2. ✅ Every assignment requirement (1–30+) has an implementation and evidence
3. ✅ The application is runnable from the README
4. ✅ All test suites pass with clear, reproducible results
5. ✅ The technical report is 8–12 pages and covers all three tasks
6. ✅ Evidence folder is complete, organized, and referenced from the report
7. ✅ Requirement traceability matrix accounts for all requirements
8. ✅ Ethics statement is signed and included
9. ✅ No real credentials, secrets, or reusable attack payloads are present
10. ✅ All files follow the naming convention `2021-1-84189CF_IFT542`

---

**Next Step:** Begin systematic completion of gaps identified in Part 3, starting with evidence organization and automated test expansion.

End of IMPLEMENTATION_PLAN.md
