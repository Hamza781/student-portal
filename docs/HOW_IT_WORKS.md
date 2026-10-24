# How the Student Portal works

## 1. One request, from start to end
Example: a teacher opens http://localhost:8080/teacher/lessons.php

1. BROWSER: you type the address. The browser opens a TCP connection to port 8080.
2. HTTP: the browser sends a text request: "GET /teacher/lessons.php" plus a Cookie with the session id.
3. APACHE: it receives the request. Its DocumentRoot is D:/student-portal/public. It finds the file
   public/teacher/lessons.php. The extension is .php, so Apache gives the file to PHP.
   (The folders src/ and config/ are outside public/, so Apache can never serve them.)
4. PHP: it runs the file. The file loads src/bootstrap.php. PHP starts the session and finds the user.
   require_role('teacher') checks the role.
5. PDO and MYSQL: PHP connects to MariaDB with the limited user (password from config/db.php).
   It sends the SQL with "?" placeholders and the values separately. MariaDB returns rows.
6. PHP builds the HTML text. Every value is escaped with e() (htmlspecialchars).
7. RESPONSE: Apache sends the HTML back with headers ("200 OK", security headers).
8. BROWSER: it shows the HTML. It sees link/script tags and makes 2 more requests
   for /css/style.css and /js/app.js. Static files are served directly by Apache.

## 2. Login and sessions
- HTTP has no memory. Each request is new. A session gives it memory.
- On login.php: PHP finds the user by email, then password_verify() compares the typed password
  with the stored hash. Passwords are never stored in plain text.
- If it is correct: session_regenerate_id(true) makes a NEW session id, and we save
  $_SESSION['user_id'].
- PHP sends a cookie (PHPSESSID) to the browser. The cookie has only a random id.
  The data stays on the server. The cookie is HttpOnly and SameSite=Lax.
- On every next request, the browser sends the cookie, and PHP finds $_SESSION again.
- Logout: we empty $_SESSION, delete the cookie and destroy the session.

## 3. How roles are checked
- There is one users table with a "role" column.
- The role is NOT stored in the cookie or in a form. At every request, current_user() reads the
  user (and role) from the database, using the id from the session.
- The first line of every protected page is require_role('admin') (or teacher, student).
  Not logged in -> redirect to login. Wrong role -> 403 error.
- Public registration writes role = 'student' in the SQL code. The form has no role field.
  Only the admin page "create user" reads a role from a form, and it is admin-only.
- Ownership: a teacher can only open his own lessons (the SQL has teacher_id = ?).
  A student only sees rows with student_id = his own id (taken from the session, not from the URL).

## 4. The 10 questions a teacher may ask

1. What is SQL injection? How do you stop it?
   An attacker puts SQL in an input, like ' OR 1=1 --. I use PDO prepared statements: the SQL and the
   values are sent separately, so the input is always only data.

2. Why password_hash and not MD5 or plain text?
   If the database leaks, the attacker must not read passwords. password_hash is slow, uses a random
   salt, and cannot be reversed. password_verify checks a password against the hash.

3. What is a session? Where is the data?
   A session keeps the login between requests. The data (user id) is on the server.
   The browser only keeps a random session id in a cookie.

4. Why session_regenerate_id() after login?
   It stops session fixation. An attacker cannot give you a session id and then use it after you log in.

5. What is XSS? How do you stop it?
   An attacker saves JavaScript in a field, for example his name. It would run in other users' browsers.
   I escape every output with htmlspecialchars() (the function e()). I also use a
   Content-Security-Policy header that blocks inline scripts.

6. What is CSRF? How do you stop it?
   Another site makes your browser send a form to our site while you are logged in.
   Every POST form has a secret token that is also saved in the session. If the token is wrong,
   the request is refused. The cookie also has SameSite=Lax.

7. How do you make sure a student cannot open the admin pages?
   Every page starts with require_role(). The check is on the server. Hiding a menu link is not
   security. If a student types the admin URL, he gets 403.

8. How does a student see only his own grades?
   The SQL uses WHERE student_id = ? with the id from the session. The URL has no id the student can change.

9. Why are src/ and config/ outside public/?
   Apache only serves public/. Nobody can open the database password or the PHP helper code from the browser.
   config/db.php is also in .gitignore, so it is not on GitHub.

10. Why validate on the server if JavaScript already checks?
    JavaScript runs in the user's browser, so an attacker can turn it off or send requests with other tools
    (Kali, curl). Only server-side checks can be trusted. JavaScript is only for comfort.

## Bonus short answers
- Why a MySQL user and not root? Least privilege. If the site is hacked, the attacker cannot drop tables
  or read other databases.
- Why one users table? Same fields for everybody (name, email, password). The role column tells who is who.
  It is simple and avoids repeated code.
- What is a foreign key? A link between tables. It stops wrong data (for example a grade for a student who
  does not exist). ON DELETE CASCADE deletes the linked rows too.