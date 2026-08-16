# Incident Record: Simulated Brute Force Attack

**Student:** 2021-1-84189CF | **Date:** 2026-08-11 | **Time:** 14:32 UTC

---

## INCIDENT SUMMARY

| Field | Value |
|-------|-------|
| **Incident ID** | INC-2026-08-11-001 |
| **Severity** | HIGH |
| **Status** | RESOLVED |
| **Duration** | 8 minutes (14:32–14:40 UTC) |
| **Type** | Brute Force Attack (Simulated) |
| **Affected System** | Student Registration Web Application |
| **Affected Users** | 1 student account (student1@example.test) |
| **Data Compromised** | None (blocked by rate limiting) |

---

## 1. DETECTION

**Time Detected:** 2026-08-11 14:33 UTC  
**Detection Method:** Automated security event logging

### Detection Evidence

**Log Entry 1 (14:32:05):**
```json
{
  "ts": "2026-08-11T14:32:05Z",
  "event": "login_failed",
  "subject": "student1@example.test",
  "ip": "203.0.113.42",
  "detail": {}
}
```

**Log Entry 2 (14:32:15):**
```json
{
  "ts": "2026-08-11T14:32:15Z",
  "event": "login_failed",
  "subject": "student1@example.test",
  "ip": "203.0.113.42",
  "detail": {}
}
```

**Log Entry 3–5:** (similar, omitted for brevity)

**Log Entry 6 (14:32:45):**
```json
{
  "ts": "2026-08-11T14:32:45Z",
  "event": "account_locked",
  "subject": "student1@example.test",
  "ip": "203.0.113.42",
  "detail": {"minutes": 15}
}
```

### Database Evidence

**Query Result: `login_attempts` table**
```
identifier                | ip_address      | success | attempted_at
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:32:05
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:32:15
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:32:25
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:32:35
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:32:45
student1@example.test     | 203.0.113.42    | 0       | 2026-08-11 14:33:05
```

**Query Result: `account_lockouts` table**
```
user_id | locked_until            | reason
1       | 2026-08-11 14:47:45     | too_many_failed_attempts
```

---

## 2. INVESTIGATION

### Phase 1: Scope Assessment (14:33–14:35)

**Questions Asked:**
1. Is the targeted account legitimate? ✅ Yes (student1@example.test, created 2020)
2. Are other accounts being targeted? ❌ No (only student1@example.test)
3. Is the attacker IP known? ❌ No (203.0.113.42 = public/random)
4. Were any successful logins made? ❌ No (all 6 attempts failed)

**Findings:**
- Single account targeted (not random credential stuffing)
- Brute force attempt (6 password guesses in 40 seconds)
- Rate limiting automatically activated
- No unauthorized access gained

### Phase 2: Root Cause Analysis (14:35–14:37)

**Possible Causes:**
1. Attacker attempting password reuse (leaked password from other site)
2. Competitor trying to sabotage student system
3. Malicious student testing security controls
4. Automated bot scanning for vulnerable endpoints

**Most Likely:** Credential reuse from unrelated data breach (external origin)

### Phase 3: Verification (14:37–14:40)

**Verification Performed:**

1. **Verify Rate Limiting Activated**
   ```sql
   SELECT COUNT(*) as failed_attempts FROM login_attempts
   WHERE identifier = 'student1@example.test' 
     AND success = 0 
     AND attempted_at > NOW() - INTERVAL 15 MINUTE;
   ```
   Result: 6 attempts ✅

2. **Verify Account Locked**
   ```sql
   SELECT locked_until FROM account_lockouts
   WHERE user_id = 1;
   ```
   Result: 2026-08-11 14:47:45 (15 min lock) ✅

3. **Verify No Database Tampering**
   ```sql
   SELECT password_hash FROM users WHERE id = 1;
   ```
   Result: Hash unchanged, still Argon2id format ✅

4. **Verify No Privilege Escalation**
   ```sql
   SELECT role FROM users WHERE id = 1;
   ```
   Result: role = 'student' (unchanged) ✅

5. **Verify Session Not Compromised**
   - No active sessions for student1@example.test
   - No unauthorized admin logins

---

## 3. CONTAINMENT ACTIONS

**Time:** 2026-08-11 14:33:20 UTC

### Automatic Response

✅ Rate limiting threshold triggered (5 failed attempts)  
✅ Account automatically locked for 15 minutes  
✅ Incident logged to security_events table  
✅ Incident logged to security.log file  

### Manual Response

**Action 1: Notify Student (14:35)**

```
To: student1@example.test
From: security@futminna.test
Subject: Account Security Notice

Hello,

Your Student Registration account (2021-1-84189CF) was targeted by 6 failed login attempts in the past few minutes. Your account has been temporarily locked as a security measure.

WHAT HAPPENED:
- Attacker IP: 203.0.113.42
- Attempts: 6 (in 40 seconds)
- Time: 2026-08-11 14:32–14:33 UTC
- Result: Blocked by automatic rate limiting

YOUR ACCOUNT IS SAFE:
- The attacker never gained access (all passwords incorrect)
- Your password remains secure
- Automatic account lockout prevented brute force success
- Your account will auto-unlock in 15 minutes

RECOMMENDED ACTIONS:
1. If this was not you, reset your password: [link]
2. If your password is used elsewhere, update those accounts
3. Enable multi-factor authentication (coming soon)

Questions? Contact: security@futminna.test

The Security Team
```

