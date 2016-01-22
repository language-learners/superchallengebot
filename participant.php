<?php

require_once('database.php');
require_once('preferences.php');

getParticipantData();

?>

<html>
<head>
    <link rel="shortcut icon" href="favicon.ico">
    <title>Language Super Challenge</title>
    <link rel="stylesheet" type="text/css" href="style/style.css"/>
    <meta charset="utf-8"/>
</head>
<body>
    <script src="script/jquery-1.8.2.min.js"></script>
    <script src="script/raphael-min.js"></script>
    <script src="script/morris.min.js"></script>
    <script src="script/participant.js"></script>
    
    <div id='background'>
        <div class='centered'>
            <div class='left'></div>
            <div class='right'></div>
        </div>
    </div>
    
    <div id='headerback'>
        <div id='header'>
            <?php printHeaderLine(); ?>
            <a href="index.php" class='headerlink' id='backlink'><div class='headericon' id='backicon'></div>back</a>
        </div>
    </div>

    <div id='main'>
    
    <div class='shortdesc'>
        <?php printInfoLine(); ?>
    </div>
    
    <?php printLanguageSections() ?>
        
    </div>

<?php

function getParticipantData()
{
    global $participant;
    $username = safe($_GET['username']);

    // participant information
    $participantdata = callStoredProcedure("GetParticipantDetails('".$username."')");
    $participant = mysqli_fetch_array($participantdata);
}

function printHeaderLine()
{
    global $participant;
    print "<img src=".$participant['ImageUrl']." />".
          $participant['UserName'];
}

function printInfoLine()
{
    global $participant;
    $website = $participant['WebsiteUrl'];
    $username = $participant['UserName'];
    print "@<a href=http://www.twitter.com/$username>$username</a>".
          ($website != "" ? " - <a href=$website>$website</a>" : "")."<br>".
          $participant['About'];
}

function printLanguageSections()
{
    // challenge information
    global $participant;
    $entrydata = callStoredProcedure("GetParticipantEntries('".$participant['UserName']."')");
    while($entry = mysqli_fetch_array($entrydata))
    {
        printLanguageSection($entry);                
    }
}

