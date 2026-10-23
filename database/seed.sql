-- FAKE data only.
-- Before you import: replace PASTE_HASH_HERE with the text from make_hashes.php.
-- Keep the single quotes. Every fake user gets the same password: Test@1234

USE student_portal;
SET @hash = '$2y$10$pN1be9.JpOwIl2i7M4jqReD6QIMBQplDYAx8ZFbVUQEwCB253IDNK';

INSERT INTO classes (id, name) VALUES (1, 'Network 1A'), (2, 'Network 1B');

INSERT INTO subjects (id, name) VALUES
  (1, 'Networking Basics'), (2, 'Python Programming'),
  (3, 'Operating Systems'), (4, 'Databases');

-- ids: admin = 1, teachers = 2-3, students 4-8 (class 1), students 9-13 (class 2)
INSERT INTO users (id, full_name, email, password_hash, role, phone, birth_date, class_id) VALUES
  (1,  'Admin Demo',       'admin@school.test',    @hash, 'admin',   NULL,       NULL,         NULL),
  (2,  'Teacher One',      'teacher1@school.test', @hash, 'teacher', NULL,       NULL,         NULL),
  (3,  'Teacher Two',      'teacher2@school.test', @hash, 'teacher', NULL,       NULL,         NULL),
  (4,  'Amine Ben Ali',    'student1@school.test', @hash, 'student', '20000001', '2004-03-12', 1),
  (5,  'Sarra Trabelsi',   'student2@school.test', @hash, 'student', '20000002', '2004-07-21', 1),
  (6,  'Yassine Gharbi',   'student3@school.test', @hash, 'student', '20000003', '2003-11-02', 1),
  (7,  'Meriem Jebali',    'student4@school.test', @hash, 'student', NULL,       '2004-01-30', 1),
  (8,  'Omar Khelifi',     'student5@school.test', @hash, 'student', NULL,       NULL,         1),
  (9,  'Nour Mansouri',    'student6@school.test', @hash, 'student', '20000006', '2004-05-09', 2),
  (10, 'Skander Haddad',   'student7@school.test', @hash, 'student', NULL,       NULL,         2),
  (11, 'Ines Bouazizi',    'student8@school.test', @hash, 'student', NULL,       '2003-09-17', 2),
  (12, 'Mehdi Chaabane',   'student9@school.test', @hash, 'student', NULL,       NULL,         2),
  (13, 'Lina Ferjani',     'student10@school.test',@hash, 'student', NULL,       NULL,         2);

-- teaching: (id, teacher, subject, class)
INSERT INTO teaching (id, teacher_id, subject_id, class_id) VALUES
  (1, 2, 1, 1), (2, 2, 1, 2), (3, 2, 2, 1),
  (4, 3, 3, 1), (5, 3, 3, 2), (6, 3, 4, 2);

-- Two lessons by Teacher One for class 1
INSERT INTO lessons (id, teaching_id, lesson_date, time_slot) VALUES
  (1, 1, '2026-10-19', '08:00-09:30'),
  (2, 3, '2026-10-20', '09:45-11:15');

INSERT INTO attendance (lesson_id, student_id, status) VALUES
  (1, 4, 'present'), (1, 5, 'present'), (1, 6, 'absent'), (1, 7, 'present'), (1, 8, 'absent'),
  (2, 4, 'present'), (2, 5, 'absent'),  (2, 6, 'present'), (2, 7, 'present'), (2, 8, 'present');

-- grades: (student, subject, grade, teacher)
INSERT INTO grades (student_id, subject_id, grade, graded_by) VALUES
  (4, 1, 78, 2), (4, 2, 45, 2), (5, 1, 55, 2), (5, 2, 90, 2),
  (6, 1, 32, 2), (7, 1, 88, 2), (8, 1, 49, 2);