SET GLOBAL time_zone = 'UTC';

CREATE DATABASE IF NOT EXISTS `deliveroo` CHARACTER SET utf8;
CREATE USER IF NOT EXISTS 'dev'@'%' IDENTIFIED BY 'dev';
GRANT ALL ON `deliveroo`.* to 'dev'@'%';

CREATE DATABASE IF NOT EXISTS `deliveroo_test` CHARACTER SET utf8;
CREATE USER IF NOT EXISTS 'test'@'%' IDENTIFIED BY 'test';
GRANT ALL ON `deliveroo_test`.* to 'test'@'%';

FLUSH PRIVILEGES;