**Action 2: Monitor for Escalation (14:35–14:50)**

```
Monitor: Does attacker try other accounts?
Monitor: Does attacker try other IP addresses?
Monitor: Are there SQL injection attempts mixed in?
Monitor: Are there XSS attempts in form fields?

Interval: Check every 5 minutes
Duration: 15 minutes (while lockout active)

Result: No escalation detected ✅
```

---

## 4. ERADICATION

**Time:** 2026-08-11 14:40 UTC

### Verification of Security Controls

**Check 1: Rate Limiting Code**
```php
private const MAX_ATTEMPTS_PER_WINDOW = 5;
private const WINDOW_MINUTES = 15;
private const LOCKOUT_MINUTES = 15;
```
✅ Parameters verified, unchanged, appropriate

**Check 2: Parameterized Queries**
```php
Database::run(
    'SELECT id FROM users WHERE email = ? OR matric_no = ? LIMIT 1',
    [$identifier, $identifier]
);
```
✅ Query parameterized, no injection possible

**Check 3: Password Hashing**
```sql
SELECT password_hash FROM users WHERE email = 'student1@example.test';
```
Result: `$argon2id$v=19$m=65536,t=4,p=1$...`
✅ Argon2id hash confirmed, not plaintext

### Root Cause Analysis Summary

**No vulnerability found.** System functioned as designed:
1. Rate limiting detected repeated failures
2. Automatic lockout prevented brute force success
3. Incident was logged for audit trail
4. Student was notified
5. Investigation found no data breach

**Attacker's tools/methods:** Generic password guessing (e.g., `password123`, `Admin@123`)  
**Attacker's success rate:** 0% (all passwords incorrect)

---

## 5. RECOVERY

**Time:** 2026-08-11 14:47:45 UTC

### Account Restoration

The student account was automatically unlocked at 14:47:45 UTC (15 minutes after lockout).

**Verification:**
```sql
SELECT * FROM account_lockouts WHERE user_id = 1;
```
Result: No records (lock expired and removed) ✅

**Student can log in:** ✅ Confirmed at 14:48 UTC

### System Health Check

- ✅ Application responding normally
- ✅ Database connections stable
- ✅ Security logging operational
- ✅ Rate limiting ready for next incident
- ✅ All security headers present
- ✅ No other students affected

---

## 6. LESSONS LEARNED

### What Went Well

1. ✅ **Automatic Detection:** Incident detected within 2 minutes (14:32 attack, 14:33 detection)
2. ✅ **Autonomous Response:** Rate limiting activated without manual intervention
3. ✅ **Clear Logging:** Security events logged with timestamp, IP, subject
4. ✅ **No Data Compromise:** Attackers never got past authentication
5. ✅ **Audit Trail:** Complete incident history preserved

### What Could Improve

| Improvement | Priority | Effort | Benefit |
|-------------|----------|--------|---------|
| Email notification when account locked | HIGH | Low | Faster user awareness |
| CAPTCHA after 3 failed attempts | MEDIUM | Medium | Reduce bot attacks |
| Geographic IP analysis (detect unusual login location) | MEDIUM | High | Detect account takeover |
| Rate limiting by username + geolocation | LOW | High | Balance security/usability |
| Incident dashboard with real-time alerts | MEDIUM | High | Better monitoring |

### Recommended Changes

1. **Add email alert for locked accounts** (1 hour of effort)
   - Send to user: "Your account was locked due to [N] failed login attempts"
   - Send to admin: "Student account locked: student1@example.test from 203.0.113.42"

2. **Review rate limiting thresholds** (15 min)
   - Current: 5 attempts per 15 min
   - Consider: 3 attempts per 15 min (more aggressive)

3. **Add CAPTCHA after 2 failures** (4 hours of effort)
   - Reduces automated bot attacks
   - Balances security with usability

### Training Opportunity

- Security team trained: ✅ Manual response verified
- Student educated: ✅ Notified of incident, rate limiting explained
- Documentation updated: ✅ This incident record + runbook

---

## TIMELINE SUMMARY

| Time | Event |
|------|-------|
| 14:32:05 | Attempt 1: Failed login |
| 14:32:15 | Attempt 2: Failed login |
| 14:32:25 | Attempt 3: Failed login |
| 14:32:35 | Attempt 4: Failed login |
| 14:32:45 | Attempt 5: Failed login |
| 14:33:05 | Attempt 6: Failed login → **ACCOUNT LOCKED** |
| 14:33:20 | Incident detected in logs |
| 14:35:00 | Student notified via email |
| 14:37:00 | Investigation completed |
| 14:40:00 | Verification confirmed: no breach |
| 14:47:45 | Account auto-unlocked (15 min expiry) |
| 14:48:00 | Student can log in again |

---

## CONCLUSION

This simulated incident demonstrates that the IFT542 Student Registration system's security controls are working as designed:

✅ **Rate limiting** automatically blocked brute force attack  
✅ **Account lockout** prevented unauthorized access  
✅ **Security logging** captured all details for investigation  
✅ **Automated response** required no manual intervention  
✅ **No data compromise** achieved by attacker  

The system is **resilient** to brute force attacks and provides an **audit trail** for forensic analysis.

