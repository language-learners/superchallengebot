<?php
require_once("helpers.php");
require_once("configuration.php");
// Do we use the testing database or the release one?

$link = login(DB_NAME.$testing);

function login($databasename)
{

    // Connects to the Database 
    global $testing;

    $link = mysqli_connect($testing ? "server_url" : DB_HOST , DB_USER , DB_PASSWORD , DB_NAME);
        
    if(!$link)
        die(mysqli_connect_error());
    
    return $link;
}

function safe($string)
{
    global $link;
    return mysqli_real_escape_string($link, strip_tags($string));
}

function callStoredProcedure($procedure)
{
    global $link;
    $resultset = mysqli_multi_query($link, "CALL ".$procedure)
        or die(__FILE__.__LINE__.mysqli_error($link).$procedure);
    $data = mysqli_store_result($link);
    
    // clear remaining sets in the resultset before returning
    while (mysqli_more_results($link)) {mysqli_next_result($link);}
    return $data;
}

function setPreference($name, $value) {
    global $link;
    $data = mysqli_query($link, "INSERT INTO Preferences (Name, Value)
        VALUES ('".$name."', '".$value."')
        ON DUPLICATE KEY UPDATE Value = '".$value."'")
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function getPreference($name) {
    global $link;
    $data = mysqli_query($link, "SELECT Value FROM Preferences
        WHERE Name = '".$name."'")
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    $info = mysqli_fetch_array($data);
    return $info['Value'];
}

function getPreferences() {
    global $link;
    $data = mysqli_query($link, "SELECT * FROM Preferences")
		or die(__FILE__.__LINE__.mysqli_error($link));
    
    $preferences = Array();
    while($info = mysqli_fetch_array($data))
        $preferences[$info['Name']] = $info['Value'];

    return $preferences;
}

function getStatistics() {
    global $link;
    $data = mysqli_query($link, "SELECT 
        SUM(PagesRead) AS TotalPagesRead, 
        SUM(MinutesWatched) AS TotalMinutesWatched 
            FROM Entries")
        or die(__FILE__.__LINE__.mysqli_error($link));
        
    return mysqli_fetch_array($data);
}

function getLanguageName($code) {
    global $link;
    $data = mysqli_query($link, "SELECT Name FROM Language
        WHERE Code = '".$code."'")
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    $info = mysqli_fetch_array($data);
    return $info['Name'];
}

function getActionEntryId($actionid) {
    global $link;
    $data = mysqli_query($link, "SELECT EntryId FROM Actions
        WHERE Id = '$actionid'")
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    $info = mysqli_fetch_array($data);
    return $info['EntryId'];
}

function getAction($actionid) {
    global $link;
    $data = mysqli_query($link, "SELECT * FROM Actions
        WHERE Id = '$actionid'")
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    return mysqli_fetch_array($data);
}

function insertParticipant($username, $displayname, $feedcode = "none", $feeddata = "") 
{
    // insert or update
    global $link;
    $data = mysqli_query($link, 
        "INSERT INTO Participants (UserName, DisplayName, FeedData) 
        VALUES ('".safe($username).
            "', '".safe($displayname).
            "', '".safe($feeddata)."') 
                ON DUPLICATE KEY UPDATE UserName=UserName")
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function updateParticipant($username, $displayname, $location, $imageurl, $websiteurl, $about)
{
    global $link;
    mysqli_query($link, "UPDATE Participants 
        SET DisplayName='".safe($displayname)."',
            Location='".safe($location)."',
            ImageUrl='".safe($imageurl)."',
            WebsiteUrl='".safe($websiteurl)."',
            About='".safe($about)."'
        WHERE UserName='".$username."'")
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function insertEntry($username, $languagecode)
{
    global $link;
    
    // Check for double entries
    $query = "SELECT UserName, LanguageCode FROM Entries
        WHERE UserName='".safe($username).
        "' AND LanguageCode='".safe($languagecode)."'";
    $result = mysqli_query($link, $query) or die(__FILE__.__LINE__.mysqli_error($link));

    if (mysqli_num_rows($result) )
    {
        return false;
    }
    else
    {
        // Insert a new entry
        $query = mysqli_query($link, "INSERT INTO Entries 
            (UserName, LanguageCode) 
            VALUES ('".safe($username).
                "', '".safe($languagecode)."')")
        or die(__FILE__.__LINE__.mysqli_error($link));
        return true;
    }
}

function removeEntry($id)
{
    // Delete entry
    global $link;
    $data = mysqli_query($link, "DELETE FROM Entries 
        WHERE Id=".$id)
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function incrementPagesRead($actionid, $entryid, $pages, $title = "")
{
    insertActionRecord($actionid, 'inc_pagesread', $entryid, $pages, $title);
    return incrementEntryRecord($entryid, 'PagesRead', $pages);
}

function incrementMinutesWatched($actionid, $entryid, $minutes, $title = "")
{
    insertActionRecord($actionid, 'inc_minuteswatched', $entryid, $minutes, $title);
    return incrementEntryRecord($entryid, 'MinutesWatched', $minutes);;
}

// increments the entry record and returns the new total
function incrementEntryRecord($id, $fieldname, $value)
{
    // update
    global $link;
    $data = mysqli_query($link, "UPDATE Entries
        SET ".$fieldname."=".$fieldname."+".$value.
        " WHERE Id=".$id)
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    // return the new value
    $result = mysqli_query($link, "SELECT ".$fieldname.
            " FROM Entries WHERE Id=".$id)
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    $info = mysqli_fetch_array($result);
    return $info[$fieldname];
}

function updateEntryBadges($id, $longestsprint, $longeststreak, $currentstreak)
{
    // update
    global $link;
    $data = mysqli_query($link, "UPDATE Entries
        SET LongestSprint='$longestsprint', LongestStreak='$longeststreak', CurrentStreak='$currentstreak'
        WHERE Id=$id")
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function insertActionRecord($actionid, $actioncode, $entryid, $amount, $data = "", $time = "NOW()")
{
    global $link;
    if($time != "NOW()") {$time = "'$time'";}
    $data = mysqli_query($link, "INSERT INTO Actions 
        (Id, EntryId, ActionCode, Time, AmountData, TextData) 
        VALUES ('$actionid', $entryid, '$actioncode', $time, ".
        safe($amount).", '".safe($data)."')")
    or die(__FILE__.__LINE__.mysqli_error($link));
}

function getActionCode($actionid)
{
    global $link;
    $data = mysqli_query($link, "SELECT ActionCode FROM Actions
        WHERE Id = '$actionid'")
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    // no data
    if(mysqli_num_rows($data) < 1)
        return "";
    
    // the code
    $info = mysqli_fetch_array($data);
    return $info['ActionCode'];
}

function updateAction($actionid, $newprefix)
{
    // we must at least have something!
    if(!$actionid)
        return false;
            
    global $link;
    $result = mysqli_query($link, "UPDATE Actions
        SET ActionCode=CONCAT('$newprefix"."_', SUBSTR(ActionCode, 5))
        WHERE id='$actionid'") //LEFT(ActionCode, 4)='inc_' AND 
    or die(__FILE__.__LINE__.mysqli_error($link));

    // we should always affect 1 row
    return (mysqli_affected_rows($link) == 1);
}

function getUniqueEntry($username, $languagecode = "")
{
    // only filter by language if one is provided
    global $link;
    $result = mysqli_query($link, "SELECT Id FROM Entries
        WHERE UserName = '".safe($username)."' ".
        ($languagecode == "" ? "" : 
            "AND LanguageCode = '".safe($languagecode)."'"))
    or die(__FILE__.__LINE__.mysqli_error($link));
    
    // no data, or too much data
    if(mysqli_num_rows($result) < 1)
        return -1;
    else if(mysqli_num_rows($result) > 1)
        return -2;
    
    // otherwise, the id as promised
    $info = mysqli_fetch_array($result);   
    return $info;
}

function getUpdateNames($count = 100)
{
    // where did we start off?
    $lastindex = getPreference("last_userupdate_index");
    
    // return those rows
    global $link;
    /* echo "SELECT UserName FROM Participants LIMIT ".$lastindex." ".$count ; */
    /* $namesresult = mysqli_query($link, "SELECT UserName FROM Participants  */
    /*     LIMIT ".$lastindex.", ".$count) */
    /*         or die(__FILE__.__LINE__.mysqli_error($link)); */
    echo "SELECT UserName FROM Participants LIMIT ".$lastindex." ".$count ;
    $namesresult = mysqli_query($link, "SELECT UserName FROM Participants 
        LIMIT ".$lastindex.", ".$count)
            or die(__FILE__.__LINE__.mysqli_error($link));

    $namearray = array();
    while($namerow = mysqli_fetch_assoc($namesresult))
        $namearray[] = $namerow['UserName'];

    // how many rows, if we need to wrap
    $countresult = mysqli_query($link, "SELECT COUNT(*) FROM Participants")
            or die(__FILE__.__LINE__.mysqli_error($link));
    $totalcount = mysqli_fetch_array($countresult);
    $lastindex += $count;
    if($lastindex >= $totalcount[0]) 
        $lastindex = 0;
    
    // Save the last updated index (wrapping if necessary)
    setPreference("last_userupdate_index", $lastindex);

    return $namearray;
}

function findLanguageInString($string)
{
    // get a list of options
    global $preferences;
    global $link;
    $columnnames = array("Code", "Name");
    $data = mysqli_query($link, "SELECT ".implode(", ", $columnnames)." FROM Language")
    or die(__FILE__.__LINE__.mysqli_error($link));

    // check against all languages in the database
    while($info = mysqli_fetch_array($data))
    {
        // check against all specified columns
        foreach($columnnames as $columnname)
        {
            $result = findHashtagInString(strtolower($info[$columnname]), $string);
            if($result !== false) 
                return $info;
        }
    }
    
    // return it
    return null;
}

?>
