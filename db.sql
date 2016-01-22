create database challenge;

CREATE TABLE IF NOT EXISTS Preferences 
(
	Name VARCHAR(200),
	Value VARCHAR(200)
);

CREATE TABLE IF NOT EXISTS Entries
(
	UserName VARCHAR(200),
	LanguageCode VARCHAR(45), 
	PagesRead INT,
	MinutesWatched INT,
	LongestSprint INT,
	LongestStreak INT,
	CurrentStreak INT
);

CREATE TABLE IF NOT EXISTS Language
(
	Code VARCHAR(45),
	Name VARCHAR(200)
);

CREATE TABLE IF NOT EXISTS Actions
(
	EntryId INT(11) NOT NULL AUTO_INCREMENT,
	Id VARCHAR(200),
	ActionCode VARCHAR(200),
	Time TIMESTAMP,
	AmountData INT, 
	TextData INT
);

CREATE TABLE IF NOT EXISTS Participants
(
	UserName VARCHAR(200),
	DisplayName VARCHAR(200),
	FeedData VARCHAR(200),
	Location VARCHAR(200),
	ImageUrl VARCHAR(200),
	WebsiteUrl VARCHAR(200),
	About VARCHAR(200)
);

INSERT INTO Preferences (Name, Value) VALUES ('book_pages', '100');
INSERT INTO Preferences (Name, Value) VALUES ('film_minutes', '100');

-- INSERT INTO Preferences (Name, Value) VALUES ('consumer_key', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('consumer_secret_key', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('oauth_token', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('oauth_secret_token', '');

DELIMITER //
CREATE PROCEDURE GetParticipantDetails(IN username VARCHAR(255))
   BEGIN
   SELECT * FROM Participants
   WHERE UserName = username;
   END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE GetParticipantEntries(IN username VARCHAR(255))
   BEGIN
   SELECT * FROM Entries
   WHERE UserName = username;
   END //
DELIMITER ;


-- These look like the most complex procedures, I'm fairly sure they should be a calculation.
-- Don't yet understand what is being returned. So placeholder below. 


-- DELIMITER //
-- CREATE PROCEDURE GetGroupedEntries(
--        IN username VARCHAR(255),
--        IN books INT,
--        IN FILMS INT
--        )
--    BEGIN
--    SELECT * FROM Entries
--    WHERE UserName = username;
--    END //
-- DELIMITER ;


-- DELIMITER //
-- CREATE PROCEDURE GetEntryActions(
--        IN username VARCHAR(255),
--        IN actionitem VARCHAR(255)
--        )
--    BEGIN
--    SELECT * FROM Entries
--    WHERE UserName = username;
--    END //
-- DELIMITER ;



