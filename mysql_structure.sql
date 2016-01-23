-- SQL Structure for Language Challenge Database
-- https://github.com/language-learners/superchallengebot
--
-- Généré le: Sam 23 Janvier 2016 à 08:51
-- Version du serveur: 10.0.23-MariaDB-cll-lve
-- Version de PHP: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `languagechallenge`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetChallenge`(IN InCode VARCHAR(255))
BEGIN
        SELECT * FROM Challenge
    WHERE Challenge.Code != InCode;
        END$$

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetChallenges`()
BEGIN
        SELECT * FROM Challenge 
        ORDER BY GroupCode, VariationName;
        END$$

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetEntryActions`(IN InEntryId INT, IN InActionCode VARCHAR(255))
BEGIN
        SELECT Time, AmountData, TextData
    FROM Actions
    WHERE EntryId = InEntryId
    AND ActionCode = InActionCode;
        END$$

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetGroupedEntries`(IN TotalBookPages INT, IN TotalFilmMinutes INT)
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

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetLanguages`()
BEGIN
        SELECT Code, Name FROM Language
        ORDER BY Name ASC;
        END$$

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetParticipantDetails`(IN InUserName VARCHAR(255))
BEGIN
        SELECT UserName, DisplayName, Location, 
        ImageUrl, WebsiteUrl, About 
    FROM Participants 
    WHERE Participants.UserName = InUserName;
        END$$

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetParticipantEntries`(IN InUserName VARCHAR(255))
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

CREATE DEFINER=`languagechallenge`@`localhost` PROCEDURE `GetStatistics`()
BEGIN
        SELECT SUM(PagesRead) AS TotalPagesRead, SUM(MinutesWatched) AS TotalMinutesWatched 
            FROM Entries;
        END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Actions`
--

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

CREATE TABLE IF NOT EXISTS `Language` (
  `Code` varchar(2) NOT NULL,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `Participants`
--

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

CREATE TABLE IF NOT EXISTS `Preferences` (
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
