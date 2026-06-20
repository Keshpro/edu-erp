
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student','faculty') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL,
  program VARCHAR(80) NOT NULL,
  status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
  enrollment_date DATE
);

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  credits INT NOT NULL DEFAULT 3,
  lecturer VARCHAR(120),
  schedule VARCHAR(80),
  room VARCHAR(40),
  seats_taken INT DEFAULT 0,
  seats_total INT DEFAULT 40,
  department VARCHAR(80)
);

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_pk INT NOT NULL,
  course_id INT NOT NULL,
  status ENUM('approved','pending') DEFAULT 'pending',
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  exam_date DATE NOT NULL,
  exam_time TIME NOT NULL,
  location VARCHAR(80),
  status VARCHAR(20) DEFAULT 'scheduled'
);

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_pk INT NOT NULL,
  course_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('present','absent') DEFAULT 'present'
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_read TINYINT DEFAULT 0
);

-- Seed Users
INSERT INTO users (name, email, password, role) VALUES
('Admin User',   'admin@edu.com',   '$2y$10$HJxx10RAVijDBtn1j5X1i.P9IsWtNV7gPCe2CsTxCCwqx9Km.Y9yS',   'admin'),
('Alice Smith',  'student@edu.com', '$2y$10$U9U55rrvnFAJnQtuRg6FOu1UldYtYgevzJ7TgUdD7Nn2aA5gn1Num', 'student'),
('Dr. Alan Turing','faculty@edu.com','$2y$10$88rwZKdF.UOcrVqKCI7xFuLZweXzCs505zgekayqKckALlAHWeRaC', 'faculty');

-- Seed Students
INSERT INTO students (student_id, name, email, program, status, enrollment_date) VALUES
('STU-2024-001', 'Alice Smith',       'student@edu.com',      'Computer Science',    'active',  '2024-09-01'),
('STU-2024-002', 'John Doe',          'john.doe@edu.com',     'Software Engineering','active',  '2024-09-01'),
('STU-2023-145', 'Emily Johnson',     'emily.j@edu.com',      'Business Admin',      'active',  '2023-09-01'),
('STU-2014-088', 'Michael Williams',  'michael.w@edu.com',    'Computer Science',    'pending', '2014-09-01');

-- Seed Courses
INSERT INTO courses (code, name, credits, lecturer, schedule, room, seats_taken, seats_total, department) VALUES
('CS301',   'Advanced Data Structures', 4, 'Dr. Alan Turing',  'Mon/Wed 10:00 AM', 'Sci-204', 38, 40, 'Computer Science'),
('MATH250', 'Linear Algebra II',        3, 'Dr. Emmy Noether', 'Tue/Thu 2:00 PM',  'Math-101',12, 40, 'Mathematics'),
('ENG105',  'Modern World Literature',  3, 'TBA',              'Fri 9:00 AM',      'Arts-305',15, 30, 'English'),
('CS405',   'Software Engineering',     4, 'Prof. Lee',        'Mon/Wed 1:00 PM',  'Sci-301', 20, 40, 'Computer Science'),
('CS320',   'Database Systems',         3, 'Dr. Smith',        'Tue/Thu 10:00 AM', 'Sci-205', 25, 40, 'Computer Science'),
('PSY101',  'Intro to Psychology',      3, 'Dr. Davis',        'Fri 1:00 PM',      'Arts-101',30, 40, 'Psychology');

-- Seed Exams
INSERT INTO exams (course_id, exam_date, exam_time, location, status) VALUES
(1, '2024-10-24', '09:00:00', 'Main Auditorium', 'scheduled'),
(5, '2024-10-20', '14:00:00', 'Hall B',          'scheduled');

-- Seed Registrations (Alice = student_pk 1)
INSERT INTO registrations (student_pk, course_id, status) VALUES
(1, 4, 'approved'),
(1, 5, 'pending'),
(1, 6, 'approved');

-- Seed Notifications (user_id 2 = Alice)
INSERT INTO notifications (user_id, title, message) VALUES
(2, 'Final Exam Schedule Published', 'The final examination schedule for CS301 (Data Structures) is now available in your portal.'),
(2, 'Course Registration Deadline',  'Registration for the Spring semester closes in 2 days. Late fees will apply.');
