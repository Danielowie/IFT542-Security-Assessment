# INCIDENT RESPONSE RUNBOOK
## Student Registration Web Application
**Duration:** One Page | **Format:** Quick Reference

---

## 1. PREPARATION
**Objective:** Enable rapid detection and response

**Detection Setup:**
- Monitor security.log for these patterns: `validation_rejected`, `login_failed`, `account_locked`, `csrf_rejected`, `ssrf_blocked`, `authorization_denied`
- Alert threshold: >10 events of same type in 5 minutes
- Log review: Daily automated scans for suspicious patterns
- Test quarterly: Simulate incidents and validate response time

**Tools Ready:**
- Database: `mysql -u root -p student_registration`
- Logs: `/mnt/user-data/uploads/2021-1-84189CF_IFT542/storage/logs/security.log`
- Admin access: Verified credentials in secure vault
- Communication: Incident notification list prepared

---

## 2. IDENTIFICATION
**Objective:** Confirm and classify the incident

**Detection Steps:**
1. Review alert notification and validate event
2. Check `security.log` for pattern and timing
3. Query database for affected records: `SELECT * FROM security_events WHERE event_type = ? ORDER BY created_at DESC LIMIT 50;`
4. Classify incident severity:
   - **CRITICAL:** SQL injection successful, RCE, data breach (Immediate response)
   - **HIGH:** Authentication bypass, privilege escalation, CSRF successful (1-hour response)
   - **MEDIUM:** Brute force attempts, malformed requests (Next business day)

**Questions to Answer:**
- How many users/records affected?
- Which accounts/data exposed?
- How long was the incident active?
- Attacker IP/geographic origin?

---

## 3. CONTAINMENT
**Objective:** Stop the attack immediately

**If SQL Injection Detected:**
- Block attacker IP at firewall immediately
- Review /admin/students.php last 50 queries in `security_events` table
- Restore database from backup if data modified

**If Brute Force Attack:**
- `DELETE FROM account_lockouts WHERE locked_until < NOW();` (Clear stale lockouts)
- Verify rate limiting is enforced: Check login_attempts table for same IP/identifier pattern
- Block IP range at firewall if distributed attack

**If XSS/CSRF Exploitation:**
- Invalidate all active sessions: Clear `user_sessions` table (all users log out)
- Alert users via email of security incident
- Force password reset for affected accounts

**If SSRF Detected:**
- Review url_preview.php logs for unauthorized destinations
- Block attacker IP immediately
- Confirm no internal services were accessed

**If Unauthorized Access:**
- Revoke compromised account: Update `users.is_active = 0`
- Audit all actions by that account in `security_events`
- Force password reset; create new temporary password
- Review all accessed records

---

## 4. ERADICATION
**Objective:** Eliminate the attack vector and prevent recurrence

**If Input Validation Bypassed:**
- Review source code: Is Validator.php being called?
- Verify parameterised queries used: Check Database.php for `?` placeholders
- Test with known payloads: Run automated tests `php tests/run_tests.php`
- Patch and redeploy if code issue found

**If Authentication Compromised:**
- Force all password resets: `UPDATE users SET password_hash = ? WHERE role = 'student';`
- Enable MFA for admin accounts
- Review SSH/database access logs for unauthorized connections
- Update authentication library if needed

**If Logs Tampered:**
- Verify log integrity: File modification time and size
- Restore logs from backup if corrupted
- Enable write-once log storage for future incidents
- Review database audit tables: `security_events` table immutability

**Post-Eradication Testing:**
- Run full test suite: `php tests/run_tests.php` (must pass 100%)
- Manually test vulnerable endpoint with safe payload
- Verify all security controls active

---

## 5. RECOVERY
**Objective:** Restore normal operations

**Data Recovery:**
- Restore affected tables from backup if data modified
- Verify data integrity: Checksums match pre-incident state
- Re-enable affected user accounts: `UPDATE users SET is_active = 1`
- Clear temporary blocks/locks

**Service Restoration:**
- Redeploy patched code if applicable
- Clear application cache and compiled files
- Restart web server: `php -S localhost:8000 -t public`
- Verify all functionality: Login, enrollment, uploads

**Communication:**
- Notify affected users of incident and remediation
- Provide guidance on password changes and account security
- Publish incident summary to stakeholders (non-technical)

**Timeline:** Usually completes within 4 hours for contained incidents

---

## 6. LESSONS LEARNED (Post-Incident)
**Objective:** Prevent recurrence through continuous improvement

**Review Meeting (within 48 hours):**
- What warning signs did we miss?
- Why did detection fail/succeed?
- How fast was our response? (Target: <30 min)
- What broke in the containment?
- What code/config needs improvement?

**Documentation:**
- Update this runbook with lessons learned
- Record incident details in `/evidence/incident_record.md`
- Timeline: When detected? When contained? When recovered?
- Root cause: Technical or procedural?
- Recommendations: Code hardening, monitoring, process changes

**Prevention for Future:**
- Add new alert rule if pattern not previously detected
- Strengthen that specific control
- Update training for team
- Schedule follow-up audit in 30 days

**Archive:** Store incident report in `/evidence/incidents/` for compliance

---

## QUICK REFERENCE COMMANDS

| Action | Command |
|--------|---------|
| View recent events | `tail -n 50 storage/logs/security.log` |
| Find attack pattern | `grep "validation_rejected" storage/logs/security.log \| tail -20` |
| Query incidents | `mysql -e "SELECT event_type, COUNT(*) FROM student_registration.security_events GROUP BY event_type;"` |
| Block attacker IP | Edit firewall rules or `.htaccess` to deny from specific IP |
| Clear lockouts | `DELETE FROM student_registration.account_lockouts WHERE locked_until < NOW();` |
| Invalidate sessions | `DELETE FROM student_registration.sessions;` (all users log out) |
| Reset password | `UPDATE student_registration.users SET password_hash = '$argon2id...' WHERE id = ?;` |
| Restore database | `mysql student_registration < backup_20260815.sql` |

---

## INCIDENT SEVERITY MATRIX

| Severity | Criteria | Response Time | Escalation |
|----------|----------|----------------|-----------|
| **CRITICAL** | Active data breach, RCE, system down | Immediate (5 min) | Notify CTO + Security |
| **HIGH** | Auth bypass, privilege escalation, CSRF working | 1 hour | Notify Team Lead |
| **MEDIUM** | Brute force, malformed requests, scanning | Next business day | Document in log |
| **LOW** | Single failed validation, probing | Track in metrics | Quarterly review |

---

**Last Updated:** August 15, 2026  
**Review Frequency:** Quarterly  
**Next Drill Date:** November 15, 2026
