CREATE DATABASE IF NOT EXISTS messaging_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'chatting_user'@'localhost' IDENTIFIED BY 'chatting_password_change_in_prod';
GRANT ALL PRIVILEGES ON messaging_platform.* TO 'chatting_user'@'localhost';
FLUSH PRIVILEGES;

USE messaging_platform;
