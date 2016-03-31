<?php require_once('frontpage.php'); ?>

<html>
<head>
    <link rel="shortcut icon" href="favicon.ico">
    <title>Language Super Challenge</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" type="text/css" href="style/style.css"/>
</head>
<body>
    <script src="script/main.js"></script>
    <script src="script/sorttable.js"></script>

    <div id='headerback'>
        <div id='header'>
            Language Challenge Twitter Bot
            <a href="http://www.twitter.com/langchallenge"><div id='twit'></div></a>
            <a target="_blank" href="#about" class='jumptokeyword'><div id='help'></div></a>
        </div>
    </div>
    
    <div id='background'>
        <div class='centered'>
            <div class='left'></div>
            <div class='right'></div>
        </div>
    </div>
    
    <div id='main'>
        
    <div id='getstarted' class='<?php hideGetStarted(); ?>'>
        <div class='shortdesc'>
            Track your language learning progress as you read 100 books and watch 100 movies before the 31st of December 2017. Sign up below!
        </div>
    <div class='panel'>
    <div class='header'>
        Getting Started
        <a target="_blank" class='close headerlink' id="closegetstarted">close<div class='headericon' id='closeicon'></div></a>
    </div>
    <div class='subheader'>
        ( It's simple and only takes 30 seconds )
    </div>
    <div class='content'>
        
        <a href="https://twitter.com/signup" target='_blank' class='bullet'>
            <span class='number'>1</span>
            Sign up to Twitter</a>
        <a href="https://twitter.com/#!/langchallenge" target='_blank' class='bullet'>
            <span class='number'>2</span>
            Follow the language challenge bot</a>
        <a class='jumptokeyword bullet' href='#registration'>
            <span class='number'>3</span>
            Send a registration tweet</a>
        <a class='jumptokeyword bullet' href='#reading' >
            <span class='number'>4</span>
            Tweet your books and films</a>
        <a href='#participants' class='bullet'>
            <span class='number'>5</span>
            Check how other learners are doing</a>
        <br><br>
        <i>Note: It may take a few minutes before your tweets register.</i>
    </div>
    </div>
    </div>
    
    <div id='participants' class='panel'>
    <div class='header'>
        Participants
        <a class ='jumptokeyword headerlink' href='#about'>more information<div class='headericon' id='moreicon'></div></a>
    </div>
    <div class='subheader'>

    </div>
    <?php printParticipants(); ?>
    </div>
        
        
    <div id='about' class='panel'>
    <div class='header'>
        About the Super Challenge
    </div>
    <div class='subheader'>
        Track your language learning progress.
    </div>
    <div class='content'>
        <p>The <a href="http://forum.language-learners.org/viewtopic.php?f=21&t=769" target='_blank'>Language Super Challenge</a>
        encourages you to increase your abilities in a foreign language by reading 100 books and watching 100 movies before December 31st 2017. 
        To help you get through the next <?php printDaysLeft() ?> days, the <a href="https://twitter.com/#!/langchallenge" target='_blank'>
        LangChallenge Twitter Bot</a> will read your tweets and track your progress for you.</p>

        <p>Simply <a href="https://twitter.com/signup" target='_blank'>sign up to Twitter</a> and send a 
            <a href='#keywords'>registration tweet</a>, and then the LangChallenge Twitter bot will follow your progress. 
            You can see <a href='#participants'>who else is doing the challenge</a>, and see more details by clicking on your username in the list.</p>
        <br>
        <p>Questions or comments? Please ask on the Forum in the <a href="http://forum.language-learners.org/viewforum.php?f=16"  target='_blank'>technical support room.</a>
    </div>
    </div>
        
    <div class='panel'>
    <div class='header'>
        News & Updates
    </div>
    <div class='subheader'>
        What's been happening to the Super Challenge?
    </div>
    <div class='content'>
        <p><span class='subhead'>Update 0.6</span><br>
            - Released code as opensource.<br>
            - Now hosted on the language-learners.org website for 2016-17 Super Challenge.<br>
        </p>
        <div class='hideable' id='news'>
        <p><span class='subhead'>Update 0.5</span><br>
            - Added activity badges.<br>
            - Streaks now display a tick if you have completed the requirement for the week.<br>
        </p>

       <p><span class='subhead'>Update 0.4</span><br>
            - New ranking algorithms.<br>

           - Fixed bug with star display for complete challenges.<br>
            - Removed daily sprint badge.</p>
        <p><span class='subhead'>Update 0.3</span><br>
            - There's a new interface!<br>
            - Stars now work, and tracking should be out of 25.<br>

           - Added awards for consistent practice.</p>
        <p> <span class='subhead'>Update 0.2</span><br>
            - Ironed out the major bugs, the language bot is ready to go!</p>
        <p> <span class='subhead'>Update 0.1</span><br>
            - First release of the 2014-15 language challenge bot!<br>
            - Simplified things and removed the multiple challenge types.<br>
            - Also removed writing/conversing, it wasn't used much and isn't part of this challenge.</p>
        </div>
        <a class='toggle' href='#news'>show more</a>
    </div>
    </div>
        
    <div class='panel'>
    <div class='header'>
        Keywords
    </div>
    <div class='subheader'>
        Using hashtags to communicate with the twitter bot.
    </div>
    <div class='content'>

       <p> Every tweet should contain at least one keyword, along with any required information. The options outlined below are in the following format:<br>
        <span class='subhead'>Functionality</span> : #tag/#alternativetag, required information, [#optional tags], [optional information]</p>
    
        <div class='hideable' id='keywords'>
    
        <p><span class='subhead' id='registration'> Registration</span> : #register, #swedish/#french/#it/#ru/etc<br>

       Tags in brackets are optional.<br>

       Example: "<i>@langchallenge I'm going to #register and study #French for the Language Challenge.</i>"</p>
    
        <p>If participating with multiple languages, simply tweet a new registration for each one.<br>
        Example: "<i>@langchallenge I'm also going to #register for #Swedish.</i>"</p>
    
        <p><span class='subhead' id='withdrawing'>Withdrawing</span> : #giveup, [#language]<br>
        If you don't want to change the challenge but withdraw completely, use this.<br>
        <b><i>Be Careful!</i></b> because all associated data will be removed along with your entry in the participants table.<br>
        Example: "<i>@langchallenge #Swedish is too hard to pronounce. I #giveup.</i>"</p>

        <p> <span class='subhead' id='reading'>Reading</span> : #book/#read, [#swedish/#french/#it/#ru/etc], [123 page/s], ["book title"]<br>
        A book automatically counts as 50 pages. If you only read part of a book, simply write
        how many pages you read, followed by the word 'page(s)'. <br>If you read a really long book,
        it will automatically be split into the appropriate number against your score.<br>
        If you want to say which book you've been reading, encase the title in quotation marks.<br>
        If you're studying multiple languages, you need to specify which one using the language code.<br>
        Examples: "<i>@langchallenge I just read a #book.</i>"<br>
                  "<i>@langchallenge I just finished reading a #sv #book with 60 pages. It was called "The Invisible Book" and I kept losing it.</i>"</p>
    
        <p> <span class='subhead' id='watching'>Watching</span> : #film/#movie/#watch(ed)(ing)/#listen(ed)(ing)/#audio/#radio, [#swedish/#french/#it/#ru/etc], [123 min(ute)/h(ou)r/s], ["film title"]<br>
        A film automatically counts as 90 minutes. If you watch part of a film or a tv series,
        simply write how long it was followed by the word 'minute(s)', 'min(s)', 'hour(s)' or 'hr(s)'. <br>
        If you want to specify which film you watched, encase the title in quotation marks.<br>
        If you're studying multiple languages, you need to specify which one using the language code.<br>
        Examples: "<i>@langchallenge I just watched a #movie #ru.</i>"<br>
                  "<i>@langchallenge I just saw the first 20 min of a #film ("The endless snowstorm"). Really strange Indie movie!</i>"</p>
 
        <p><span class='subhead' id='editing'>Editing</span> : #edit(ed)/#update(ed), [123 page(s)/minute(s)/etc], ["updated title of item"]<br>
        If you made a mistake in a tweet, you can change the number of pages, the time, the number of words, or the title by using this command.<br>
        You must reply to the tweet you wish to edit so that the system knows which one you're referring to.<br>
        Find the tweet in your stream and then click on the "reply" button just below it, beside the blue arrow.<br>
        Examples: <i>reply: "@langchallenge The author wrote another chapter and #updated the book online, now I've read 260 pages total!</i>\"<br>
                 <i>reply: "@langchallenge I misread the title and need to #edit it to be "How to FIND your monocle in 30 seconds".</i>\"</p>
 
        <p><span class='subhead' id='deleting'>Deleting</span> : #undo(ne)/#delete(ed), <br>
        If your mistake isn't fixable or you no longer want to count an item, use this command to remove the tweet from your list entirely.<br>
        You must reply to the tweet you wish to remove so that the system knows which one you're referring to.<br>
        Find the tweet in your stream and then click on the "reply" button just below it, beside the blue arrow.<br>
        Example: <i>reply: "@langchallenge I lied about this update and now feel very guilty, so I'm going to #undo it.</i>"</p>

        </div>
        <a class='toggle' href='#keywords' id='expandkeywords'>show more</a>
        
    </div>
    </div>
        
    <div class='panel'>
    <div class='header'>
        Languages
    </div>
    <div class='subheader'>
        Kiun lingvon vi studos?

   </div>
    <div class='content'>
        <p>If your language isn't in this list, please let us know in the <a href="http://forum.language-learners.org/viewforum.php?f=16" target='_blank'>technical support room.</a>
        <?php printLanguages() ?>
        <a class='toggle' href='#languages'>show more</a>
    </div>
    </div>
        
    <?php showUpdateTime(); ?>

    </div>

</body>
</html>