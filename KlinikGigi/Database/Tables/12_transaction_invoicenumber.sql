DROP TABLE IF EXISTS transaction_invoicenumber;

CREATE TABLE transaction_invoicenumber
(
	InvoiceNumberID		BIGINT PRIMARY KEY AUTO_INCREMENT,
	TransactionDate		DATE,
	OrderNumber			VARCHAR(20),
	CreatedDate 		DATETIME NOT NULL,
	CreatedBy 			VARCHAR(255) NOT NULL,
	ModifiedDate 		TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL,
	ModifiedBy			VARCHAR(255) NULL
	
)ENGINE=InnoDB;

CREATE UNIQUE INDEX INVOICENUMBER_INDEX
ON transaction_invoicenumber (InvoiceNumberID, TransactionDate);