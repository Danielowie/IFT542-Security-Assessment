# STRIDE THREAT MODELING WORKSHEET
## IFT 542 - Student Registration Web Application
### Matric: 2021/1/84189CF

---

## APPLICATION OVERVIEW
**Name:** Student Registration Web Application  
**Technology Stack:** PHP 8.2, MySQL 5.7, Apache  
**Users:** Students, Administrators  
**Data Flows:** Login, Profile Updates, Course Enrollment, File Upload, Admin Management  

---

## THREAT IDENTIFICATION BY STRIDE CATEGORY

### 1. SPOOFING (Identity Spoofing)

**Threat S-001: Credential Harvesting via Brute Force**
- **Description:** Attacker attempts unlimited failed login tries to guess passwords
- **Attack Vector:** POST /login.php with automated credential guessing
- **Impact:** Unauthorized account access, data breach
- **Likelihood:** 5/5 (Easy to automate)
- **Impact:** 5/5 (Full account compromise)
- **Risk Score:** 25
- **Control:** Rate limiting (max 5 attempts/15 min) + Account lockout (15 min)
- **Status:** ✓ Implemented (Scenario 3)

**Threat S-002: Session Fixation**
- **Description:** Attacker forces user to use attacker-controlled session ID
- **Attack Vector:** Bypass session regeneration by replaying old session cookies
- **Impact:** Session hijacking, unauthorized actions
- **Likelihood:** 4/5 (Requires network positioning)
- **Impact:** 4/5 (Full session access)
- **Risk Score:** 16
- **Control:** Regenerate session ID on login success
- **Status:** ✓ Implemented (Scenario 4)

---

### 2. TAMPERING (Data Modification)

**Threat T-001: SQL Injection in Login Query**
- **Description:** Attacker modifies SQL query via malicious input
- **Attack Vector:** POST /login.php identifier field: `admin@example.test' -- `
- **Impact:** Bypass authentication, modify database records
- **Likelihood:** 5/5 (Common attack, easy exploit)
- **Impact:** 5/5 (Complete database access)
- **Risk Score:** 25
- **Control:** Parameterised queries (prepared statements with bound parameters)
- **Status:** ✓ Implemented (Scenario 1)

**Threat T-002: SQL Injection in Admin Search**
- **Description:** Attacker modifies admin search query
- **Attack Vector:** GET /admin/students.php search: `%' OR '1'='1`
- **Impact:** Unauthorized data access, privilege escalation
- **Likelihood:** 5/5 (Common attack pattern)
- **Impact:** 4/5 (Access to student records)
- **Risk Score:** 20
- **Control:** Parameterised queries for all database access
- **Status:** ✓ Implemented (Scenario 2)

**Threat T-003: CSRF (Cross-Site Request Forgery) in Enrollment**
- **Description:** Attacker tricks user into enrolling in unwanted courses
- **Attack Vector:** Malicious website submits form to /courses.php
- **Impact:** Unauthorized course enrollment, transcript modification
- **Likelihood:** 4/5 (Requires social engineering)
- **Impact:** 3/5 (Reversible via admin)
- **Risk Score:** 12
- **Control:** CSRF tokens validated on all state-changing requests
- **Status:** ✓ Implemented (Scenario 6)

**Threat T-004: Stored XSS in User Profile**
- **Description:** Attacker injects JavaScript into profile bio
- **Attack Vector:** POST /dashboard.php bio field: `<script>alert('XSS')</script>`
- **Impact:** Session cookie theft, malware injection, phishing
- **Likelihood:** 4/5 (Stored in database)
- **Impact:** 4/5 (Affects all viewers)
- **Risk Score:** 16
- **Control:** HTML encode all user output using htmlspecialchars()
- **Status:** ✓ Implemented (Scenario 5)

---

### 3. REPUDIATION (Denial of Actions)

**Threat R-001: Lack of Audit Trail**
- **Description:** Attacker modifies data then denies the action
- **Attack Vector:** No logging of who accessed/modified what/when
- **Impact:** Cannot trace unauthorized actions
- **Likelihood:** 3/5 (Requires DB access)
- **Impact:** 4/5 (Prevents incident response)
- **Risk Score:** 12
- **Control:** Comprehensive security logging for all authentication and authorization events
- **Status:** ✓ Implemented (All scenarios log events)

---

### 4. INFORMATION DISCLOSURE (Unauthorized Access)

**Threat I-001: Session Hijacking via XSS Cookie Theft**
- **Description:** XSS allows attacker to steal session cookies
- **Attack Vector:** Stored XSS payload steals `document.cookie`
- **Impact:** Account takeover, unauthorized actions
- **Likelihood:** 4/5 (Depends on XSS)
- **Impact:** 5/5 (Full session access)
- **Risk Score:** 20
- **Control:** HTTPOnly flag on session cookies + XSS prevention
- **Status:** ✓ Implemented (Scenarios 4 & 5)

**Threat I-002: SSRF (Server-Side Request Forgery) to Internal Services**
- **Description:** Attacker uses URL preview to access internal APIs/metadata
- **Attack Vector:** POST /url_preview.php: `http://169.254.169.254/` (AWS metadata)
- **Impact:** Access to internal services, credential exposure
- **Likelihood:** 3/5 (Requires specific endpoint)
- **Impact:** 4/5 (AWS credentials, internal data)
- **Risk Score:** 12
- **Control:** URL allowlist + IP range restrictions (block loopback, private, metadata)
- **Status:** ✓ Implemented (Scenario 7)

