CREATE TABLE IF NOT EXISTS `b_awz_flashcallapi_codes` (
    ID int(18) NOT NULL AUTO_INCREMENT,
    PHONE varchar(65) NOT NULL,
    EXT_ID varchar(65) NOT NULL,
    CREATE_DATE datetime NOT NULL,
    PRM longtext,
    PRIMARY KEY (`ID`),
    index IX_PHONE (PHONE),
    index IX_EXT_ID (EXT_ID)
);