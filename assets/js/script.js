// Create a date object
var dateObj = new Date();
var today = dateObj.getFullYear() + "" + (dateObj.getMonth()+1)  + dateObj.getDate();

// Clean the local storage
// Check if date is not defined on localStorage
if (!localStorage.getItem('date'))
    // If it is not, define it
    localStorage.setItem('date', today);
// If it is defined, check if date is differente of current date
else if (localStorage.getItem('date') != today) {
    // If it is different, clean the localStorage
    localStorage.clear();
    // If it is not, define it
    localStorage.setItem('date', today);
}

// Ready function
$(function(){
    // Send an alert before the page is closed or refreshed
    $(window).on('beforeunload', function(){
        return 'If you close this page, the sound notification will be bost. Continue?';
    });

    // console.log(localStorage);

    // Fill up the today area with date data
    $("#today").html(dateObj.getDate() + "/" + (dateObj.getMonth()+1)  + "/" + dateObj.getFullYear());
    // Set each cronometer
    $('.cronometer').each(function(i){
        // Define hour and minute
        var hour = $(this).attr('date-hour');
        var minute = $(this).attr('date-minute');
        var date = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), hour, minute);
        // Identify the index of currennt cronometer
        var cronometerIndex = $('.cronometer').index(this);
        // Configure the countdown plugin
        $(this).countdown({
            until : date,
            compact: true,
            description: '',
            onExpiry: function() {
                window.focus();
                // Create an object of audio for alert user
                $('.sound_container').eq(cronometerIndex).html('<audio autoplay loop><source src="assets/mp3/beep.mp3" type="audio/mpeg" /><source src="mp3/beep.ogg" type="audio/ogg" /><embed hidden="true" autostart="true" loop="true" src="mp3/beep.mp3" /></audio>');
                $('.stop').eq(cronometerIndex).show(); // Show the stop button
                $('.stop').eq(cronometerIndex).parent().parent().find('.answer').show(); // Show the answer buttonn
                notification("Attentionn", "New questionnaire available!"); // Show the notification
            }
        });
    });

    // Hidden .stop and .answer
    $('.stop').hide();
    $('.answer').each(function(){
        if ($(this).parents('.session').find('.countdown-amount').html() != '00:00:00')
            $(this).hide();
    });

    $('.notimedefined .answer:first').each(function(){
        $(this).show();
    });

    // Var to save the ID of last questionnaire filled
    var biggestID = '';
    // Loop for each questionnaire
    $('.questionnaire').each(function(){
        // The current ID
        var ID = $(this).attr('id');
        // Check if the current id is set on localStorage
        if (localStorage.getItem(ID)) {
            // If true, hide the answer button
            $(this).find('.answer').hide();
            // Set the ID as the biggestID
            biggestID = ID;
        } else {
            // Otherwise, hide the answered button
            $(this).find('.answered').hide();
        }
    });
    // If biggestID has some value
    if (biggestID) {
        // Check the last index
        var index = $('.questionnaire').index($('#'+biggestID)) + 1;
        // Get the next questionnaire ID
        var nextObjectID = $('.questionnaire').eq(index).attr('id');
        // Animate the document to next questionnaire
        if ($('#' + nextObjectID).length)
            $('html, body').animate({
                scrollTop: $('#' + nextObjectID).offset().top - 100
            }, 300);
    }

    // Configure the event of answer buttons
    $('.answer').each(function(){
        // Click event
        $(this).on('click', function(e){
            $(this).removeClass('blink_me'); // Remove blink_me class
            $(this).parent().find('.answered').show('medium'); // Show the answered button
            $(this).parent().find('.answered .button').show().addClass('blink_me'); // Add the blink_me class to answered button
            $(this).hide(); // Hide the answer buttonn

            // Check the delay of next questionnaire
            var currentQuestionnaire = $(this).parents('.questionnaire');
            var nextQuestionnaire = $(currentQuestionnaire).next();
            var nextDelay = $(nextQuestionnaire).attr('data-delay');
            if (nextDelay > 0) {

                var cronometerIndex = $('.cronometer').index(this);

                $(nextQuestionnaire).find('.q-intro').hide();
                $(nextQuestionnaire).find('.q-countdown-container').show();
                $(nextQuestionnaire).find('.q-countdown').countdown({
                    until: nextDelay * 60,
                    compact: true,
                    description: '',
                    onExpiry: function() {
                        window.focus();
                        // Create an object of audio for alert user
                        $(nextQuestionnaire).find('.sound_container').html('<audio autoplay loop><source src="assets/mp3/beep.mp3" type="audio/mpeg" /><source src="mp3/beep.ogg" type="audio/ogg" /><embed hidden="true" autostart="true" loop="true" src="mp3/beep.mp3" /></audio>');
                        $(nextQuestionnaire).find('.stop').show(); // Show the stop button
                        $(nextQuestionnaire).find('.answer').show(); // Show the answer button
                        $(nextQuestionnaire).find('.q-countdown-container').hide(); // Show the answer button
                        notification("Attentionn", "New questionnaire available!"); // Show the notification
                    }
                });
            }
        });
    });

    // Configure the event of answered buttons
    $('.answered').each(function(){
        // Click event
        $(this).find('.button').on('click', function(e){
            // Identify the ID
            var ID = $(this).attr('data-id');
            // Remove blink_me classe
            $(this).removeClass('blink_me').hide();
            // Save on locaStorage that this questionnaire was filled out
            localStorage.setItem(ID, true);
            //
            //$('#'+ID).find('.answer').addClass('blink_me');
            // Index of next questionnaire
            var index = $('.questionnaire').index($(this).parent().parent()) + 1;
            // ID of next questionnaire
            var nextObjectID = $('.questionnaire').eq(index).attr('id');
            // Animate the document to next questionnaire
            if ($('#' + nextObjectID).length)
                $('html, body').animate({
                    scrollTop: $('#' + nextObjectID).offset().top - 40
                }, 300);
        }).hide();
    });

    // Configure the event of stop buttons
    $('.stop').each(function(){
        // Click event
        $(this).on('click', function(e){
            // Index of stop buttonn
            var index = $('.stop').index(this);
            // Stop the audio and empty the container
            $('.sound_container').eq(index).trigger('pause').empty();
            // Add the blink_me class to answer button
            $(this).parent().find('.answer').addClass('blink_me');
            // Hide the stop button
            $(this).hide();
            // Stop the event
            e.preventDefault();
        });
    });

});


// Source: http://jsfiddle.net/SiamKreative/8W8XK/
function notification(title, content) {

    // Let's check if the browser supports notifications
    if (!("Notification" in window)) {
        alert("This browser does not support notifications.");
    }
    // Let's check if the user is okay to get some notification
    else if (Notification.permission === "granted") {
        // If it's okay let's create a notification
        var notification = new Notification(title, { body: content });
    }

    // Otherwise, we need to ask the user for permission
    // Note, Chrome does not implement the permission static property
    // So we have to check for NOT 'denied' instead of 'default'
    else if (Notification.permission !== 'denied') {
        Notification.requestPermission(function (permission) {
            // If the user is okay, let's create a notification
            if (permission === "granted") {
                var notification = new Notification(title, { body: content });
            }
        });
    }
    // At last, if the user already denied any notification, and you
    // want to be respectful there is no need to bother them any more.
}