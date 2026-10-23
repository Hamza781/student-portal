-- Creates the database and all tables.
-- You can run this file again. It deletes the old tables first (fake data only!).

CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_portal;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS attendance, grades, lessons, teaching, users, subjects, classes;
SET FOREIGN_KEY_CHECKS = 1;

-- A class, for example "Network 1A".
CREATE TABLE classes (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- A subject, for example "Databases".
CREATE TABLE subjects (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ONE table for all people. The "role" column says who they are.
-- Only students have a class_id. Admins and teachers have NULL.
-- ON DELETE RESTRICT = you cannot delete a class that still has students.
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,          -- never the real password
  role          ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
  phone         VARCHAR(20)  NULL,
  birth_date    DATE         NULL,
  class_id      INT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_class FOREIGN KEY (class_id)
    REFERENCES classes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- "Teacher X teaches subject Y to class Z".
-- This is how a teacher can have many subjects in many classes.
CREATE TABLE teaching (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  class_id   INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_teaching (teacher_id, subject_id, class_id),
  CONSTRAINT fk_teaching_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_teaching_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_teaching_class   FOREIGN KEY (class_id)   REFERENCES classes(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- One lesson (your "session"): a date and a time slot for one teaching row.
CREATE TABLE lessons (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teaching_id INT UNSIGNED NOT NULL,
  lesson_date DATE NOT NULL,
  time_slot   VARCHAR(11) NOT NULL,             -- example: 08:00-09:30
  UNIQUE KEY uq_lesson (teaching_id, lesson_date, time_slot),
  CONSTRAINT fk_lessons_teaching FOREIGN KEY (teaching_id) REFERENCES teaching(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Present or absent, for one student in one lesson.
CREATE TABLE attendance (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lesson_id  INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  status     ENUM('present','absent') NOT NULL,
  UNIQUE KEY uq_attendance (lesson_id, student_id),   -- one mark per student per lesson
  CONSTRAINT fk_att_lesson  FOREIGN KEY (lesson_id)  REFERENCES lessons(id) ON DELETE CASCADE,
  CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- One grade (out of 100) per student per subject.
CREATE TABLE grades (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  grade      TINYINT UNSIGNED NOT NULL,
  graded_by  INT UNSIGNED NULL,                        -- the teacher
  graded_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_grade (student_id, subject_id),        -- one grade per student per subject
  CONSTRAINT chk_grade CHECK (grade BETWEEN 0 AND 100),
  CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_grades_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  CONSTRAINT fk_grades_teacher FOREIGN KEY (graded_by)  REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;