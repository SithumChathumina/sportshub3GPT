-- NSBM SportsHub database
-- Import this file using phpMyAdmin or: mysql -u root -p < database/sportshub.sql

CREATE DATABASE IF NOT EXISTS sportshub3_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportshub3_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS match_registrations;
DROP TABLE IF EXISTS tournament_registrations;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS training_sessions;
DROP TABLE IF EXISTS memberships;
DROP TABLE IF EXISTS facilities;
DROP TABLE IF EXISTS clubs;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clubs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    sport VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    coach_name VARCHAR(100) NOT NULL,
    meeting_day VARCHAR(20) NOT NULL,
    location VARCHAR(120) NOT NULL,
    max_members SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE memberships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    club_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    UNIQUE KEY unique_membership (user_id, club_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE training_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    club_id INT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    venue VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tournaments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    sport VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    venue VARCHAR(120) NOT NULL,
    registration_deadline DATE NOT NULL,
    max_teams SMALLINT UNSIGNED NOT NULL DEFAULT 8,
    status ENUM('upcoming', 'ongoing', 'completed') NOT NULL DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE matches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT UNSIGNED NULL,
    sport VARCHAR(80) NOT NULL,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    match_datetime DATETIME NOT NULL,
    venue VARCHAR(120) NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    home_score SMALLINT UNSIGNED NULL,
    away_score SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tournament_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    status ENUM('registered', 'withdrawn') NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tournament_user (tournament_id, user_id),
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE match_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    preferred_team ENUM('home', 'away') NOT NULL,
    status ENUM('registered', 'withdrawn') NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_match_user (match_id, user_id),
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE facilities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('facility', 'equipment') NOT NULL,
    location VARCHAR(120) NOT NULL,
    total_units SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('active', 'maintenance') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    facility_id INT UNSIGNED NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    purpose VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    admin_note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Demo passwords: Admin@123 and Student@123
INSERT INTO users (student_id, full_name, email, password_hash, role) VALUES
('ADMIN001', 'SportsHub Administrator', 'admin@nsbm.lk', '$2y$10$cLFQJDpWwlz6EWw6C9NhEePPDYNVtiNYdyMIkgFXNi6IUBboHbCce', 'admin'),
('26.1IFSL001', 'Demo Student', 'student@nsbm.lk', '$2y$10$tZIpnUWgukVN1XOoT62F2eNJ/jyaQkNaGaDgyDCj9ZtXOU8pBSzrm', 'student'),
('26.1IFSL002', 'Nethmi Perera', 'nethmi@nsbm.lk', '$2y$10$tZIpnUWgukVN1XOoT62F2eNJ/jyaQkNaGaDgyDCj9ZtXOU8pBSzrm', 'student'),
('26.1IFSL003', 'Kavindu Silva', 'kavindu@nsbm.lk', '$2y$10$tZIpnUWgukVN1XOoT62F2eNJ/jyaQkNaGaDgyDCj9ZtXOU8pBSzrm', 'student');

INSERT INTO clubs (name, sport, description, coach_name, meeting_day, location, max_members) VALUES
('NSBM Cricket Club', 'Cricket', 'Develop batting, bowling and fielding skills through weekly practice and inter-university matches.', 'Mr. Chamara Fernando', 'Tuesday', 'NSBM Cricket Ground', 45),
('NSBM Football Club', 'Football', 'A welcoming club for players of all levels, focused on teamwork, fitness and competitive football.', 'Mr. Ruwan Jayasinghe', 'Wednesday', 'NSBM Football Field', 40),
('NSBM Basketball Club', 'Basketball', 'Train in ball handling, shooting and game strategy with our student basketball community.', 'Ms. Dinusha Wijeratne', 'Thursday', 'Indoor Sports Complex', 30),
('NSBM Badminton Club', 'Badminton', 'Friendly and competitive badminton sessions for beginners and experienced players.', 'Ms. Sanduni Peris', 'Friday', 'Indoor Sports Complex', 35),
('NSBM Rugby Club', 'Rugby', 'Build strength, discipline and team spirit through regular rugby training and fixtures.', 'Mr. Asela Bandara', 'Monday', 'NSBM Rugby Field', 40),
('NSBM Athletics Club', 'Athletics', 'Track and field training covering sprinting, distance running, jumping and throwing events.', 'Ms. Ishara Gunasekara', 'Saturday', 'NSBM Running Track', 50);

INSERT INTO memberships (user_id, club_id, status, reviewed_at) VALUES
(2, 1, 'approved', NOW()),
(2, 3, 'approved', NOW()),
(2, 4, 'pending', NULL),
(3, 1, 'approved', NOW()),
(3, 2, 'approved', NOW()),
(4, 3, 'approved', NOW());

INSERT INTO training_sessions (club_id, title, session_date, start_time, end_time, venue) VALUES
(1, 'Batting and fielding practice', CURDATE() + INTERVAL 2 DAY, '15:30', '17:30', 'NSBM Cricket Ground'),
(3, 'Shooting fundamentals', CURDATE() + INTERVAL 3 DAY, '16:00', '18:00', 'Indoor Sports Complex'),
(1, 'Bowling nets', CURDATE() + INTERVAL 7 DAY, '15:30', '17:30', 'NSBM Cricket Ground'),
(2, 'Tactical training', CURDATE() + INTERVAL 4 DAY, '16:00', '18:00', 'NSBM Football Field');

INSERT INTO tournaments (name, sport, description, start_date, end_date, venue, registration_deadline, max_teams, status) VALUES
('NSBM Inter-Faculty Cricket Cup', 'Cricket', 'A twenty-over tournament between faculty teams. Register your team before the deadline.', CURDATE() + INTERVAL 18 DAY, CURDATE() + INTERVAL 20 DAY, 'NSBM Cricket Ground', CURDATE() + INTERVAL 10 DAY, 8, 'upcoming'),
('Green Basketball Challenge', 'Basketball', 'A fast-paced 5x5 basketball challenge open to all NSBM students.', CURDATE() + INTERVAL 25 DAY, CURDATE() + INTERVAL 26 DAY, 'Indoor Sports Complex', CURDATE() + INTERVAL 15 DAY, 12, 'upcoming'),
('NSBM Football League', 'Football', 'The semester football league featuring group matches and a knockout round.', CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 12 DAY, 'NSBM Football Field', CURDATE() - INTERVAL 15 DAY, 8, 'ongoing');

INSERT INTO matches (tournament_id, sport, home_team, away_team, match_datetime, venue, status, home_score, away_score) VALUES
(1, 'Cricket', 'Faculty of Computing', 'Faculty of Business', CURDATE() + INTERVAL 18 DAY + INTERVAL 9 HOUR, 'NSBM Cricket Ground', 'scheduled', NULL, NULL),
(2, 'Basketball', 'NSBM Green', 'NSBM Gold', CURDATE() + INTERVAL 25 DAY + INTERVAL 10 HOUR, 'Indoor Sports Complex', 'scheduled', NULL, NULL),
(3, 'Football', 'Computing FC', 'Business FC', CURDATE() - INTERVAL 7 DAY + INTERVAL 15 HOUR, 'NSBM Football Field', 'completed', 3, 1),
(3, 'Football', 'Engineering FC', 'Science FC', CURDATE() - INTERVAL 5 DAY + INTERVAL 15 HOUR, 'NSBM Football Field', 'completed', 1, 2),
(NULL, 'Badminton', 'NSBM A', 'NSBM B', CURDATE() - INTERVAL 3 DAY + INTERVAL 16 HOUR, 'Indoor Sports Complex', 'completed', 2, 2),
(NULL, 'Cricket', 'NSBM XI', 'Alumni XI', CURDATE() + INTERVAL 6 DAY + INTERVAL 14 HOUR, 'NSBM Cricket Ground', 'scheduled', NULL, NULL);

INSERT INTO facilities (name, type, location, total_units, status) VALUES
('Indoor Court 1', 'facility', 'Indoor Sports Complex', 1, 'active'),
('Indoor Court 2', 'facility', 'Indoor Sports Complex', 1, 'active'),
('Football Field', 'facility', 'NSBM Outdoor Grounds', 1, 'active'),
('Cricket Practice Net', 'facility', 'NSBM Cricket Ground', 2, 'active'),
('Badminton Racket Set', 'equipment', 'Sports Equipment Room', 12, 'active'),
('Basketball', 'equipment', 'Sports Equipment Room', 15, 'active'),
('Football Training Cones', 'equipment', 'Sports Equipment Room', 20, 'active'),
('Volleyball Net', 'equipment', 'Sports Equipment Room', 2, 'maintenance');

INSERT INTO bookings (user_id, facility_id, booking_date, start_time, end_time, quantity, purpose, status, admin_note) VALUES
(2, 6, CURDATE() + INTERVAL 3 DAY, '15:00', '16:00', 2, 'Club practice', 'approved', 'Collect from the equipment room.'),
(2, 1, CURDATE() + INTERVAL 5 DAY, '14:00', '16:00', 1, 'Friendly basketball game', 'pending', NULL);
