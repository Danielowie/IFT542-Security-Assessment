# Incident Response Runbook: Student Registration Web Application

**Student:** 2021-1-84189CF | **Application:** IFT542 Student Registration System

---

## 1. PREPARATION

### Incident Response Team
- **Security Administrator:** Reviews logs, initiates response
- **Database Administrator:** Manages backups, transaction rollback
- **System Administrator:** Controls server access, firewall rules

### Monitoring & Alerting
- ✅ Security event logging enabled (`storage/logs/security.log`)
- ✅ Database audit trail active (`security_events` table)
- ✅ Failed login alerts: >5 attempts in 15 minutes
- ✅ Rate limiting blocks active (automatic)
- ✅ Session timeout: 30 minutes inactivity

### Incident Severity Levels
- **CRITICAL:** Active data breach, authentication bypass, database compromise
- **HIGH:** Failed login DoS, unauthorized admin access, data tampering detected
- **MEDIUM:** Suspicious pattern in logs, single failed authorization, validation anomaly
- **LOW:** Minor validation errors, expected rate limiting

### Contact Information
- **Security:** security@futminna.test
- **IT:** support@futminna.test
- **Management:** admin@futminna.test

---

## 2. IDENTIFICATION

### Indicators of Compromise (IOCs)

**SQL Injection Attempts:**
- Check: Unusual characters in login attempts (`' --`, `' OR '1'='1`, etc.)
- Log file: `storage/logs/security.log` (event_type: "validation_rejected")
- Database: `SELECT * FROM security_events WHERE event_type = 'validation_rejected' AND created_at > NOW() - INTERVAL 1 HOUR`
- Action: Alert if >10 attempts in 5 minutes

**Brute Force Attack:**
- Check: Multiple failed logins from same IP or targeting same account
- Log file: Multiple "login_failed" events
- Database: `SELECT subject, ip_address, COUNT(*) as n FROM security_events WHERE event_type = 'login_failed' AND created_at > NOW() - INTERVAL 30 MINUTE GROUP BY subject, ip_address HAVING n > 5`
- Action: Rate limiting auto-activates; alert if bypassed

**Account Takeover:**
- Check: Successful login from unusual IP or after lockout
- Log file: "login_failed" followed shortly by "login_success"
- Action: Force password reset, review session logs

**Unauthorized Admin Access:**
- Check: Student account accessing admin functions
- Log file: "authorization_denied" events targeting admin URLs
- Database: `SELECT * FROM security_events WHERE event_type = 'authorization_denied' AND created_at > NOW() - INTERVAL 1 HOUR`
- Action: Verify user account, check browser session

**XSS Exploitation:**
- Check: Unusual bio content (HTML/JavaScript tags)
- Log file: May not be logged (browser-side), check database directly
- Action: Query bio field for script tags, sanitize if found

**File Upload Malware:**
- Check: Executable files uploaded (.php, .exe, .sh, etc.)
- Log file: Rejected uploads with reason
- Action: Verify upload validation in `public/upload.php`

---

## 3. CONTAINMENT

### Immediate Actions (First 5 Minutes)

**If Active Attack Detected:**

1. **Rate Limiting Auto-Activation**
   - System automatically activates after 5 failed attempts per 15 min
   - No manual action needed (autonomous protection)
   - Review logs to confirm: `SELECT * FROM account_lockouts WHERE locked_until > NOW()`

2. **Lock Suspected Accounts**
   ```sql
   UPDATE users SET is_active = 0 WHERE id IN (suspected_user_ids);
   ```
   - Marks accounts inactive (blocks login)
   - Logs are retained (not deleted)
   - User notified via email (if configured)

3. **Isolate Compromised Session (If Confirmed)**
   ```php
   Database::run(
       'DELETE FROM login_attempts WHERE ip_address = ? AND created_at > NOW() - INTERVAL 1 DAY',
       [attacker_ip]
   );
   Session::destroy();  // Force re-login
   ```
   - Invalidates active sessions from attacker IP
   - Clears login attempt history for that attacker

4. **Firewall Blocking (Network Level)**
   - Contact System Admin: Block attacker IP at firewall
   - Temporary block: 24 hours
   - Log: Document IP and block reason

### Notify Stakeholders (Within 30 Minutes)

```
Subject: SECURITY ALERT - Student Registration System

Dr. Bashir,

A potential security incident has been detected in the IFT542 Student Registration application.

SUMMARY:
- Time Detected: [timestamp]
- Type: [SQL Injection/Brute Force/Unauthorized Access/etc.]
- Affected Users: [count] accounts
- Status: Contained (automated rate limiting activated)

ACTIONS TAKEN:
- Locked affected accounts
- Activated rate limiting
- Reviewing audit logs

NEXT STEPS:
- Full investigation in progress
- Update in 1 hour

Contact: security@futminna.test
```

---

## 4. ERADICATION

### Investigation (1–4 Hours)

**Step 1: Gather Evidence**

```bash
# Extract all security events from past 24 hours
mysql -u root student_registration << EOF
SELECT * FROM security_events 
WHERE created_at > NOW() - INTERVAL 1 DAY 
ORDER BY created_at DESC;
EOF

# Extract login attempts
mysql -u root student_registration << EOF
SELECT identifier, ip_address, COUNT(*) as attempts, MAX(attempted_at) as last_attempt
FROM login_attempts 
WHERE attempted_at > NOW() - INTERVAL 1 DAY
GROUP BY identifier, ip_address
ORDER BY attempts DESC;
EOF

# Check for modified user records
mysql -u root student_registration << EOF
SELECT * FROM users WHERE role != (SELECT role FROM users WHERE id = 1 LIMIT 1);
EOF
```