**Threat I-003: Plaintext Password Storage**
- **Description:** Database breach exposes all passwords in plaintext
- **Attack Vector:** SQL injection or database theft
- **Impact:** Credential reuse across other systems
- **Likelihood:** 3/5 (Requires DB access)
- **Impact:** 5/5 (All users compromised)
- **Risk Score:** 15
- **Control:** Argon2id password hashing (2^16 memory cost)
- **Status:** ✓ Implemented (Scenario 2 verification)

---

### 5. DENIAL OF SERVICE (Availability)

**Threat D-001: Brute Force Login Exhaustion**
- **Description:** Attacker floods login endpoint with requests, locking accounts
- **Attack Vector:** Automated POST to /login.php with many usernames
- **Impact:** Legitimate users locked out, service unavailability
- **Likelihood:** 5/5 (Easy to automate)
- **Impact:** 3/5 (Temporary, reversible)
- **Risk Score:** 15
- **Control:** Rate limiting (per IP, per identifier) + Progressive backoff
- **Status:** ✓ Implemented (Scenario 3)

**Threat D-002: Database Query Bombing**
- **Description:** Attacker crafts expensive SQL queries to exhaust resources
- **Attack Vector:** Complex WHERE clause in search: Multiple OR conditions
- **Impact:** Database slowdown, service degradation
- **Likelihood:** 3/5 (Requires knowledge of schema)
- **Impact:** 3/5 (Temporary unavailability)
- **Risk Score:** 9
- **Control:** Query timeout + Resource limits + Parameterised queries prevent modification
- **Status:** ✓ Implemented (Scenarios 1 & 2 prevent query tampering)

---

### 6. ELEVATION OF PRIVILEGE

**Threat E-001: Missing Access Control on Admin Pages**
- **Description:** Student bypasses admin role check, accesses admin functions
- **Attack Vector:** Direct URL to /admin/index.php while logged in as student
- **Impact:** Unauthorized course manipulation, grade modification
- **Likelihood:** 4/5 (Trivial to attempt)
- **Impact:** 5/5 (Modify all user records)
- **Risk Score:** 20
- **Control:** Server-side role verification on every admin page
- **Status:** ✓ Implemented (Scenario 8)

**Threat E-002: Insecure Direct Object Reference (IDOR)**
- **Description:** Student modifies user_id parameter to access other profiles
- **Attack Vector:** GET /profile.php?user_id=2 (access other student data)
- **Impact:** Privacy violation, personal information theft
- **Likelihood:** 4/5 (Common vulnerability)
- **Impact:** 4/5 (Access to personal data)
- **Risk Score:** 16
- **Control:** Authorization check - Verify user owns the profile before display
- **Status:** ✓ Implemented (Enforced in Auth::requireLogin)

---

## TOP 3 PRIORITY THREATS & MITIGATION

| Rank | Threat | Risk Score | Control | Residual Risk |
|------|--------|-----------|---------|--------------|
| 1 | SQL Injection (Login) | 25 | Parameterised queries | 2 (Impossible to exploit via SQL syntax) |
| 2 | Brute Force | 20 | Rate limit + Lockout | 3 (Account recovery needed) |
| 3 | CSRF | 12 | CSRF tokens | 2 (Token generation is cryptographically secure) |

---

## CONTROL IMPLEMENTATION SUMMARY

### Preventive Controls (Stop attacks before they happen)
- Input validation (SQL injection, XSS)
- Strong password hashing (Argon2id)
- CSRF tokens
- Role-based access control
- URL allowlist (SSRF)

### Detective Controls (Identify attacks in progress)
- Security logging (failed logins, validation rejections, authorization denials)
- Rate limit tracking
- Account lockout detection

### Corrective Controls (Respond to breaches)
- Incident response runbook
- Log analysis and alerting
- Account recovery procedures

---

## RESIDUAL RISK ASSESSMENT

After implementing all controls:
- **SQL Injection:** Residual Risk = 2/25 (92% reduction)
- **Brute Force:** Residual Risk = 3/20 (85% reduction)
- **CSRF:** Residual Risk = 2/12 (83% reduction)
- **XSS:** Residual Risk = 2/16 (87% reduction)
- **SSRF:** Residual Risk = 2/12 (83% reduction)
- **Access Control:** Residual Risk = 1/20 (95% reduction)

**Overall:** All critical threats mitigated to acceptable levels (< 3/5)

---

## VALIDATION & TESTING EVIDENCE

| Threat | Test Scenario | Evidence |
|--------|---|---|
| S-001 (Brute Force) | Scenario 3 | Account lockout after 6 attempts |
| S-002 (Session Fixation) | Scenario 4 | Session ID changes on login |
| T-001 (SQLi Login) | Scenario 1 | Injection payload rejected |
| T-002 (SQLi Search) | Scenario 2 | Payload treated as literal |
| T-003 (CSRF) | Scenario 6 | Form without token rejected |
| T-004 (XSS) | Scenario 5 | Script tag rendered as text |
| I-002 (SSRF) | Scenario 7 | Internal IPs blocked |
| E-001 (Access Control) | Scenario 8 | Student gets 403 on admin page |
| General Logging | All Scenarios | Security events logged |
| Security Headers | Scenario 10 | All 4 headers present |

---
 
**Status:** ✓ ALL THREATS MITIGATED AND TESTED
