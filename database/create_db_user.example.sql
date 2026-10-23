-- Creates a limited MySQL user. The website uses this user, NOT root.
-- Why? If the site is hacked, the attacker can only read and change data.
-- He cannot drop tables or touch other databases.
-- 1) Change the password below. 2) Put the SAME password in config/db.php.

CREATE USER IF NOT EXISTS 'portal_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON student_portal.* TO 'portal_user'@'localhost';
FLUSH PRIVILEGES;