**Step 2: Determine Root Cause**

| Finding | Cause | Eradication |
|---------|-------|------------|
| **Multiple failed logins from one IP** | Brute force attack | IP blocked; rate limiting confirmed working |
| **SQL injection patterns in logs** | Attempted SQL injection | Verify parameterized queries still in place; no bypass found |
| **Unusual admin login** | Compromised admin account | Force password reset; check session logs |
| **Script tags in bio field** | XSS exploitation | Sanitize field; verify output encoding active |

**Step 3: Verify Integrity**

```sql
-- Verify no unauthorized privilege escalation
SELECT id, email, role FROM users WHERE role = 'admin';

-- Verify all passwords are hashed (not plaintext)
SELECT COUNT(*) as unhashed FROM users WHERE password_hash NOT LIKE '$argon2id%';
-- Expected result: 0

-- Verify no backdoor accounts created
SELECT COUNT(*) as recent_users FROM users WHERE created_at > NOW() - INTERVAL 1 DAY;
```

### Remediation

**If SQL Injection Vulnerability Found:**
- Code review of `src/Database.php` and `Database::run()` calls
- Verify `PDO::ATTR_EMULATE_PREPARES = false` still set
- Re-deploy from source control (no manual modifications)

**If XSS Exploitation Found:**
- Sanitize affected field: `UPDATE users SET bio = NULL WHERE bio LIKE '%<script%'`
- Verify `e()` function applied to all bio output in views
- Force CSP reload in browser cache

**If Brute Force / Rate Limiting Bypass:**
- Verify `login_attempts` table has recent entries
- Check `account_lockouts` table for active locks
- Review rate limiting logic in `src/Auth.php`

**If Account Compromise:**
- Force password reset: `DELETE FROM password_resets WHERE user_id = ?`
- Generate new password reset token: `INSERT INTO password_resets (...)`
- Send reset link to registered email

---

## 5. RECOVERY

### Restore From Backup (If Data Corruption)

```bash
# List backups
ls -la /backup/student_registration_*.sql

# Restore from last known-good backup (before incident)
mysql -u root < /backup/student_registration_2026-08-11_1900.sql
```

**Do NOT restore if:**
- Attacker still has access (will re-compromise)
- Malware persists in code
- Containment not yet complete

### Re-Enable Affected Services

1. **Unlock User Accounts (After Investigation)**
   ```sql
   UPDATE users SET is_active = 1 WHERE id IN (affected_user_ids);
   DELETE FROM account_lockouts WHERE user_id IN (affected_user_ids);
   ```

2. **Clear Rate Limiting for Clean IPs**
   ```sql
   DELETE FROM login_attempts WHERE ip_address NOT IN (known_attacker_ips);
   ```

3. **Notify Users**
   ```
   Subject: Account Unlocked - Student Registration System
   
   Your account has been temporarily locked due to a security incident.
   The issue has been resolved and your account is now active.
   
   If you did not attempt to log in, please reset your password:
   [password reset link]
   
   Contact: support@futminna.test
   ```

### Verify System Integrity

- ✅ Application starts without errors
- ✅ Database connections successful
- ✅ All test accounts can log in
- ✅ Security logs writing normally
- ✅ Admin functions accessible (authenticated only)
- ✅ Security headers present in HTTP responses

---

## 6. LESSONS LEARNED

### Post-Incident Review (Within 1 Week)

**Questions to Answer:**
1. How was the attack detected? Was detection timely?
2. Was the automated response effective?
3. Could the attack have been prevented?
4. Were there gaps in monitoring?
5. How long did response take?

**Improvements to Implement:**

| Issue | Improvement | Deadline |
|-------|-------------|----------|
| Rate limiting threshold too high? | Lower MAX_ATTEMPTS_PER_WINDOW from 5 to 3 | 1 week |
| Missing alert on locked accounts? | Add email notification when account_lockouts created | 2 weeks |
| Logs not monitored? | Set up log aggregation (ELK stack) | 1 month |
| User enumeration possible? | Verify timing attack defence still effective | 1 week |

### Documentation Update

- [ ] Update this runbook with lessons learned
- [ ] Add incident to historical log
- [ ] Review threat model for missed risks
- [ ] Test incident response in simulation (monthly)

### Communication

- [ ] Incident report sent to management
- [ ] Stakeholders briefed
- [ ] Staff trained on new procedures
- [ ] Public statement issued (if needed)

---

## Quick Reference

### Common Commands

**Check for active attacks:**
```sql
SELECT event_type, COUNT(*) as n FROM security_events 
WHERE created_at > NOW() - INTERVAL 1 HOUR
GROUP BY event_type;
```

**Lock all active sessions:**
```sql
TRUNCATE TABLE login_attempts;
TRUNCATE TABLE account_lockouts;
-- All users will need to re-login
```

**Reset a user's password:**
```php
$newPassword = bin2hex(random_bytes(16));
$hash = Auth::hashPassword($newPassword);
Database::run('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
// Send $newPassword to user via email (one-time only display)
```

**View incident timeline:**
```sql
SELECT created_at, event_type, subject, ip_address, detail FROM security_events
WHERE created_at > NOW() - INTERVAL 1 DAY
ORDER BY created_at ASC;
```

---

**Approved By:** Dr. Bashir  
**Last Updated:** 2026-08-11  
**Review Cycle:** Quarterly
