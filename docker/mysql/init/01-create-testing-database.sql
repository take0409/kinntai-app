CREATE DATABASE IF NOT EXISTS `attendance_test`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `attendance_test`.* TO 'laravel'@'%';
FLUSH PRIVILEGES;
