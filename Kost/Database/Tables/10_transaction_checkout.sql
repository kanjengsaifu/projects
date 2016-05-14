DROP TABLE IF EXISTS transaction_checkout;

CREATE TABLE transaction_checkout
(
	CheckOutID	 	BIGINT PRIMARY KEY AUTO_INCREMENT,
	CheckInID		BIGINT,
	TransactionDate	DATETIME NOT NULL,
	CreatedDate 	DATETIME NOT NULL,
	CreatedBy 		VARCHAR(255) NOT NULL,
	ModifiedDate 	TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
	ModifiedBy 		VARCHAR(255) NULL,
	FOREIGN KEY(CheckInID) REFERENCES transaction_checkin(CheckInID) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB;


CREATE UNIQUE INDEX CHECKOUTID_INDEX
ON transaction_checkout (CheckOutID, CheckInID);