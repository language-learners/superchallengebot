-- SQL Structure for Language Challenge Database
-- https://github.com/language-learners/superchallengebot
--
-- Généré le: Sam 23 Janvier 2016 à 08:51
-- Version du serveur: 10.0.23-MariaDB-cll-lve
-- Version de PHP: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Use these commands to create the DB/
-- 
-- DROP DATABASE languagechallenge;
-- CREATE DATABASE languagechallenge;
use languagechallenge;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `languagechallenge`

--
-- Structure de la table `Actions`
--
DROP TABLE `Actions`;
CREATE TABLE IF NOT EXISTS `Actions` (
  `Id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `EntryId` int(10) unsigned NOT NULL,
  `ActionCode` varchar(255) NOT NULL,
  `Time` datetime DEFAULT NULL,
  `AmountData` int(11) DEFAULT '0',
  `TextData` text,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=690287028508975105 ;

-- --------------------------------------------------------

--
-- Structure de la table `Entries`
--
DROP TABLE `Entries`;
CREATE TABLE IF NOT EXISTS `Entries` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varchar(255) NOT NULL,
  `LanguageCode` varchar(2) NOT NULL,
  `PagesRead` int(10) unsigned DEFAULT '0',
  `MinutesWatched` int(10) unsigned DEFAULT '0',
  `LongestStreak` int(10) unsigned DEFAULT '0',
  `CurrentStreak` int(10) unsigned NOT NULL DEFAULT '0',
  `LongestSprint` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=421 ;

-- --------------------------------------------------------

--
-- Structure de la table `Language`
--

DROP TABLE `Language`;
CREATE TABLE IF NOT EXISTS `Language` (
  `Code` varchar(2) NOT NULL,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Participants`
--
DROP TABLE `Participants`;
CREATE TABLE IF NOT EXISTS `Participants` (
  `UserName` varchar(255) NOT NULL,
  `DisplayName` varchar(255) NOT NULL,
  `FeedData` varchar(255) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `ImageUrl` varchar(255) DEFAULT NULL,
  `WebsiteUrl` varchar(255) DEFAULT NULL,
  `About` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UserName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Preferences`
--

DROP TABLE `Preferences`;
CREATE TABLE IF NOT EXISTS `Preferences` (
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--

DELIMITER $$
--
-- Procédures
--
DROP PROCEDURE `GetChallenge`$$
CREATE PROCEDURE `GetChallenge`(IN InCode VARCHAR(255))
BEGIN
        SELECT * FROM Challenge
    WHERE Challenge.Code != InCode;
        END$$

DROP PROCEDURE `GetChallenges`$$
CREATE PROCEDURE `GetChallenges`()
BEGIN
        SELECT * FROM Challenge 
        ORDER BY GroupCode, VariationName;
        END$$

DROP PROCEDURE `GetEntryActions`$$
CREATE PROCEDURE `GetEntryActions`(IN InEntryId INT, IN InActionCode VARCHAR(255))
BEGIN
        SELECT Time, AmountData, TextData
    FROM Actions
    WHERE EntryId = InEntryId
    AND ActionCode = InActionCode;
        END$$

DROP PROCEDURE `GetGroupedEntries`$$
CREATE PROCEDURE `GetGroupedEntries`(IN TotalBookPages INT, IN TotalFilmMinutes INT)
BEGIN
        SELECT Participants.DisplayName AS DisplayName, 
            Language.Name AS LanguageName, Entries.MinutesWatched AS MinutesWatched, Entries.PagesRead AS PagesRead,
            Entries.LongestStreak AS LongestStreak, Entries.CurrentStreak AS CurrentStreak,
            Entries.LongestSprint AS LongestSprint,
            EntryGroups.UserName AS UserName, EntryGroups.TotalUnits AS TotalUnits
        FROM Participants, Entries, Language, 
           (SELECT Entries.UserName AS UserName,
               MAX(PagesRead/TotalBookPages+MinutesWatched/TotalFilmMinutes) AS TotalUnits
            FROM Entries
            GROUP BY UserName DESC ) AS EntryGroups
        WHERE Entries.LanguageCode = Language.Code
        AND Entries.UserName = Participants.UserName
        AND Entries.UserName = EntryGroups.UserName
        ORDER BY TotalUnits DESC, UserName DESC,
             (PagesRead/TotalBookPages+MinutesWatched/TotalFilmMinutes) DESC;
        END$$

DROP PROCEDURE `GetLanguages`$$
CREATE PROCEDURE `GetLanguages`()
BEGIN
        SELECT Code, Name FROM Language
        ORDER BY Name ASC;
        END$$

DROP PROCEDURE `GetParticipantDetails`$$
CREATE PROCEDURE `GetParticipantDetails`(IN InUserName VARCHAR(255))
BEGIN
        SELECT UserName, DisplayName, Location, 
        ImageUrl, WebsiteUrl, About 
    FROM Participants 
    WHERE Participants.UserName = InUserName;
        END$$

DROP PROCEDURE `GetParticipantEntries`$$
CREATE PROCEDURE `GetParticipantEntries`(IN InUserName VARCHAR(255))
BEGIN
        SELECT Language.Name AS LanguageName, Language.Code as LanguageCode,
        Entries.Id AS EntryId,
        Entries.MinutesWatched AS MinutesWatched, Entries.PagesRead AS PagesRead,
        Entries.LongestStreak AS LongestStreak, Entries.CurrentStreak AS CurrentStreak,
        Entries.LongestSprint AS LongestSprint
    FROM Language, Entries
    WHERE Entries.LanguageCode = Language.Code
        AND Entries.UserName = InUserName;
        END$$

DROP PROCEDURE `GetStatistics`$$
CREATE PROCEDURE `GetStatistics`()
BEGIN
        SELECT SUM(PagesRead) AS TotalPagesRead, SUM(MinutesWatched) AS TotalMinutesWatched 
            FROM Entries;
        END$$

DELIMITER ;

-- --------------------------------------------------------


-- ----------- INSERT DUMMY DATA -------------------


INSERT INTO Participants VALUES (
'dummy','dummy','feed_data','UK',
'https://abs.twimg.com/sticky/default_profile_images/default_profile_6_normal.png',
'http://dummy.user.com','about me');


INSERT INTO Entries(UserName,LanguageCode,PagesRead,MinutesWatched,LongestSprint,LongestStreak,CurrentStreak) 
VALUES ('dummy','af', 0,0,0,0,0 );

INSERT INTO Entries(UserName,LanguageCode,PagesRead,MinutesWatched,LongestSprint,LongestStreak,CurrentStreak) 
VALUES ('dummy','fr', 0,0,0,0,0 );


-- --------------- INSERT STATIC VALUES -------------------------------

--
-- OAuth information has to be obtained from Twitter developer site
--
INSERT INTO Preferences (Name, Value) VALUES ('consumer_key', '');
INSERT INTO Preferences (Name, Value) VALUES ('consumer_secret_key', '');
INSERT INTO Preferences (Name, Value) VALUES ('oauth_token', '');
INSERT INTO Preferences (Name, Value) VALUES ('oauth_secret_token', '');

INSERT INTO Preferences (Name, Value) VALUES ('last_update',            0);
INSERT INTO Preferences (Name, Value) VALUES ('last_twitter_id',        0);
INSERT INTO Preferences (Name, Value) VALUES ('book_pages',            50);
INSERT INTO Preferences (Name, Value) VALUES ('film_minutes',          90);
INSERT INTO Preferences (Name, Value) VALUES ('last_userupdate_index', 200);




INSERT INTO Preferences (Name, Value) VALUES ('book_pages', '100');
INSERT INTO Preferences (Name, Value) VALUES ('film_minutes', '100');

INSERT INTO Language (Code, Name) VALUES ('af','Afrikaans ');
INSERT INTO Language (Code, Name) VALUES ('sq','Albanian');
INSERT INTO Language (Code, Name) VALUES ('gr','Ancient Greek');
,'feed_data','UK',
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

