CREATE TABLE IF NOT EXISTS students (
    nic VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    email VARCHAR(120) NOT NULL,
    course VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_activity (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(20) NOT NULL,
    student_nic VARCHAR(20) NULL,
    admin_username VARCHAR(50) NOT NULL,
    details_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @updated_at_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND COLUMN_NAME = 'updated_at'
);
SET @add_updated_at_sql := IF(
    @updated_at_exists = 0,
    'ALTER TABLE students ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
    'DO 0'
);
PREPARE add_updated_at_stmt FROM @add_updated_at_sql;
EXECUTE add_updated_at_stmt;
DEALLOCATE PREPARE add_updated_at_stmt;

SET @students_course_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND INDEX_NAME = 'idx_students_course'
);
SET @add_students_course_idx_sql := IF(
    @students_course_idx_exists = 0,
    'ALTER TABLE students ADD INDEX idx_students_course (course)',
    'DO 0'
);
PREPARE add_students_course_idx_stmt FROM @add_students_course_idx_sql;
EXECUTE add_students_course_idx_stmt;
DEALLOCATE PREPARE add_students_course_idx_stmt;

SET @students_created_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'students'
      AND INDEX_NAME = 'idx_students_created_at'
);
SET @add_students_created_idx_sql := IF(
    @students_created_idx_exists = 0,
    'ALTER TABLE students ADD INDEX idx_students_created_at (created_at)',
    'DO 0'
);
PREPARE add_students_created_idx_stmt FROM @add_students_created_idx_sql;
EXECUTE add_students_created_idx_stmt;
DEALLOCATE PREPARE add_students_created_idx_stmt;

SET @activity_created_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'student_activity'
      AND INDEX_NAME = 'idx_activity_created_at'
);
SET @add_activity_created_idx_sql := IF(
    @activity_created_idx_exists = 0,
    'ALTER TABLE student_activity ADD INDEX idx_activity_created_at (created_at)',
    'DO 0'
);
PREPARE add_activity_created_idx_stmt FROM @add_activity_created_idx_sql;
EXECUTE add_activity_created_idx_stmt;
DEALLOCATE PREPARE add_activity_created_idx_stmt;

SET @activity_action_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'student_activity'
      AND INDEX_NAME = 'idx_activity_action'
);
SET @add_activity_action_idx_sql := IF(
    @activity_action_idx_exists = 0,
    'ALTER TABLE student_activity ADD INDEX idx_activity_action (action)',
    'DO 0'
);
PREPARE add_activity_action_idx_stmt FROM @add_activity_action_idx_sql;
EXECUTE add_activity_action_idx_stmt;
DEALLOCATE PREPARE add_activity_action_idx_stmt;

SET @activity_student_idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'student_activity'
      AND INDEX_NAME = 'idx_activity_student_nic'
);
SET @add_activity_student_idx_sql := IF(
    @activity_student_idx_exists = 0,
    'ALTER TABLE student_activity ADD INDEX idx_activity_student_nic (student_nic)',
    'DO 0'
);
PREPARE add_activity_student_idx_stmt FROM @add_activity_student_idx_sql;
EXECUTE add_activity_student_idx_stmt;
DEALLOCATE PREPARE add_activity_student_idx_stmt;

INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$12$P1E3R1vsJdiXAeg2FmiuQ.voSnEajEovbXWZa7kMw3cBJCqzRYBjC')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);
