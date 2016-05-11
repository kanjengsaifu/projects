DROP TABLE IF EXISTS master_room;

CREATE TABLE master_room
(
	RoomID 			BIGINT PRIMARY KEY AUTO_INCREMENT,
	RoomName 		VARCHAR(255) NOT NULL,
	StatusID	 	INT NOT NULL,
	CreatedDate 	DATETIME NOT NULL,
	CreatedBy 		VARCHAR(255) NOT NULL,
	ModifiedDate 	TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
	ModifiedBy 		VARCHAR(255) NULL,
	FOREIGN KEY(StatusID) REFERENCES master_status(StatusID)
)ENGINE=InnoDB;