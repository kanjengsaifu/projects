DROP TABLE IF EXISTS master_itemnotification;

CREATE TABLE master_itemnotification
(
	ItemNotificationID	BIGINT PRIMARY KEY AUTO_INCREMENT,
	ItemID 		BIGINT,
	Remarks		TEXT,
	CreatedDate 	DATETIME NOT NULL,
	CreatedBy 	VARCHAR(255) NOT NULL,
	ModifiedDate 	TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
	ModifiedBy 	VARCHAR(255) NULL,
	FOREIGN KEY(ItemID) REFERENCES master_item(ItemID) ON UPDATE CASCADE ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE UNIQUE INDEX ITEMNOTIFICATION_INDEX
ON master_itemnotification (ItemNotificationID, ItemID);