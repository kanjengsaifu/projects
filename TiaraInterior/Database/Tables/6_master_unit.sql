DROP TABLE IF EXISTS master_unit;

CREATE TABLE master_unit
(
	UnitID			BIGINT PRIMARY KEY AUTO_INCREMENT,
	UnitName		VARCHAR(255) NOT NULL,
	CreatedDate 	DATETIME NOT NULL,
	CreatedBy 		VARCHAR(255) NOT NULL,
	ModifiedDate 	TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
	ModifiedBy 		VARCHAR(255) NULL
)ENGINE=InnoDB;

CREATE UNIQUE INDEX UNIT_INDEX
ON master_unit (UnitID);