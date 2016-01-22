<?php
require_once('database.php');
require_once('preferences.php');
require_once('helpers.php');

function update()
{
    // Only update every 5 minutes (if the last update was too new, return)
    global $testing;
    $lastupdate = getPreference("last_update");
    if(!$testing && $lastupdate > strtotime('1 minute ago'))
        {loginfo("update() called but not run"); return;}
        
    // Check local twitter feeds (langchallenge@twitter) for all participants
    $success = updateTwitterFeed($lastupdate);
        
    // Done!
    setPreference("last_update", time());
}

$twit = null;
function updateTwitterFeed($lastupdate)
{
    // Twitter!
    global $preferences; global $twit;
    require_once('oauth/twitteroauth.php');
    $twit = new TwitterOAuth($preferences->CONSUMER_KEY, $preferences->CONSUMER_SECRET_KEY, 
                             $preferences->OAUTH_TOKEN, $preferences->OAUTH_SECRET_TOKEN);

    // Update the following/followed balance
    updateTwitterFollowing();
    
    // Update user information for those who require it
    updateTwitterUsers();
    
    // Get mentions since the last update
    $args = Array();
    $lastreadid = getPreference("last_twitter_id");
    if($lastreadid) $args['since_id'] = $lastreadid;
    $tweets = $twit->get('statuses/mentions_timeline', $args);
    
    // if we have no tweets, stop here
    if(!is_array($tweets))
        {loginfo("No tweets to read!"); return false;}
        
    // process tweets individually
    uasort($tweets, 'cmpTweetId');
    foreach($tweets as $tweet)
        $lastreadid = processTweet($tweet);
    
    // Save the last updated tweet id
    setPreference("last_twitter_id", $lastreadid);
    return true;
}

function updateTwitterFollowing()
{
    // First get a list of all followers
    global $twit;
    $args = Array();
    $args['stringify_ids'] = "true";
    $followers = $twit->get('followers/ids', $args);

    // the smaller numbers are newer followers - so we can just split off the first
    // 100 and use them, hopefully no more than 100 people follow us per given 
    // update cycle!
    $latestfollowerids = array_slice($followers->ids, 0, 100);

    // loop up friendships (max 100 items in a query)
    $args = Array();
    $args['stringify_ids'] = "true";
    $args['user_id'] = implode(",", $latestfollowerids);
    $friendships = $twit->get('friendships/lookup', $args);

    foreach($friendships as $friendship)
    {
        // we should follow them in return
        if(in_array('followed_by', $friendship->connections) && // if we're followed by them
       !in_array('following', $friendship->connections) && // but not following them ourselves
        !in_array('following_requested', $friendship->connections)) // (and we've not already tried)
        {
            // TODO : you know, we probably shouldn't keep pestering people if they
            // don't want to let us follow them. But on the other hand, there's no
            // point following the language bot and not letting it follow you (ie, it
            // won't see your messages).
            loginfo("Now following ".$friendship->screen_name);
            $twit->post('friendships/create', array('user_id' => $friendship->id_str));
        }
    }
}

function updateTwitterUsers()
{
    // get the user data for a bunch of users
    global $twit;
    $args = array();
    $args['screen_name'] = implode(", ", getUpdateNames(100));;
   $users = $twit->get('users/lookup', $args);

   
    // update each user's information
    foreach($users as $user)
    {
        updateParticipant($user->screen_name, 
                $user->name, 
                $user->location,
                $user->profile_image_url,
                $user->url,
                $user->description);
    }
}

function cmpTweetId($a, $b)
{
    if ($a->id == $b->id) {
        return 0;
    }
    return ($a->id < $b->id) ? -1 : 1;
}

function processTweet($tweet)
{
    // who wrote the tweet?
    $contents = strtolower($tweet->text);
    
    // if we're testing, only allow test tweets through
    // (if we're not testing, don't allow test tweets through!)
    global $testing; 
    if(($testing == true && strpos($contents, "#test") === false) ||
       ($testing != true && strpos($contents, "#test") !== false)) return $tweet->id_str;
    loginfo("Processing tweet ".$tweet->id_str." from user ".$tweet->user->screen_name."\n".$contents);
    
    // Or we can adjust it, which basically just performs a delete
    // followed by a new insert
    $tweet->information = getTweetInformation($tweet);
    
    // If we're registering a new challenger!
    $processed = false;
    $register = strpos($contents, "#register");
    if($register !== false)
    {
        processRegistration($tweet);
        $processed = true;
    }
    
    // We can "delete" a tweet with the undo tag

    $undo = strposa($contents, array("#undo","#delete"));
    if(!$processed && $undo !== false)
    {
        // basically change the 'inc_' to 'del_'
        processUndo($tweet);
        $processed = true;
    }

   
    // We can edit an existing tweet
    $edit = strposa($contents, array("#edit", "#update"));
    if(!$processed && $edit !== false)
    {
        processEdit($tweet);
        $processed = true;
    }
    
    // filter now for ID/Events - we can only run the following on single events
    if(!$processed && findEntryId($tweet) < 0)
    {
        // we count errors as processing, because we've replied
        replyEntryErrorTweet($tweet);
        $processed = true;
   }

   
    // If we're removing an entry
   $giveup = strpos($contents, "#giveup");
    if(!$processed && $giveup !== false)
    {
        processGiveup($tweet);
        $processed = true;
    }

   
    // If we're processing content
    if(!$processed && $tweet->information->contenttype)
    {
        processContent($tweet);
        $processed = true;
    }
        
    // If we got this far and still didn't process it, there's something wrong
    if(!$processed)
    {
        
    }

    // After processing, return the ID
   if(!$testing)
       return $tweet->id_str;
}

