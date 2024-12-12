CREATE TABLE `dis_mec_votes` (
  `ID`           INT            NOT NULL AUTO_INCREMENT,
  `CONTENT_ID`   INT(11)        NOT NULL,
  `CONTENT_TYPE` VARCHAR(250)   NOT NULL,
  `USER_ID`      INT(11)        NOT NULL,
  `VOTE`         TINYINT(4)     NOT NULL,
  INDEX `VOTE_INDEX` (`CONTENT_ID`, `CONTENT_TYPE`, `USER_ID`, `VOTE`),
  PRIMARY KEY (`ID`)
)
  COLLATE = 'utf8_unicode_ci'
  ENGINE = InnoDB;