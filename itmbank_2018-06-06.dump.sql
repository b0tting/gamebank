CREATE TABLE accounts (
  "id" INTEGER PRIMARY KEY,
  "ownerid" INTEGER,
  "number" TEXT,
  "balance" REAL,
  "lastvisit" DATETIME,
  "lastvisitip" TEXT,
  "created" DEFAULT CURRENT_TIMESTAMP NOT NULL
);
INSERT INTO "accounts" ("id","ownerid","number","balance","lastvisit","lastvisitip","created") VALUES ('1','1','NL04ABNA02342680543','470.0','10-06-2018 10:52','83.128.36.66','2018-05-21 20:12:13');
INSERT INTO "accounts" ("id","ownerid","number","balance","lastvisit","lastvisitip","created") VALUES ('2','2','NL02BUNQ0524377065','855900.25','07-06-2018 05:41','83.83.160.181','2018-05-22 18:33:02');
INSERT INTO "accounts" ("id","ownerid","number","balance","lastvisit","lastvisitip","created") VALUES ('3','3','NL05ASNB0352802342','984235.89',NULL,'','2018-05-22 18:35:42');
INSERT INTO "accounts" ("id","ownerid","number","balance","lastvisit","lastvisitip","created") VALUES ('4','4','NL02ABNA0255325673','1126748.66',NULL,'','2018-05-22 18:36:56');
CREATE TABLE owners (
  "id" INTEGER PRIMARY KEY,
  "displayname" TEXT,
  "password" TEXT,
  "lastvisit" DATETIME,
  "lastvisitip" TEXT,
  "created" DEFAULT CURRENT_TIMESTAMP NOT NULL
);
INSERT INTO "owners" ("id","displayname","password","lastvisit","lastvisitip","created") VALUES ('1','De bende uit Amsterdam','Molentjes',NULL,'','2018-05-22 18:25:57');
INSERT INTO "owners" ("id","displayname","password","lastvisit","lastvisitip","created") VALUES ('2','De bende uit Almelo','Grolsch',NULL,'','2018-05-22 18:26:54');
INSERT INTO "owners" ("id","displayname","password","lastvisit","lastvisitip","created") VALUES ('3','De bende uit Maaskantje','Quukske',NULL,'','2018-05-22 18:27:30');
INSERT INTO "owners" ("id","displayname","password","lastvisit","lastvisitip","created") VALUES ('4','De bende uit Maastricht','Sjoes',NULL,'','2018-05-22 18:28:16');
CREATE TABLE questions (
  "id" INTEGER PRIMARY KEY,
  "account_id" INTEGER,
  "question" TEXT,
  "answer" TEXT
);
INSERT INTO "questions" ("id","account_id","question","answer") VALUES ('1','1','Wat is de kleur van de auto van de baas van Maaskantje','Oranje');
INSERT INTO "questions" ("id","account_id","question","answer") VALUES ('2','1','Waar komt de dochter van de boekhouder vandaan?','Lutjebroek');
INSERT INTO "questions" ("id","account_id","question","answer") VALUES ('3','2','Hoeveel ringen had Sauron?','1');
CREATE TABLE transactions (
  "id" INTEGER PRIMARY KEY,
  "account_id_from" INTEGER,
  "account_id_to" INTEGER,
  "amount" REAL,
  "date" DATETIME,
  "description" TEXT,
  "is_player_transaction" BOOLEAN
);
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('1','1','2','10000.0','2018-05-06 10:00:00','Digitale slotdecoder',NULL);
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('2','2','1','5.0','2018-06-04 21:51:17','12345noemoe','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('3','2','1','5.0','2018-06-04 21:53:35','1244444','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('4','2','1','5.0','2018-06-04 21:54:52','123123','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('5','2','1','5.0','2018-06-04 21:59:58','123 123','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('6','2','1','5.0','2018-06-04 22:00:15','123 grekraatk','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('7','2','1','111.0','2018-04-29 21:53:35','Datum test','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('8','1','3','10.0','2018-06-07 05:47:43','testen','1');
INSERT INTO "transactions" ("id","account_id_from","account_id_to","amount","date","description","is_player_transaction") VALUES ('9','1','3','15.0','2018-06-07 05:52:04','test in de kop','1');
CREATE TABLE 'lockouts' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'ip' TEXT, 'until' INTEGER, 'lastfault' DATETIME, 'faulttimes' INTEGER);
INSERT INTO "lockouts" ("id","ip","until","lastfault","faulttimes") VALUES ('3','83.83.160.181','1528145945','1528351213','1');
INSERT INTO "lockouts" ("id","ip","until","lastfault","faulttimes") VALUES ('4','83.128.36.66','1528191868','1528191268','4');
INSERT INTO "lockouts" ("id","ip","until","lastfault","faulttimes") VALUES ('5','145.5.125.54',NULL,'1528297517','1');
CREATE VIEW owneraccounts AS
  SELECT a.*,
    o.password,
    o.displayname
  FROM accounts a left join owners o
      on a.ownerid = o.id;
CREATE VIEW accounttransactions AS
  SELECT t.*,
    f.number as account_from,
    ta.number as account_to,
    oto.displayname as account_to_owner,
    ofrom.displayname as account_from_owner
  FROM transactions t left join accounts f
      on t.account_id_from = f.id
    left join accounts ta
      on t.account_id_to = ta.id
    left join owners oto
      on t.account_id_to = oto.id
    left join owners ofrom
      on t.account_id_from = ofrom.id;