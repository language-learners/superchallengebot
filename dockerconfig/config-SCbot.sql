-- Insert Twitter OAuth information and start and end dates of the challenge in the form
-- '2017-12-31 11:59:00'

UPDATE Preferences SET Name='consumer_key', Value= '';
UPDATE Preferences SET Name='consumer_secret_key', Value= '';
UPDATE Preferences SET Name='oauth_token',  Value= '';
UPDATE Preferences SET Name='oauth_secret_token',  Value= '';
UPDATE Preferences SET Name='StartDate',  Value= '';
UPDATE Preferences SET Name='EndDate',  Value= '';