function getTweetInformation($tweet)
{
    // an  information array
    global $preferences;
    $contents = strtolower($tweet->text);
    $information = new stdClass();

    // first get the type
    $information->contenttype =
        (strposa($contents, $preferences->TAGS['book']) ? 'book' :
        (strposa($contents, $preferences->TAGS['film']) ? 'film' : ''));
    
    // then other properties
    updateTweetInformation($tweet, $information);
    
    // done
    return $information;
}

// if the contenttype is already known
function updateTweetInformation($tweet, $information)
{
    // information
    $contents = strtolower($tweet->text);
    
    // the language, if we have one
    $information->language = findLanguage($tweet);
    
    // titles are always specified in quotes
    $information->title = findTitleInString($contents);
    
    // pull out numbers
    $information->amount = findAmountInString($contents, $information->contenttype);
}

function processRegistration($tweet)
{
    $language = findLanguage($tweet);
    if(!$language)
    {           
        // no language specified!
        // we need to have a language to sign up!
        replyTweet($tweet, "You can't sign up to a language challenge 
            without a language! Specify one with a hashtag.");
    }
    // our variation must be correct!
    else
    {
        // add a new user!
        insertParticipant($tweet->user->screen_name, $tweet->user->name, "twitter");
        $success = insertEntry($tweet->user->screen_name, $language['Code']);

        // Reply to all specific registrations
        if(!$success) {
            $message = "You're already studying ".$language['Name']."!";
        } else {
            $message = "has registered for the Super Challenge in "
                .$language['Name'].". Good luck!";
       }

       replyTweet($tweet, $message);
    }
}

function processUndo($tweet)
{   
    global $preferences;
    
    // the action code must not be 'del' (ie, we can't have
    // already undone this action.
    $targetaction = getAction($tweet->in_reply_to_status_id_str);
    if(!$targetaction) {
        replyTweet($tweet, "Which tweet do you want to remove? Reply to your own tweet that contains the mistake to undo it.");
        return;
    } elseif(substr($targetaction['ActionCode'], 0, 3) == "del") {
        replyTweet($tweet, "You've already removed this tweet!");
        return;
    } elseif(substr($targetaction['ActionCode'], 0, 4) == "undo") {
        replyTweet($tweet, "You can't undo a #undo tweet!");
        return;
    }
    
    // update the existing action to be 'del_'
    updateAction($tweet->in_reply_to_status_id_str, 'del');
    
    // if we succeeded, add a new action for the undo
    insertActionRecord($tweet->id_str, "undo", 
            getActionEntryId($tweet->in_reply_to_status_id_str), 
            0, $tweet->in_reply_to_status_id_str);
    
    // and roll back the data
    $type = $preferences->CONTENTTYPE[substr($targetaction['ActionCode'], 4)];
    $entrycontent = $preferences->ENTRYCONTENT[$type];
    $entryid = $targetaction['EntryId'];
    $targetactionamount = $targetaction['AmountData'];
    incrementEntryRecord($entryid, $entrycontent, -$targetactionamount);
    
    // tell the user what we did
    replyTweet($tweet, "made a mistake and removed a tweet.");
        
}

function processEdit($tweet)
{   
    global $preferences;
    
    // the action code must not be 'del' or 'edt' (ie, we can't have
    // already undone this action.
    $targetaction = getAction($tweet->in_reply_to_status_id_str);
    if(!$targetaction) {
        replyTweet($tweet, "Which tweet do you want to edit? Reply to your own tweet that contains the mistake to edit it.");
        return;
    } elseif(substr($targetaction['ActionCode'], 0, 3) == "del") {
        replyTweet($tweet, "You can't edit a tweet that's been undone!");
        return;
    } elseif(substr($targetaction['ActionCode'], 0, 3) == "edt") {
        replyTweet($tweet, "You've already edited this tweet! Reply to the new #edit tweet instead.");
        return;
    }
    
    // if there's no content type, we use the existing one and update the tweet information
    // so that stuff like the content amount, which relies on the content type, is populated
    $editaction = $targetaction;
    $editinformation = $tweet->information;
    if(!$editinformation->contenttype) {
        $editinformation->contenttype = $preferences->CONTENTTYPE[substr($targetaction['ActionCode'], 4)];
        updateTweetInformation($tweet, $editinformation);
    }
    
    // update things!
    $actiontaken = false;
    if($editinformation->title) {
        $editaction['TextData'] = $tweet->information->title;
        $actiontaken = true;
    }

    if($editinformation->amount) {
        $editaction['AmountData'] = $editinformation->amount;
        $actiontaken = true;
    }
   
    // an update tweet must actually update something
    if(!$actiontaken) {
        replyTweet($tweet, "An edit tweet must update the title or the amount.");
        return;
    }

    // update the existing action code to be edited
    updateAction($tweet->in_reply_to_status_id_str, 'edt');
    
    // add a new action for the edit (preserving the old timestamp) and increment
    // the new total
    insertActionRecord($tweet->id_str, $editaction['ActionCode'], $editaction['EntryId'], $editaction['AmountData'], $editaction['TextData'], $editaction['Time']);
    $edittype = $preferences->CONTENTTYPE[substr($editaction['ActionCode'], 4)];
    incrementEntryRecord($editaction['EntryId'], $preferences->ENTRYCONTENT[$edittype], $editaction['AmountData']);
    
    // roll back the previous data
    $targettype = $preferences->CONTENTTYPE[substr($targetaction['ActionCode'], 4)];
    incrementEntryRecord($targetaction['EntryId'], $preferences->ENTRYCONTENT[$targettype], -$targetaction['AmountData']);
    
    // tell the user what happened
    replyTweet($tweet, "made a mistake and updated their tweet.");
    
}

function processGiveup($tweet)
{
    removeEntry($tweet->entryid);
    $languagename = $tweet->information->language['Name'];
    replyTweet($tweet, "has stopped studying".
        ($languagename ? " ".$languagename : "").
        " and withdrawn from the challenge. :(");
}

function processContent($tweet)
{
    global $preferences;
    
    // get the type of content (book/movie/etc)
    $type = $tweet->information->contenttype;
    
    // see if we can find any other information
    $amount = $tweet->information->amount;
    $title = $tweet->information->title;
    
    // default our amount, if there's none specified
    if($amount == 0)
        $amount = $preferences->TARGETS[$type];
    
    // if ammount is still zero, something went wrong!
    if($amount == 0) {
        loginfo("something went wrong processing content : ".$tweet->text);
        return;
    }
    
    // return something nice
    $replyoptions = array(
        'book' => "read $amount pages of ".($title ? $title : "a book"),
        'film' => "watched $amount minutes of ".($title ? $title : "a film"));
    
    $replystring = $replyoptions[$type].
            ($tweet->information->language ? " in ".$tweet->information->language['Name'] : "").".";
    
    // increment the content in the database
    insertActionRecord($tweet->id_str, $preferences->ACTIONS[$type], $tweet->entryid, $amount, $title);
    incrementEntryRecord($tweet->entryid, $preferences->ENTRYCONTENT[$type], $amount); // can be used to return the total amount
    
    // say something nice to the person
    replyTweet($tweet, $replystring);
}


// return the ID for the entry matching the tweet, and fill in the language structure if warranted
function findEntryId($tweet)
{
    // language is optional (but helps)
    $language = $tweet->information->language;
    $languagecode = $language ? $language['Code'] : "";

   
    // but we must have a single entry or we can't go further
    $entry = getUniqueEntry($tweet->user->screen_name, $languagecode);
    if($entry < 0)
    {
        $tweet->error = $entry;
        return $entry;
    }
    
    $tweet->entryid = $entry['Id'];
    
    return $tweet->entryid;
}

function findLanguage($tweet)
{
    return findLanguageInString(sanifyText($tweet->text));
}

function replyEntryErrorTweet($tweet)
{        
    if($tweet->error == -1)
    {
        replyTweet($tweet, "You don't seem to be studying".
            ($tweet->information->language ? " ".$tweet->information->language['Name'] : " that language").
            ". Register first using the #register tag!");
    }
    else if($tweet->error == -2)
    {
        replyTweet($tweet, "You're studying several languages. 
            Specify which one you mean by using a hashtag.");
    }
    else
        loginfo("Unrecognised entry error code ".$tweet->error);
}

function replyTweet($tweet, $message)
{
    global $testing;
    global $twit;
    
    $message = "@".$tweet->user->screen_name." ".$message;
    $replytoid = $tweet->id_str;
    
    loginfo("Replied to ".$replytoid." with ".$message.$testing);
    if(!$testing)
        $twit->post('statuses/update', array('status' => $message, 'in_reply_to_status_id' => $replytoid));
}

?>

