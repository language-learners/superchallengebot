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
	EntryId INT(11) PRIMARY KEY AUTO_INCREMENT,
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


DELIMITER //
CREATE PROCEDURE GetLanguages()
   BEGIN
   SELECT * FROM Language;
   END //
DELIMITER ;


-- These look like the most complex procedures, I'm fairly sure they should be a calculation.
-- Don't yet understand what is being returned. So placeholder below. 

DROP PROCEDURE GetGroupedEntries;
DELIMITER //
CREATE PROCEDURE GetGroupedEntries(
       IN books INT,
       IN films INT)
   BEGIN
   SELECT t1.UserName, t1.LanguageCode, t1.PagesRead, t1.MinutesWatched, 
   	  t1.LongestSprint, t1.LongestStreak, t1.CurrentStreak, t2.UserName, t2.DisplayName, 
	  t2.FeedData, t2.Location, t2.ImageUrl, t2.WebsiteUrl, t2.About, 
	  t3.Code, t3.Name as LanguageName, 
	  SUM(t1.PagesRead) as TotalUnits
   FROM Entries t1
   JOIN
   Participants t2 ON t1.UserName = t2.UserName
   JOIN
   Language t3 ON t1.LanguageCode = t3.Code ;
   END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE GetEntryActions(
       IN username VARCHAR(255),
       IN actionitem VARCHAR(255)
       )
   BEGIN
   SELECT * FROM Entries
   WHERE UserName = username;
   END //
DELIMITER ;

------------- INSERT DUMMY DATA -------------------


INSERT INTO Participants VALUES (
'dummy','dummy','feed_data','UK',
'https://abs.twimg.com/sticky/default_profile_images/default_profile_6_normal.png',
'http://dummy.user.com','about me');


INSERT INTO Entries(UserName,LanguageCode,PagesRead,MinutesWatched,LongestSprint,LongestStreak,CurrentStreak) 
VALUES ('dummy','af', 0,0,0,0,0 );

INSERT INTO Entries(UserName,LanguageCode,PagesRead,MinutesWatched,LongestSprint,LongestStreak,CurrentStreak) 
VALUES ('dummy','fr', 0,0,0,0,0 );



----------------- INSERT STATIC VALUES -------------------------------


-- INSERT INTO Preferences (Name, Value) VALUES ('consumer_key', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('consumer_secret_key', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('oauth_token', '');
-- INSERT INTO Preferences (Name, Value) VALUES ('oauth_secret_token', '');

INSERT INTO Preferences (Name, Value) VALUES ('book_pages', '100');
INSERT INTO Preferences (Name, Value) VALUES ('film_minutes', '100');



INSERT INTO Language (Code, Name) VALUES ('af','Afrikaans ');
INSERT INTO Language (Code, Name) VALUES ('sq','Albanian');
INSERT INTO Language (Code, Name) VALUES ('gr','Ancient Greek');
INSERT INTO Language (Code, Name) VALUES ('ar','Arabic');
INSERT INTO Language (Code, Name) VALUES ('am','Aramaic');
INSERT INTO Language (Code, Name) VALUES ('be','Belarusian');
INSERT INTO Language (Code, Name) VALUES ('bg','Bulgarian');
INSERT INTO Language (Code, Name) VALUES ('yu','Cantonese');
INSERT INTO Language (Code, Name) VALUES ('zh','Chinese');
INSERT INTO Language (Code, Name) VALUES ('hr','Croatian');
INSERT INTO Language (Code, Name) VALUES ('cs','Czech');
INSERT INTO Language (Code, Name) VALUES ('da','Danish');
INSERT INTO Language (Code, Name) VALUES ('nl','Dutch');
INSERT INTO Language (Code, Name) VALUES ('en','English');
INSERT INTO Language (Code, Name) VALUES ('eo','Esperanto');
INSERT INTO Language (Code, Name) VALUES ('fi','Finnish');
INSERT INTO Language (Code, Name) VALUES ('fr','French');
INSERT INTO Language (Code, Name) VALUES ('de','German');
INSERT INTO Language (Code, Name) VALUES ('el','Greek');
INSERT INTO Language (Code, Name) VALUES ('he','Hebrew');
INSERT INTO Language (Code, Name) VALUES ('hi','Hindi');
INSERT INTO Language (Code, Name) VALUES ('hu','Hungarian');
INSERT INTO Language (Code, Name) VALUES ('is','Icelandic');
INSERT INTO Language (Code, Name) VALUES ('in','Indonesian');
INSERT INTO Language (Code, Name) VALUES ('ga','Irish');
INSERT INTO Language (Code, Name) VALUES ('it','Italian');
INSERT INTO Language (Code, Name) VALUES ('ja','Japanese');
INSERT INTO Language (Code, Name) VALUES ('ko','Korean');
INSERT INTO Language (Code, Name) VALUES ('lt','Lithuanian');
INSERT INTO Language (Code, Name) VALUES ('mi','Maori');
INSERT INTO Language (Code, Name) VALUES ('no','Norwegian');
INSERT INTO Language (Code, Name) VALUES ('fa','Persian');
INSERT INTO Language (Code, Name) VALUES ('ph','Phoenician');
INSERT INTO Language (Code, Name) VALUES ('pl','Polish');
INSERT INTO Language (Code, Name) VALUES ('pt','Portuguese');
INSERT INTO Language (Code, Name) VALUES ('pa','Punjabi');
INSERT INTO Language (Code, Name) VALUES ('ro','Romanian');
INSERT INTO Language (Code, Name) VALUES ('ru','Russian');
INSERT INTO Language (Code, Name) VALUES ('es','Spanish');
INSERT INTO Language (Code, Name) VALUES ('sv','Swedish');
INSERT INTO Language (Code, Name) VALUES ('ta','Tamil');
INSERT INTO Language (Code, Name) VALUES ('th','Thai');
INSERT INTO Language (Code, Name) VALUES ('tr','Turkish');
INSERT INTO Language (Code, Name) VALUES ('uk','Ukrainian');
INSERT INTO Language (Code, Name) VALUES ('ur','Urdu');
INSERT INTO Language (Code, Name) VALUES ('vi','Vietnamese');
INSERT INTO Language (Code, Name) VALUES ('cy','Welsh');

