<?php

// Preferences
require_once('preferences.php');
require_once('database.php');

function showUpdateTime()
{
    // When we last updated
    $lastupdate = getPreference("last_update");
    print "<p>Data current at ".date("r", $lastupdate)."<br>
        <a href='#' onclick='resetPreferences();'>Clear Local Data</a></p>";
}

function printDaysLeft()
{
    $difference = mktime(0, 0, 0, 01, 01, 2016) - time();
    if ($difference < 0) { $difference = 0; }
    $daysleft = floor($difference/60/60/24);
    
    print $daysleft;
}

function hideGetStarted()
{
    // first check if we should show it!
    if(isset($_COOKIE["langchallenge_hidegetstarted"])) {
        print "hide";
    }
    
    // show it!
}

$userstartype = array();
function printParticipants()
{
    global $preferences;
    global $userstartype; 

    // statistics
    $statistics = getStatistics();
    
    // return all participants, languages, and challenges
    $data = callStoredProcedure("GetGroupedEntries(".
        $statistics['TotalPagesRead'].",".
        $statistics['TotalMinutesWatched'].")");

    
    $lastusername = ""; $index = 0; $subindex = 0;
    $rowcount = mysqli_num_rows($data); $rowindex = 0;
    $infos = array();
    while($info = mysqli_fetch_array($data)) {
        array_push($infos, $info);
    }
    
    // first set up needed variables etc
    foreach($infos as &$info)
    {        
        // other stuff
        $username = $info['UserName'];
        $displayname = $info['DisplayName'];

        $minuteswatched = $info['MinutesWatched'];
        $pagesread = $info['PagesRead'];

        $books = round($pagesread / $preferences->BOOK_PAGES);
        $films = round($minuteswatched / $preferences->FILM_MINUTES);
        
        // figure out sort order
        $rowindex++;
        if($username != $lastusername)
        {
            $subindex = 0;
            $index++;
            $lastusername = $username;
        }
        $subindex+=0.01;
        
        // stars, numbers, or blank?
        $info['FilmEntry'] = getTableEntry($films, $preferences->TARGET_FILMS, "films");
        $info['BookEntry'] = getTableEntry($books, $preferences->TARGET_BOOKS, "books");
        
        // calculate the star type across a number of entries
        //$userstartype[$username]->type = 0;
        
        // participant formatting
        $info['UserString'] = "<span class='username'><a href=participant.php?username=".$username.">"
                      .$displayname."</a></span><br> <span class='twitter'>@<a href=http://twitter.com/".$username.">".$username."</a></span>";
        $info['CustomKey'] = " sorttable_customkey='".($info['TotalUnits']-$index-$subindex)."'";
        
        // unset the reference
        unset($info);
    }
    
    // print the headers
    print "<table class='sortable' id='participantstable'><tr class='merge'>
                <th id='thparticipant'>Participant</th>
                <th id='thstudying'>Studying</th>
                <th id='thwatched'>Watched</th>
                <th id='thread'>Read</th>
                <th id='thprogress'></th></tr>";
    
    // and the table in random order!
    shuffle($infos);
    foreach($infos as $info) {
        $filmentry = $info['FilmEntry']; $bookentry = $info['BookEntry'];
        //$startype = $startypes[$userstartype[$info['UserName']]->type];

        //<div class='star $startype'></div>
        $filmstars = floor($filmentry->count / 25);
        $bookstars = floor($bookentry->count / 25);
        
        $stars = "<div class='star none'></div>";
        $rank = $filmentry->count + $bookentry->count;
        $bluecount = 0;
        $newstars = getStarsHtml($filmstars, $bookstars, true, $rank, $bluecount) .
                    getStarsHtml($bookstars, $filmstars, false, $rank, $bluecount);
        if($newstars != "") {
            $stars = "<div class='star filler'></div>$newstars";
        }
       
        $sprint = $info['LongestSprint'];
        $streak = $info['CurrentStreak'];
        
        $badges = getStreakDiv($streak, 5, 10, 20);
        $badges .= getSprintDiv($sprint);
        
        print "<tr>
            <td class='show'".$info['CustomKey'].">"
            .$info['UserString']."</td>
            <td class=''>".$info['LanguageName']."</td>
            <td sorttable_customkey='$filmentry->count'>$filmentry->content</td>
            <td sorttable_customkey='$bookentry->count'>$bookentry->content</td>
            <td sorttable_customkey='$rank' class='stars'>$stars$badges</td></tr>";
    }
    
    // the footer
    print "</table>";
}

/*
 * A little complicated, but basically get a list of all stars for this type,
 * either including or excluding the shared stars
 */
function getStarsHtml($count, $comparecount, $combine, &$rank, &$bluecount)
{
    $stars = "";
    for($i = 0; $i < $count; ++$i)
    {
        $startype = 'blue';
        $localrank = 100;
        if($i == 1 && $comparecount > 1) {$startype = 'bronze'; $stars = ""; $localrank = 1000;}
        if($i == 3 && $comparecount > 3) {$startype = 'silver'; $stars = ""; $localrank = 2000;}
        if($i == 7 && $comparecount > 7) {$startype = 'gold'; $stars = ""; $localrank = 3000;}
        if($i == 15 && $comparecount > 15) {$startype = 'white'; $stars = ""; $localrank = 4000;}
        if($startype == 'blue') {$bluecount ++; }
        
        if($combine || (!$combine && $startype == 'blue')) 
        {
            if(($startype == 'blue' && $bluecount <= 6) ||
                $startype != 'blue')
            {
                $stars .= "<div class='star $startype'></div>";
            }
            $rank += $localrank;
        }
    }
    return $stars;
}

function getStreakDiv($count, $bronzelevel, $silverlevel, $goldlevel)
{
    $type = "";
    if($count >= $bronzelevel) {$type = 'bronze';}
    if($count >= $silverlevel) {$type = 'silver';}
    if($count >= $goldlevel) {$type = 'gold';}
    
    return $type == "" ? "" : "<div class='badge $type streak' 
        title='Current Streak : $count consecutive weeks'></div>";
}

function getSprintDiv($count)
{
    $type = "";
    if($count >= 5) {$type = 'gold';}
    else if($count >= 2) {$type = 'silver';}
    else if($count >= 1) {$type = 'bronze';}
    
    $s = $count == 1 ? "" : "s";
    return $type == "" ? "" : "<div class='badge $type sprint' 
        title='Activity Badge : studied $count whole book$s and film$s in the last fortnight'></div>";
}

function getTableEntry($count, $target, $tail)
{
    $entry = new stdClass();
    $entry->count = $count;
    $entry->target = $target;
    $entry->content = "$count $tail";
    return $entry;
}

function printLanguages()
{
    $count = 0;
    $languagedata = callStoredProcedure("GetLanguages()");    
    while($info = mysqli_fetch_array($languagedata))
    {
        $code = $info['Code'];
        $name = $info['Name'];
        print "<span class='languagecode'>".$code."</span>".$name."<br>";
        
        // show the first three and then hide the rest
        if($count++ == 2) {
            print "<div class='hideable' id='languages'>";
        }
    }
    print "</div>";
}

?>
