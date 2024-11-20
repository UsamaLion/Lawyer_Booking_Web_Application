-- Insert sample users
INSERT INTO Users (username, email, password, role) VALUES
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('user2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('lawyer1', 'lawyer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer'),
('lawyer2', 'lawyer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer'),
('lawyer3', 'lawyer3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer'),
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample lawyers
INSERT INTO Lawyers (user_id, license_number, specialization, city, approval_status) VALUES
((SELECT id FROM Users WHERE username = 'lawyer1'), 'L12345', 'Family Law', 'New York', 'approved'),
((SELECT id FROM Users WHERE username = 'lawyer2'), 'L67890', 'Criminal Law', 'Los Angeles', 'approved'),
((SELECT id FROM Users WHERE username = 'lawyer3'), 'L13579', 'Corporate Law', 'Chicago', 'approved');

-- Note: The password for all sample users is 'password'
