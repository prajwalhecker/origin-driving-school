-- =====================================================
-- Origin Driving School - Clean Schema + Sample Data
-- =====================================================

DROP DATABASE IF EXISTS origin_driving_school;
CREATE DATABASE origin_driving_school;
USE origin_driving_school;

-- BRANCHES
CREATE TABLE branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  phone VARCHAR(20)
);

-- USERS
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  branch_id INT,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  role ENUM('admin','staff','instructor','student') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- COURSES
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  class_count INT,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- COURSE INSTRUCTORS
CREATE TABLE course_instructors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  instructor_id INT NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (instructor_id) REFERENCES users(id)
);

-- VEHICLES
CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  branch_id INT,
  registration_number VARCHAR(20) UNIQUE,
  make VARCHAR(50),
  model VARCHAR(50),
  status ENUM('available','assigned','maintenance') DEFAULT 'available',
  last_maintenance DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- BOOKINGS
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  instructor_id INT,
  course_id INT,
  branch_id INT,
  vehicle_id INT,
  start_time DATETIME,
  end_time DATETIME,
  status ENUM('booked','completed','cancelled') DEFAULT 'booked',
  reminder_sms_sent BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (instructor_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- INVOICES
CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  course_id INT,
  amount DECIMAL(10,2),
  due_date DATE,
  status ENUM('pending','paid','partial') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- PAYMENTS
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT,
  amount DECIMAL(10,2),
  payment_date DATETIME,
  method VARCHAR(50),
  note TEXT,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- NOTIFICATIONS
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  type VARCHAR(50),
  content TEXT,
  is_read BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- MESSAGES
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT,
  receiver_id INT,
  subject VARCHAR(100),
  content TEXT,
  channel ENUM('in_app','email') DEFAULT 'in_app',
  sent_at DATETIME,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- NOTES
CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(50),
  entity_id INT,
  author_id INT,
  content TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id)
);

-- ATTACHMENTS
CREATE TABLE attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  note_id INT,
  file_path VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (note_id) REFERENCES notes(id)
);

-- =====================================================
-- Sample Data
-- =====================================================

-- Branches
INSERT INTO branches (name, address, phone) VALUES
('Sydney CBD Branch',    '16 King St, Sydney NSW',        '02-2918-4837'),
('Parramatta Branch',    '45 Church St, Parramatta NSW', '02-4567-8392'),
('Manly Branch',         '12 The Corso, Manly NSW',      '02-9845-1203'),
('Bondi Junction Branch','8 Campbell Pde, Bondi NSW',    '02-9371-4829'),
('Newtown Branch',       '101 Enmore Rd, Newtown NSW',   '02-5728-3947');

-- Users (Admin = YOU)
INSERT INTO users (branch_id, first_name, last_name, email, password, phone, role) VALUES
(1, 'Prajwal', 'Khadka', 'clashprajwal@gmail.com', SHA2('Gawd!$gawd123',256), '0422774077', 'admin'),
(1, 'Ram',    'Thapa',    'ram.thapa394857@example.com',    'hash1', '0412345678', 'admin'),
(2, 'Sita',   'Shrestha', 'sita.shrestha582947@example.com','hash2', '0423456789', 'staff'),
(5, 'Bikash', 'Rai',      'bikash.rai738291@example.com',   'hash3', '0434567890', 'instructor'),
(3, 'Manoj',  'Gurung',   'manoj.gurung847392@example.com', 'hash4', '0445678901', 'instructor'),
(4, 'Kiran',  'Adhikari', 'kiran.adhikari912384@example.com','hash5', '0456789012', 'student'),
(1, 'Puja',   'Rana',     'puja.rana621738@example.com',     'hash6', '0467890123', 'student'),
(2, 'Sanjay', 'Magar',    'sanjay.magar839201@example.com',  'hash7', '0478901234', 'student'),
(5, 'Anita',  'Chettri',  'anita.chettri384729@example.com', 'hash8', '0489012345', 'student'),
(3, 'Hari',   'Basnet',   'hari.basnet592837@example.com',   'hash9', '0490123456', 'student'),
(4, 'Maya',   'KC',       'maya.kc783912@example.com',       'hash10','0411122233','student');

-- Courses
INSERT INTO courses (name, price, class_count, description) VALUES
('Basic Driving',    15000.00,  8, 'Introduction to car driving basics'),
('Advanced Driving', 25000.00, 12, 'Advanced maneuvers and highway driving'),
('Motorcycle Riding',10000.00,  6, 'Basic motorcycle operation and safety');

-- Course Instructors
INSERT INTO course_instructors (course_id, instructor_id) VALUES
(1, 4),
(1, 5),
(2, 4),
(2, 5),
(3, 4);

-- Vehicles
INSERT INTO vehicles (branch_id, registration_number, make, model, status, last_maintenance) VALUES
(1, 'SYD-1234', 'Toyota', 'Corolla', 'available',    '2025-06-01'),
(2, 'PAR-5678', 'Hyundai','Accent',  'maintenance',  '2025-05-15'),
(3, 'MAN-9101', 'Suzuki', 'Swift',   'available',    '2025-07-20'),
(5, 'NEW-2345', 'Kia',    'Rio',     'assigned',     '2025-06-25');

-- Bookings
INSERT INTO bookings (student_id, instructor_id, course_id, branch_id, vehicle_id, start_time, end_time, status) VALUES
(7, 4, 1, 1, 1, '2025-02-10 09:00:00','2025-02-10 11:00:00','completed'),
(8, 5, 3, 2, 2, '2025-03-05 14:00:00','2025-03-05 16:00:00','booked'),
(6, 4, 2, 3, 3, '2025-04-01 10:00:00','2025-04-01 12:00:00','cancelled');

-- Invoices
INSERT INTO invoices (student_id, course_id, amount, due_date, status) VALUES
(7, 1, 15000.00,'2025-02-15','paid'),
(8, 3, 10000.00,'2025-03-10','pending'),
(6, 2, 25000.00,'2025-04-05','partial');

-- Payments
INSERT INTO payments (invoice_id, amount, payment_date, method, note) VALUES
(1, 15000.00,'2025-02-12 10:30:00','cash','Full payment received'),
(3, 12500.00,'2025-04-02 09:45:00','bank_transfer','50% advance');

-- Notifications
INSERT INTO notifications (user_id, type, content, is_read) VALUES
(7, 'booking_reminder', 'Reminder: Your lesson on 2025-02-10 at 09:00 is coming up.', 0),
(8, 'payment_reminder','Reminder: Invoice #2 of $10000 is due on 2025-03-10.', 0);

-- Messages
INSERT INTO messages (sender_id, receiver_id, subject, content, channel, sent_at) VALUES
(3, 7, 'Welcome to Driving School', 'Dear Puja, welcome aboard!', 'in_app','2025-01-01 09:00:00'),
(4, 8, 'Lesson Update', 'Your lesson on 2025-03-05 has been booked.','email','2025-02-20 15:30:00');

-- Notes
INSERT INTO notes (entity_type, entity_id, author_id, content, created_at) VALUES
('student', 7, 3, 'Puja shows good control at low speeds.','2025-02-10 11:15:00');

-- Attachments
INSERT INTO attachments (note_id, file_path, uploaded_at) VALUES
(1, 'uploads/notes/puja.pdf','2025-02-10 11:16:00');
