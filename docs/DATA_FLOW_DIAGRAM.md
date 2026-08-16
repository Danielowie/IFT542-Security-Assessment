# DATA FLOW DIAGRAM (DFD)
## Student Registration Web Application
### Matric: 2021/1/84189CF

---

## OVERVIEW

This Data Flow Diagram illustrates how data flows through the Student Registration application, including all trust boundaries, external entities, processes, data stores, and data flows.

---

## DIAGRAM REPRESENTATION

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           STUDENT REGISTRATION APPLICATION              │
│                              Trust Boundary                             │
│                                                                          │
│  ┌──────────────────────┐         ┌──────────────────────┐             │
│  │  Users / Students    │         │   Administrators     │             │
│  │                      │         │                      │             │
│  │ - Student1           │         │ - Admin              │             │
│  │ - Student2           │         │                      │             │
│  └──────────┬───────────┘         └──────────┬───────────┘             │
│             │                                 │                         │
│    1.1 (Login)│                     1.2 (Admin Login)                   │
│             │                                 │                         │
│             └─────────────────┬───────────────┘                         │
│                               │                                         │
│                       ┌───────▼────────┐                               │
│                       │   P1: AUTH     │                               │
│                       │   Module       │                               │
│                       │   [Hashing,    │                               │
│                       │    Rate Limit, │                               │
│                       │    Session]    │                               │
│                       └───────┬────────┘                               │
│                               │                                         │
│              2.1 Query/Update │                                         │
│              Auth Events      │                                         │
│                               ▼                                         │
│                       ┌──────────────────────┐                         │
│                       │  D1: Users DB       │                         │
│                       │  ┌─────────────────┤                         │
│                       │  │ id, email,      │                         │
│                       │  │ password_hash,  │                         │
│                       │  │ role, is_active │                         │
│                       │  └─────────────────┤                         │
│                       └──────────────────────┘                         │
│                               ▲                                         │
│                               │                                         │
│                       ┌───────┴────────┐                               │
│                       │   P2: QUERY    │                               │
│                       │   Executor     │                               │
│                       │   [Parameterised]                              │
│                       └───────┬────────┘                               │
│                               ▲                                         │
│                               │                                         │
│          3.1 Select/Insert    │                                         │
│          (Parameterised)       │                                         │
│                               │                                         │
│             ┌─────────────────┴──────────────┐                         │
│             │                                │                         │
│    4.1 (Courses)│                  4.2 (Profiles)                      │
│             │                                │                         │
│        ┌────▼────┐                    ┌─────▼──────┐                  │
│        │  P3:    │                    │  P4:       │                  │
│        │ Enroll  │                    │ Profile    │                  │
│        │ Course  │                    │ Update     │                  │
│        └────┬────┘                    └─────┬──────┘                  │
│             │                                │                         │
│             │  5.1 Insert Enrollment        │  5.2 Update Profile    │
│             │  (CSRF Token)                 │  (Output Encode)        │
│             │                                │                         │
│             └─────────────────┬──────────────┘                         │
│                               │                                         │
│                       ┌───────▼────────┐                               │
│                       │  D2: Courses   │                               │
│                       │  D3: Enrollment│                               │
│                       │  D4: Profiles  │                               │
│                       │                │                               │
│                       │ With CSRF      │                               │
│                       │ Tokens &       │                               │
│                       │ Encoding       │                               │
│                       └───────┬────────┘                               │
│                               │                                         │
│                    6.1 Return Results │                                │
│                    (HTML Encoded)     │                                │
│                               │                                         │
│             ┌─────────────────▼──────────────┐                         │
│             │  P5: RESPONSE                  │                         │
│             │  Handler                       │                         │
│             │  [Security Headers, XSS        │                         │
│             │   Prevention, Logging]         │                         │
│             └─────────────────┬──────────────┘                         │
│                               │                                         │
│                       7.1 HTTP Response                                │
│                       (with Headers)                                   │
│                               │                                         │
│             ┌─────────────────▼──────────────┐                         │
│             │  D5: Security Log              │                         │
│             │  ┌──────────────────────────┤  │                         │
│             │  │ event_type               │  │                         │
│             │  │ user_id                  │  │                         │
│             │  │ ip_address               │  │                         │
│             │  │ created_at               │  │                         │
│             │  │ (Immutable)              │  │                         │
│             │  └──────────────────────────┤  │                         │
│             └────────────────────────────────┘                         │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
                               │
                    7.2 HTTP Response
                  (to User's Browser)
                               │
                               ▼
                        ┌─────────────┐
                        │  Browser    │
                        │  (User)     │
                        └─────────────┘
```

---

## DATA FLOW DETAILS

### FLOW 1: AUTHENTICATION

**Source:** User (Login Form)  
**Process:** P1 - Authentication Module  
**Destination:** D1 - Users Database

**Data Elements:**
- Email / Identifier (input)
- Password (input)
- Rate limit counter (internal)
- Password hash (from DB)
- Account lockout status (from DB)
- Session ID (output)

**Security Controls:**
✓ Input validation (Validator.php)
✓ Parameterised queries
✓ Argon2id password comparison
✓ Rate limiting (max 5/15 min)
✓ Account lockout (15 min)
✓ Session regeneration
✓ Logged: login_success / login_failed

---

### FLOW 2: ADMIN SEARCH

**Source:** Admin (Search Form)  
**Process:** P2 - Query Executor  
**Destination:** D1 - Users Database

**Data Elements:**
- Search term (input)
- Student records (output)

**Security Controls:**
✓ Authorization check (must be admin)
✓ Parameterised query (SQL injection prevention)
✓ Output HTML-encoded
✓ Logged: admin_search event

---

### FLOW 3: COURSE ENROLLMENT

**Source:** Student (Enrollment Form)  
**Process:** P3 - Enrollment Processor  
**Destination:** D2 - Courses, D3 - Enrollments

**Data Elements:**
- Course ID (input)
- CSRF token (validation)
- Student ID (from session)
- Enrollment record (output)

**Security Controls:**
✓ CSRF token validation
✓ Authorization check (must be student)
✓ Parameterised query
✓ Logged: enrollment event

---

### FLOW 4: PROFILE UPDATE

**Source:** Student (Profile Form)  
**Process:** P4 - Profile Manager  
**Destination:** D4 - Profiles

**Data Elements:**
- Bio text (input)
- Full name (input)
- CSRF token (validation)
- Updated profile (output)

**Security Controls:**
✓ CSRF token validation
✓ Input validation (length, characters)
✓ Output HTML-encoded on display
✓ XSS prevention (stored safely)
✓ Logged: profile_update event

---

### FLOW 5: LOGGING & AUDIT

**Source:** All Processes  
**Process:** Central Logger (in all modules)  
**Destination:** D5 - Security Log (file + database)

**Logged Events:**
- `validation_rejected` - Input validation failed
- `login_success` - Successful authentication
- `login_failed` - Failed authentication
- `account_locked` - Brute force lockout
- `csrf_rejected` - CSRF token invalid
- `ssrf_blocked` - SSRF attempt blocked
- `authorization_denied` - Access control violation
- `upload_rejected` - File upload validation failed

**Data Stored:**
- Event type
- User email / ID
- IP address
- Timestamp
- Details (JSON)

---

## TRUST BOUNDARIES

### 1. EXTERNAL BOUNDARY (Users ↔ Application)

**Untrusted Input:**
- Email/identifier from login form
- Password
- Search queries
- Profile data (bio, name)
- Course selection
- File uploads
- CSRF tokens

**Trust Loss Points:**
- HTTP request can be intercepted (HTTPS in production)
- User input can be malicious
- Session cookies can be stolen

**Controls:**
✓ HTTPS (in production)
✓ HTTPOnly cookies
✓ Input validation
✓ Output encoding
✓ CSRF tokens

---

### 2. APPLICATION ↔ DATABASE BOUNDARY

**Untrusted Data (from users):**
- Direct user input via forms
- HTTP parameters
- File contents

**Trust Loss Points:**
- SQL injection if queries are constructed via concatenation
- Database credentials in .env file
- Backup files could be stolen

**Controls:**
✓ Parameterised queries (bind variables)
✓ Database authentication
✓ Principle of least privilege (app_user)
✓ Encrypted credentials
✓ Database backups encrypted

---

### 3. APPLICATION ↔ LOGGER BOUNDARY

**Data to Log:**
- Security events
- IP addresses
- Timestamps
- User identifiers

**What NOT to Log:**
✗ Passwords
✗ Full credit card numbers
✗ API keys
✗ Session cookies
✗ PII (unless required)

**Controls:**
✓ Passwords never logged
✓ Sensitive data filtered
✓ Log immutability
✓ Log access control

---

## DATA STORES

### D1: Users Database
**Tables:**
- `users` - User accounts and roles
- `account_lockouts` - Brute force tracking
- `login_attempts` - Failed login history
- `password_reset_tokens` - Reset links

**Access Control:**
- SQL user: app_user (limited privileges)
- Operations: SELECT, INSERT, UPDATE only
- No DROP/ALTER/TRUNCATE

**Encryption:**
- Password hashes: Argon2id
- Reset tokens: Random (one-time use)

---

### D2-D4: Application Data
**Tables:**
- `courses` - Course information
- `enrollments` - Student enrollments
- `user_profiles` - Student profiles (bio, etc.)

**Protection:**
- Input validated before insert
- Output HTML-encoded on display
- Parameterised queries for all access

---

### D5: Security Log
**Storage:** File (`storage/logs/security.log`) + Database

**Immutability:**
- Write-once (prevents tampering)
- Append-only
- Archival for compliance

**Retention:**
- Keep for 1 year minimum
- Archive offline for longer periods

---

## LEVEL 1 DFD CONTEXT DIAGRAM

```
┌──────────────────────────────────────────────────────────┐
│                                                            │
│         ┌─────────────────────────────────────┐           │
│         │                                     │           │
│    ┌────▼────┐                        ┌──────▼─────┐     │
│    │ Students│◄────────────────────────│ Registration│     │
│    │ Admins  │    Authenticated       │ Application │     │
│    │         │    Session + Response  │             │     │
│    └────┬────┘                        └──────┬──────┘     │
│         │                                    │             │
│    HTTP │Request                    HTTP    │ Response    │
│    (SSL)│                            (SSL)  │             │
│         │                                    │             │
│         │  7. HTTP Response                │             │
│         │     (HTML + Headers)             │             │
│         │                                    │             │
│         │                    ┌───────────────┘             │
│         │                    │                             │
│         └────────────────────┘                             │
│                                                            │
└──────────────────────────────────────────────────────────┘
```

---

## SECURITY ANNOTATIONS ON DATAFLOWS

| Flow | From | To | Security Control |
|------|------|----|----|
| 1.1 | User Input | P1 Auth | Input validation, Rate limiting |
| 2.1 | P1 Auth | D1 DB | Parameterised queries |
| 3.1 | P2 Query | D1 DB | Parameterised queries, Auth check |
| 4.1 | User | P3 Enroll | CSRF token, Auth check |
| 4.2 | User | P4 Profile | CSRF token, Auth check |
| 5.1, 5.2 | P3, P4 | D2-D4 DB | Parameterised queries |
| 6.1 | D1-D5 | P5 Response | Output encoding, Logging |
| 7.1 | P5 | User | Security headers, CSP |

---

## THREAT MAPPING TO DATA FLOWS

| Data Flow | Threat | Control |
|-----------|--------|---------|
| 1.1 (Login Input) | SQL Injection | Parameterised + Validation |
| 1.1 (Password) | Brute Force | Rate limiting + Lockout |
| 1.1 (Session) | Session Fixation | Regenerate ID |
| 4.1, 4.2 (Forms) | CSRF | CSRF tokens |
| 6.1 (Output) | XSS | HTML encoding |
| All (Network) | Eavesdropping | HTTPS (production) |
| All (Logging) | Information Disclosure | Immutable logs + ACL |

---

## NOTES

- All data at rest encrypted with Argon2id (passwords)
- All data in transit uses HTTPS (in production)
- All queries parameterised (no dynamic SQL)
- All logs immutable and auditable
- Session data volatile (cleared on logout)
- Trust boundaries strictly enforced

---

**DFD Level:** Level 1 (Context + Processes)  
**Notation:** Yourdon/DeMarco  
**Last Updated:** August 15, 2026
