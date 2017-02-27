CREATE TABLE llx_c_mission_order_carriage (
    rowid    integer AUTO_INCREMENT PRIMARY KEY,
    code     varchar(25) NOT NULL,
    label    varchar(80) NOT NULL,
    active   tinyint DEFAULT 1 NOT NULL,
    entity   integer  DEFAULT 1 NOT NULL,
)ENGINE=innodb;