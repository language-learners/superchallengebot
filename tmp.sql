alter table Language modify Code varchar(3);
alter table Entries modify LanguageCode varchar(3);
INSERT INTO Language (Name, Code) VALUES ('Ancient-Egyptian','egy');
INSERT INTO Language (Name, Code) VALUES ('Family-Egyptian','egx');
INSERT INTO Language (Name, Code) VALUES ('Family-Romance','roa');
INSERT INTO Language (Name, Code) VALUES ('Family-Sino-Tibetan','sit');
INSERT INTO Language (Name, Code) VALUES ('Family-Slavic','sla');
INSERT INTO Language (Name, Code) VALUES ('Family-Germanic','gem');
INSERT INTO Language (Name, Code) VALUES ('Family-Finno-Ugrian','fiu');
INSERT INTO Language (Name, Code) VALUES ('Family-Semitic','sem');
INSERT INTO Language (Name, Code) VALUES ('Family-Turkic','trk');
INSERT INTO Language (Name, Code) VALUES ('Family-Greek','grk');
INSERT INTO Language (Name, Code) VALUES ('Family-North-Germanic','gmq');

