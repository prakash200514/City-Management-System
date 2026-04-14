-- Create Database
CREATE DATABASE IF NOT EXISTS smart_city;
USE smart_city;

-- 1.Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert Roles
INSERT INTO roles (name) VALUES ('Super Admin'), ('Department Admin'), ('Field Worker'), ('Citizen');

-- 2. Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Departments
INSERT INTO departments (name, description) VALUES 
('Water Supply', 'Manages water distribution and purity complaints.'),
('Electricity', 'Handles power cuts, voltage issues, and street lights.'),
('Sanitation', 'Garbage collection, sewage, and cleanliness.'),
('Roads', 'Road maintenance, potholes, and traffic infrastructure.'),
('Public Complaint Management', 'General public grievances.');

-- 3. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    dept_id INT DEFAULT NULL,
    contact_number VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- 4. Complaints Table
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    dept_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(id),
    FOREIGN KEY (dept_id) REFERENCES departments(id)
);

-- 5. Task Assignments Table
CREATE TABLE IF NOT EXISTS task_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    worker_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Assigned', 'In Progress', 'Completed') DEFAULT 'Assigned',
    completion_notes TEXT,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id),
    FOREIGN KEY (worker_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- 6. Status Logs (Audit Trail) Table
CREATE TABLE IF NOT EXISTS status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- 7. Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