function printLanguageSection($entry)
{
    global $preferences;
    $books = round($entry['PagesRead'] / $preferences->BOOK_PAGES, 1);
    $films = round($entry['MinutesWatched'] / $preferences->FILM_MINUTES, 1);
    
    $languagename = $entry['LanguageName'];
    $languagecode = $entry['LanguageCode'];
    $entryid = $entry['EntryId'];
    
    // get data for the rows
    $bookactions = getActionData($entryid, "inc_pagesread", $preferences->BOOK_PAGES, "pages", "Unknown");
    $filmactions = getActionData($entryid, "inc_minuteswatched", $preferences->FILM_MINUTES, "minutes", "Unknown");
    
    // calculate and update database with badge data!
    $times = array_merge($bookactions['Times'], $filmactions['Times']); sort($times);
    $sprint = getSprintData($bookactions, $filmactions);
    $streak = getStreakData($times);
    
    if($streak['Longest'] != $entry['LongestStreak'] ||
       $streak['Current'] != $entry['CurrentStreak'] ||
       $sprint != $entry['LongestSprint']) {
        updateEntryBadges($entryid, $sprint, $streak['Longest'], $streak['Current']);
    }

    // semi-html stuff
    $streaktick = $streak['Active'] ? 'tick' : '';
    $streakhtml = ($streak['Current'] > 1 ? "<div class='badge white streak $streaktick' 
        title='Current Streak (number of consecutive weeks)'></div> ".$streak['Current']." weeks" : "");
    
    $sprinthtml = ($sprint > 0 ? "<div class='badge white sprint' 
        title='Activity Badge (recently studied at least one book and one film)'></div> active" : "");
    
    $bookkey = $languagecode."books";
    $bookshtml = "Read $books book".($books==1?"":"s").
            getRateHtml($books).getDisplayButtonsHtml($bookkey);
    $filmkey = $languagecode."films";
    $filmshtml = "Watched $films film".($films==1?"":"s").
            getRateHtml($films).getDisplayButtonsHtml($filmkey);
    
    $booksectionhtml = getSectionHtml($bookactions, $bookkey, 'Total Read');
    $filmsectionhtml = getSectionHtml($filmactions, $filmkey, 'Total Watched');
    
    //$sprinthtml<span class='spacer'></span>
    
    // finally, print everything
    print "
        <div class='panel'>
        <div class='header'>
            $languagename
            <span class='headerlanguagecode'> (#$languagecode)</span>
            <div class='headerbadges'>$streakhtml $sprinthtml</div>
        </div>

       

       <div class='subheader'>
            $bookshtml
        </div>
        <div class='content'>
            $booksectionhtml
        </div>
    
        <div class='subheader'>
            $filmshtml
        </div>
        <div class='content'>
            $filmsectionhtml

       </div>
    
        </div>
    ";
}

function getDisplayButtonsHtml($key)
{
    return "
    <span class='buttons'>
        <a href='javascript:void(0);' onclick=\"setGraphVisibility('$key', true);\">
        <img src='image/graph.png' title='Show Graph'/></a>
        <a href='javascript:void(0);' onclick=\"setGraphVisibility('$key', false);\">
        <img src='image/list.png' title='Show List'/></a>
    </span>";
}

function getSectionHtml($actiondata, $key, $valuetitle)
{
    return "
        <div id='$key"."list' class='hideable list'>".$actiondata['List']."</div>
        <div id='$key"."graph' class='hideable graph'></div>
        <script type='text/javascript'>
            participant.data['$key'] = [" . $actiondata['Graph'] . "]; 
            participant.postUnits['$key'] = [' ".$actiondata['Type']."'];
            participant.labels['$key'] = ['$valuetitle'];
        
            setGraphVisibility('$key', ".($actiondata['Count'] > 3 ? 'true' : 'false').");
        </script>";
}

function getRateHtml($number)
{
    global $preferences;
    $remaining = $preferences->STAR_VALUE - ($number % $preferences->STAR_VALUE);
    return ($remaining <= 10 ? "<span class='ratedata'>(only $remaining to the next star!)</span>" : "");
}

function getActionData($entryid, $actioncode, $defaultamount, $typestring, $defaulttitle = "")
{
    // query the database for matching actions
    $actiondata = callStoredProcedure("GetEntryActions(".$entryid.", '".$actioncode."')");
    
    // for streaks and sprints
    $times = array();
    
    // print them out in a readable format
    $liststring = ""; $graphstring = ""; 
    $totalamount = 0; $count = 0;
    $sprintamount = 0;
    $lasttime = null;
    $currenttime = new DateTime();
    while($action = mysqli_fetch_array($actiondata))
    {
        $count++;
        
        // HTML list stuff
        $datestring = date("d M Y", strtotime($action['Time']));
        $titlestring = ($action['TextData'] ? "<b>".$action['TextData']."</b>" : "<i>".$defaulttitle."</i>");
        // if we don't have a title, always show the amount
        $amountstring = ($action['AmountData'] != $defaultamount || $titlestring == "<i></i>"
                ? " <i>".$action['AmountData']." ".$typestring."</i>" : "");

        $liststring .= "<span class='listdate'>$datestring</span>
                        <span class='listtitle'>$titlestring</span>
                        <span class='listamount'>$amountstring</span><br>";
        
        // streaks and sprints
        array_push($times, $action['Time']);
        
        // JAVASCRIPT graph stuff
        $thistime = strtotime($action['Time']); // if two entries have exactly the same time, bump one up/make it later
        $time = date("c", $lasttime == null || $lasttime != $thistime ? $thistime : ++$thistime);
        $lasttime = $thistime;
        
        $amount = $action['AmountData'];
        $title = $action['TextData'] ? $action['TextData'] : $defaulttitle;

        $graphstring .= "{'time': '$time', 'amount': $amount, 'title': \"$title\"},";
        
        // activity during the last fortnight
        $datetime = new DateTime($action['Time']);
        if($datetime->diff($currenttime)->days <= 14) {
            $sprintamount += $amount;
        }
    }
    $graphstring = substr_replace($graphstring, "", -1);
    
    // redefine action data and set the different parts
    $actiondata = array();
    $actiondata['List'] = $liststring;
    $actiondata['Graph'] = $graphstring;
    $actiondata['Count'] = $count;
    $actiondata['Type'] = $typestring;
    $actiondata['Times'] = $times;
    $actiondata['Sprint'] = $sprintamount;
    return $actiondata;
}

function getSprintData($bookactions, $filmactions)
{
    global $preferences;
    
    // how many books/films have we studied in the last fortnight?
    $sprint = 0;
    $bookunits = $bookactions['Sprint'] / $preferences->BOOK_PAGES;
    $filmunits = $filmactions['Sprint'] / $preferences->FILM_MINUTES;
    
    return min(floor($bookunits), floor($filmunits));
}

function getStreakData($times)
{

   global $preferences;
    
    // streaks and sprints
    $longeststreak = 0;
    $currentstreak = 0;
    $active = false;
    $laststreakelapsed = 0;
    
    // streaks and sprints
    $elapsedweeks = 0;
    foreach($times as $time)
    {
        $datetime = new DateTime($time);
        $elapsedweeks = floor($datetime->diff($preferences->EPOCH)->days / 7);
        $elapseddifference = $elapsedweeks - $laststreakelapsed;
        // we're part of a streak if it's only one week apart
        if($elapseddifference == 1 || $laststreakelapsed == 0)
        {
            $currentstreak++;
            $active = true;
            $laststreakelapsed = $elapsedweeks;
            $longeststreak = max($longeststreak, $currentstreak);
        }
        // reset but only if it's NOT on the same day!
        elseif($elapseddifference != 0)
        {
            $laststreakelapsed = $elapsedweeks;
            $currentstreak = 0;
            $active = false;
        }

    }
    
    // the streak is only current if the last entry was within a week
    $sinceepoch = floor($preferences->EPOCH->diff(new DateTime())->days / 7);
    if($sinceepoch - $elapsedweeks > 0)
    {
        $active = false;
    }
    if($sinceepoch - $elapsedweeks > 1) {
        $currentstreak = 0;
    }
    
    $streak = array();
    $streak['Longest'] = $longeststreak;
    $streak['Current'] = $currentstreak;
    $streak['Active'] = $active; // have we update this week?
    return $streak; 
}

?>