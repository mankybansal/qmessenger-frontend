<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Q Messenger | The Smart Way To Communicate</title>

    <script src="jquery.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="animate.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="fontAwesome/css/font-awesome.css"/>

    <script>

        var user;
        var name;

        // i don't know why I have these variables
        doneTabs = Array();
        doneChatContainers = Array();

        function emit_html_for_suggestion(s) {
            console.log(s);
            var sentiment = "<h5>Emotion: " + s.sentiment.emotion + "</h5>";

            var emails = "<h4> Emails: </h4> <ul>";
            for(var email in s.pattern_match.emails) {
                emails += "<li>" + s.pattern_match.emails[email] + "</li>";
            }

            emails += "</ul>";

            if(s.pattern_match.emails.length == 0) {
                emails = "";
            }

            var phone_nos = "<h4> Phone: </h4> <ul>";
            for(var phone in s.pattern_match.numbers) {
                phone_nos += "<li>" + s.pattern_match.numbers[phone] + "</li>"
            }
            phone_nos += "</ul>";

            if(s.pattern_match.numbers.length == 0) {
                phone_nos = "";
            }

            var movies = "";

            if(s.movies.movies.length > 0) {
                var m = s.movies.movies[0];
                //console.log(m);

                movies += "<span> Movie: " + m.title + "</span>";
                movies += "<br>";
                movies += "<span> Rating: </span>" + m.rating;
                movies += "<br>";
                movies += "<span><a href="+ m.imdb + "> IMDB Link </a></span>";
                movies += "<br>";

                //console.log(movies)
            };


            var tldr = ""
            if(s.tldr.tldr.sentences !== undefined && s.tldr.tldr.sentences.length > 0) {
                tldr = "<h4> TL;DR </h4>";
                tldr += "<ul>";
                for(var i = 0; i < s.tldr.tldr.sentences.length; i++) {
                    tldr += "<li>" + s.tldr.tldr.sentences[i] + "</li>";
                }
                tldr += "</ul>"
            };

            return movies + tldr + phone_nos + emails + sentiment;
        }
        function getSuggestions(x){
            var url = 'http://1.186.0.50:8080/cache/' + x;

            $.get(url, function (e) {
                console.log(e);
                var data = e;

                //console.log($('.messageThread[data-id="'+x+'"]').children(".messageSuggestionFrame")[0]);
                if(data.desc != "Not cached"){
                    console.log($('.messageThread[data-id="'+x+'"]').children(".messageSuggestionFrame"));

                    var suggestions = emit_html_for_suggestion(e.data);
                    console.log(e.data.sentiment.emotion);
                    $('.messageThread[data-id="'+x+'"]').children(".messageSuggestionFrame").children(".suggestionText").html(suggestions);
                }
                else {
                    $('.messageThread[data-id="'+x+'"]').children(".messageSuggestionFrame").remove();
                }
            });
        }



        function getUser(email, callback) {

            var url = '../qtest/api/getUser/?username=' + email;

            $.get(url, function (e) {
                return callback(e);
            });

        }

        var myTimer;

        function appendMessage(foo, x) {

            myTimer = setInterval(function () {
                pendingChat(x);
            }, 1000);

            var tmp;
            var str;



            $(".messageFrame").empty();
            $(".messageFrame").append("<div class='messageSpacer'></div>");

//            console.log(foo);
//            console.log(x);


            for (var index = 0; index < foo.length; index++) {

                if (foo[index].receiver == x || foo[index].sender == x) {

                } else {
                    continue;
                }

                var tmpID = "data-id='" + foo[index].id+ "'";
                var tmpRec = "data-receiver='" + foo[index].receiver + "'";
                var tmpSen = "data-sender='" + foo[index].sender + "'";
                var myMessage = false;

                getSuggestions(foo[index].id);

                // check if sender = owner
                if (foo[index].sender === user) {
                    myMessage = true;
                }


                var time = foo[index].time_stamp.substr(11);
                //console.log(time);

                //console.log(foo[index].message);
                if (foo[index].message == '') {
                    tmp = foo[index].file;
                    if (myMessage) {
                        str = "<div class='chat-message message-image myMessage' " + tmpID + tmpRec + tmpSen + "><img src='" + tmp + "'></div>";
                    }
                    else {
                        str = "<div class='chat-message message-image myMessage' " + tmpID + tmpRec + tmpSen + "><img src='" + tmp + "'></div>";
                    }
                }
                else {
                    tmp = foo[index].message;
                    if (myMessage) {
                        str = "<div class='messageThread'" + tmpID  + tmpRec + tmpSen + "> <div class='messageBubble messageTo'> <div class='messageContent '> " + tmp + "</div> <div class='messageInfo'> <div class='timeInfo'>" + time + "</div> </div> </div> <div class='messageSuggestionFrame'> <div class='suggestionHeader'> <div class='qSuggestionLogo'> <img src='QLogo.png' class='qImageSuggestion'> </div> <div class='qSuggestionTitle'>Suggestions from Q</div> </div> <div class='suggestionText'></div></div>  </div>";
                    }
                    else {

                        str = "<div class='messageThread'" + tmpID  +tmpRec + tmpSen + "> <div class='messageBubble messageFrom'> <div class='messageContent '> " + tmp + "</div> <div class='messageInfo'> <div class='timeInfo'>" + time + "</div> </div> </div> <div class='messageSuggestionFrame'> <div class='suggestionHeader'> <div class='qSuggestionLogo'> <img src='QLogo.png' class='qImageSuggestion'> </div> <div class='qSuggestionTitle'>Suggestions from Q</div> </div><div class='suggestionText'></div> </div> </div>";
                    }
                }
                $('.messageFrame').append(str);
            }
            ;


            $('.messageFrame').scrollTop($('.messageFrame').height());

        }


        function sendMessage(x, message) {

            var url = '../qtest/api/send_message/index.php?receiver=' + x + '&message=' + message;

            $.post(url, function (e) {
                //var data = $.parseJSON(e);
                var data = e;
//                console.log(data);

            });
        }

        var thisName;

        function appendThreadTab(x) {
            getUser(x, function (e) {
                thisName = e;

//                console.log(thisName);
                var tmp;
                tmp = "<div data-person='" + x + "' class='contactThread'><div class='contactIcon'><div class='contactIconImage'><i class='fa fa-user' style='font-size: 30px; margin-top: 10px; color: #CCC;'></i></div></div> <div class='contactText'> <div class='contactName'>" + thisName + "</div> <div class='contactDesc'>Last Message Text Here...</div> </div> <div class='contactSentiment'></div> </div>";
                if (doneTabs.indexOf(x) != -1) {
                }
                else {
                    $('.contactFrame').append(tmp);
                    doneTabs.push(x);
                }
            });

        }


        function appendThreadMessages(foo, x) {


            // check if conatiner exists or not
            var tmp = "<div class='chat-container' data-chat-container='" + x + "'></div>"

            // if exists
            if (doneChatContainers.indexOf(x) != -1) {
                // only append message
                // appendMessage(foo,x);
            }

            // if does't exists
            else {
                $('.right-container').append(tmp);
                doneChatContainers.push(x);
                // appendMessage(foo,x);
            }
        }

        function pendingChat(x) {

            var url = '../qtest/api/pendingChats/index.php?receiver=' + user + '&sender=' + x;

            $.post(url, function (e) {
                var data = $.parseJSON(e);
                //console.log(data);

                var tmp;
                var str;

                var foo = data;

//                console.log(foo);
//                console.log(x);


                for (var index = 0; index < foo.length; index++) {

                    if (foo[index].receiver == x || foo[index].sender == x) {

                    } else {
                        continue;
                    }

                    var tmpID = "data-id='" + foo[index].id + "'";
                    var tmpRec = "data-receiver='" + foo[index].receiver + "'";
                    var tmpSen = "data-sender='" + foo[index].sender + "'";
                    var myMessage = false;

                    getSuggestions(foo[index].id);

                    // check if sender = owner
                    if (foo[index].sender === user) {
                        myMessage = true;
                    }


                    var time = foo[index].time_stamp.substr(11);
//                    console.log(time);

//                    console.log(foo[index].message);
                    if (foo[index].message == '') {
                        tmp = foo[index].file;
                        if (myMessage) {
                            str = "<div class='chat-message message-image myMessage' " + tmpID  + tmpRec + tmpSen + "><img src='" + tmp + "'></div>";
                        }
                        else {
                            str = "<div class='chat-message message-image myMessage' " + tmpID  + tmpRec + tmpSen + "><img src='" + tmp + "'></div>";
                        }
                    }
                    else {
                        tmp = foo[index].message;
                        if (myMessage) {
                            str = "<div class='messageThread'"  + tmpID + tmpRec + tmpSen + "> <div class='messageBubble messageTo'> <div class='messageContent '> " + tmp + "</div> <div class='messageInfo'> <div class='timeInfo'>" + time + "</div> </div> </div> <div class='messageSuggestionFrame'> <div class='suggestionHeader'> <div class='qSuggestionLogo'> <img src='QLogo.png' class='qImageSuggestion'> </div> <div class='qSuggestionTitle'>Suggestions from Q</div> </div> <div class='suggestionText'></div></div> </div>";
                        }
                        else {

                            str = "<div class='messageThread'" + tmpID  + tmpRec + tmpSen + "> <div class='messageBubble messageFrom'> <div class='messageContent '> " + tmp + "</div> <div class='messageInfo'> <div class='timeInfo'>" + time + "</div> </div> </div> <div class='messageSuggestionFrame'> <div class='suggestionHeader'> <div class='qSuggestionLogo'> <img src='QLogo.png' class='qImageSuggestion'> </div> <div class='qSuggestionTitle'>Suggestions from Q</div> </div> <div class='suggestionText'></div></div>  </div>";
                        }
                    }
                    $('.messageFrame').append(str);
                }
                ;

            });

            $('.messageFrame').scrollTop($('.messageFrame').height());
        }


        function initChat() {

            var url = '../qtest/api/initChat/index.php';

            $.post(url, function (e) {
                var data = $.parseJSON(e);
//                console.log(data);


                myData = data;
                for (var i = 0; i < data.length; i++) {
                    // set left container tabs
                    var otherPerson;
                    if (data[i].sender === user) {
                        appendThreadTab(data[i].receiver);
                        otherPerson = data[i].receiver;
                    }
                    else {
                        appendThreadTab(data[i].sender);
                        otherPerson = data[i].sender;
                    }
                }


            });
        }

        var myData = [];

        var currentChat;

        var noChatOpen = true;


        $(document).ready(function () {


            $('.contentMenu').hide();
            $('.contentAction').hide();
            $('.contentFrame').hide();

            initChat();


            $(".sendButton").click(function () {
                var myMessage = $('.messageInput').val();
                sendMessage(currentChat, myMessage);
                $('.messageInput').val('');
                initChat();
//                console.log("MY CURRENT" + currentChat);
                setTimeout(function () {
                    appendMessage(myData, currentChat)
                }, 2000);
            });

            $(document).on('click', '.contactThread', function (e) {

                if(noChatOpen){
                    $('.contentMenu').show();
                    $('.contentAction').show();
                    $('.contentFrame').show();

                    noChatOpen = false;
                }

                clearInterval(myTimer);
                var thisUser = $(this).data('person');
                currentChat = thisUser;
                appendMessage(myData, thisUser);

                getUser(currentChat, function (e) {
                    $('.chatTitleText').html(e);
                });


            });


            getUser(user, function (e) {
                name = e;
            });


//            console.log(name);

            $(".myName").html(name);
            $(".myEmail").html(user);


            var overlayOpen = false;

            $(".qLogoFrame").click(function () {
                overlayToggle();
            });

            $(".appOverlay").on('click', function (e) {
                if (e.target !== this)
                    return;
                overlayToggle();
            });

            function overlayToggle() {
                if (!overlayOpen) {
                    showOverlay();
                } else {
                    hideOverlay();
                }
            }

            $(".messageSuggestionFrame").css({height: '1px'});

            function showOverlay() {
                $('.appOverlayPanel').removeClass('bounceOut').addClass('bounceIn');
                $(".appOverlay").fadeIn(350);
                overlayOpen = true;
            }

            function hideOverlay() {
                $('.appOverlayPanel').removeClass('bounceIn').addClass('bounceOut');
                $(".appOverlay").fadeOut(350);
                overlayOpen = false;
            }

            $(document).on('click', ".messageThread", function (e) {
                if ($(this).hasClass('opened')) {
                    $(this).removeClass('opened');
                    $(this).children(".messageSuggestionFrame").animate({height: '1px'}, 200);
                    $(this).children(".messageSuggestionFrame").css('background', '#EEE');
                }
                else {
                    $(this).addClass('opened');
                    $(this).children(".messageSuggestionFrame").animate({height: '300px'}, 200);
                    $(this).children(".messageSuggestionFrame").css('background', '#333');
                }
            });

        });

    </script>

    <?php

    session_start();
    if (isset($_SESSION['username'])) {
        echo "
             <script>
               user = '" . $_SESSION['username'] . "';
             </script>
             ";
    } else {
        header("location:../qtest/index.php");
    }
    ?>

    <style>

        @font-face {
            font-family: OpenSans-Light;
            src: url('fonts/OpenSans-Light.ttf');
        }

        @font-face {
            font-family: Source-Light;
            src: url('fonts/SourceSansPro-Light.ttf');
        }

        @font-face {
            font-family: Source-ELight;
            src: url('fonts/SourceSansPro-ExtraLight.ttf');
        }

        html {
            width: 100%;
            height: 100%;
            padding: 0px;
            margin: 0px;
            font-family: OpenSans-Light;
            overflow: hidden;
        }

        body {
            margin: 0px;
            height: 100%;
            width: 100%;
        }

        .backgroundPanel {
            width: 100%;
            height: 100%;
            background: #EEE;
            position: absolute;
            z-index: 0;
        }

        .backgroundPanelTop {
            width: 100%;
            height: 300px;
            background: #FABB37;
        }

        .foregroundPanel {
            width: 100%;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .appOverlay {

            display: none;

            width: 100%;
            height: 100%;
            position: fixed;
            left: 0px;
            top: 0px;
            z-index: 2;
            background: rgba(0, 0, 0, 0.85);
        }

        .appOverlaySpacer {
            width: 100%;
            height: 10%;
            float: top;
        }

        .appOverlayPanel {
            width: 70%;
            margin: 0 auto;
            float: top;
            height: 80%;
            background: white;
            border-radius: 7px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            -vendor-animation-duration: .2s;
            overflow: hidden;
        }

        .mainPanelSpacer {
            height: 5%;
            width: 100%;
            float: top;
        }

        .mainPanel {
            width: 93%;
            height: 90%;
            border-radius: 7px;
            margin: 0 auto;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .contactPanel {
            width: 348.5px;
            background: #FFF;
            height: 100%;
            float: left;
            border-right: 1.5px solid #EEE;
        }

        .contentPanel {
            width: calc(100% - 350px);
            height: 100%;
            float: right;
            background: #333;
        }

        .myContactFrame {
            float: top;
            height: 150px;
            background: #EEE;
            width: 100%;
        }

        .contactFrame {
            float: top;
            height: calc(100% - 150px);
            width: 100%;
            overflow-y: auto;
            background: #FFF;
        }

        .contentMenu {
            height: 80px;
            float: top;
            width: 100%;
            background: #333;
        }

        .contentFrame {
            height: calc(100% - 180px);
            float: top;
            width: 100%;
            background: #EDE9E4;
            overflow-y: auto;
        }

        .contentAction {
            height: 100px;
            float: top;
            width: 100%;
            background: #DDD;
        }

        .qLogoFrame {
            float: right;
            height: 80px;
            width: 80px;
            background: #333;
            cursor: pointer;
            transition: all ease .2s;
            margin-right: 10px;
        }

        .qLogoFrame:hover {
            background: #555;
        }

        .sendButton {
            height: 100px;
            width: 100px;
            float: right;
            text-align: center;
            padding-top: 20px;
            box-sizing: border-box;
        }

        .actionButton {
            height: 100px;
            width: 100px;
            float: left;
        }

        .inputFrame {
            height: 100%;
            width: calc(100% - 200px);
            float: left;
            text-align: center;
        }

        .messageInput {
            outline: none;
            border: 0px;
            width: 95%;
            margin: 0 auto;
            height: 55px;
            margin-top: 22.5px;
            background: white;
            border-radius: 10px;
            padding: 19px;
            font-size: 17px;
            box-sizing: border-box;
            font-family: OpenSans-Light;
        }

        .messageFrame {
            width: 80%;
            margin: 0 auto;
            height: 100%;
        }

        .messageThread {

            display: inline-block;
            width: 100%;
            box-sizing: border-box;
            vertical-align: top;
            min-height: 10px;
            cursor: pointer;
        }

        .suggestionText {
            color: #EEE;
            height: 10px;
            width: 100%;
            padding: 0px 20px 0px 20px;
        }

        .messageBubble {
            display: inline-block;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
            transition: all ease .2s;
        }

        .messageContent {
            min-width: 100px;
            max-width: 450px;
            min-height: 20px;
            padding: 10px 15px 10px 15px;
            box-sizing: border-box;
        }

        .messageInfo {
            height: 25px;
            width: 100%;
            background: rgba(0, 0, 0, 0.02);
        }

        .messageFrom {
            float: left;
            background: #FFF;
        }

        .messageTo {
            float: right;
            background: #FABB37;
        }

        .messageFrom:hover, .messageTo:hover {
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.2);
        }

        .messageSpacer {
            width: 100%;
            height: 30px;
            float: top;
        }

        .messageDivider {
            width: 100%;
            height: 10px;
        }

        .messageSuggestionFrame {
            height: 1px;
            margin-top: 10px;
            margin-bottom: 10px;
            width: 100%;
            display: inline-block;
            float: top;
            background: #EEE;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .suggestionHeader {
            height: 60px;
            width: 100%;
            float: top;
            background: rgba(0, 0, 0, 0.1);
        }

        .qSuggestionLogo {
            float: left;
            height: 60px;
            width: 60px;
            background: rgba(0, 0, 0, 0.2);
        }

        .qSuggestionTitle {
            height: 100%;
            box-sizing: border-box;
            width: 300px;
            padding-top: 15px;
            padding-left: 10px;
            font-size: 20px;
            color: #DDD;
            float: left;
            font-family: Source-ELight;
        }

        .qImageSuggestion {
            height: 40px;
            width: 40px;
            opacity: .5;
            margin-top: 10px;
            margin-left: 10px;
        }

        .qButtonImage {
            width: 50px;
            height: 50px;
            margin-top: 15px;
            margin-left: 15px;
        }

        .chatIcon {
            width: 80px;
            height: 80px;
            float: left;
            margin-left: 10px;
        }

        .chatIconImage {
            width: 60px;
            height: 60px;
            border-radius: 30px;
            background: white;
            margin-left: 10px;
            margin-top: 10px;
        }

        .chatTitle {
            height: 100%;
            float: left;
            width: 300px;
            box-sizing: border-box;
            padding-top: 15px;
            color: #eee;
        }

        .chatTitleText {
            float: top;
            width: 100%;
            font-size: 20px;
        }

        .chatSubTitleText {
            float: top;
            width: 100%;
            font-size: 15px;
        }

        .timeInfo {
            float: left;
            height: 100%;
            box-sizing: border-box;
            padding-top: 5px;
            margin-left: 10px;
            font-size: 12px;
        }

        .contactThread {
            width: 100%;
            border-bottom: 2px solid #EEE;
            height: 60px;
            float: top;
            cursor: pointer;
            transition: all ease .2s;
        }

        .contactThread:hover {
            background: #FABB37;
        }

        .myDetails {
            height: 80px;
            width: 100%;
            float: top;
        }

        .contactIcon {
            width: 60px;
            height: 60px;
            float: left;
            margin-left: 10px;
        }

        .contactIconImage {
            width: 50px;
            height: 50px;
            border-radius: 25px;
            background: rgba(0, 0, 0, 0.1);
            margin-left: 5px;
            margin-top: 5px;
            text-align: center;

        }

        .contactText {
            height: 100%;
            float: left;
            width: 200px;
            box-sizing: border-box;
            padding-top: 10px;
            color: #333;
            margin-left: 10px;
        }

        .contactName {
            float: top;
            width: 100%;
            font-size: 17px;
        }

        .contactDesc {
            float: top;
            width: 100%;
            font-size: 13px;
        }

        .contactSentiment {
            float: right;
            background: rgba(0, 0, 0, 0.1);
            height: 60px;
            width: 60px;

        }

        .myActions {
            width: 100%;
            height: 40px;
            background: #333;
            float: top;
            margin-top: 30px;
        }

        .myActionButtons {
            width: 40px;
            height: 40px;
            font-size: 20px;
            color: #eee;
            padding-top: 7px;
            box-sizing: border-box;
            float: right;
            transitions: all ease .2s;
            text-align: center;
        }

        .myActionButtons:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .myDP {
            width: 80px;
            height: 80px;
            float: left;
            margin-left: 10px;
        }

        .myDPImage {
            width: 60px;
            height: 60px;
            border-radius: 30px;
            background: rgba(0, 0, 0, 0.1);
            margin-left: 10px;
            margin-top: 10px;
        }

        .myText {
            height: 100%;
            float: left;
            width: 200px;
            box-sizing: border-box;
            padding-top: 15px;
            color: #333;
        }

        .myName {
            float: top;
            width: 100%;
            font-size: 20px;
        }

        .myEmail {
            float: top;
            width: 100%;
            font-size: 15px;
        }

        .suggestionCard {
            width: 80%;
            background: white;
            border-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            margin-top: 20px;
            height: 200px;
        }

        .overlayHeader {
            height: 200px;
            width: 100%;
            background: #222;
            box-sizing: border-box;
            padding-top: 50px;
        }

        .overlayQIcon {
            float: left;
            height: 100px;
            width: 100px;
            margin-left: 50px;
        }

        .qWelcome {
            height: 100px;
            float: left;
            width: 300px;
            box-sizing: border-box;
            color: #EEE;
            margin-left: 30px;
        }

        .qWelcomeTitle {
            float: top;
            width: 100%;
            font-size: 35px;
        }

        .qWelcomeSubTitle {
            float: top;
            width: 100%;
            font-size: 20px;
        }

        .qStatus {
            float: top;
            width: 100%;
            margin-top: 5px;
            font-size: 15px;
            color: #777;
        }


    </style>
</head>
<body>
<div class="backgroundPanel">
    <div class="backgroundPanelTop">

    </div>
</div>
<div class="foregroundPanel">
    <div class="mainPanelSpacer"></div>
    <div class="mainPanel">
        <div class="contactPanel">
            <div class="myContactFrame">
                <div class="myDetails">
                    <div class="myDP">
                        <div class="myDPImage" style="text-align: center;">
                            <i class='fa fa-user' style='font-size: 30px; margin-top: 15px; color: #555;'></i>
                        </div>
                    </div>
                    <div class="myText">
                        <div class="myName"></div>
                        <div class="myEmail">someone@somthing.com</div>
                    </div>
                </div>
                <div class="myActions">

                    <div class="myActionButtons">
                        <i class="fa fa-gear"></i>
                    </div>

                    <div class="myActionButtons">
                        <i class="fa fa-bell-slash"></i>
                    </div>
                    <div class="myActionButtons">
                        <i class="fa fa-ellipsis-h"></i>
                    </div>
                    <div class="myActionButtons">
                        <i class="fa fa-tasks"></i>
                    </div>
                    <div class="myActionButtons"></div>
                </div>

            </div>
            <div class="contactFrame">


            </div>
        </div>
        <div class="contentPanel">
            <div class="contentMenu">
                <div class="chatIcon">
                    <div class="chatIconImage" style="text-align: center;">
                        <i class='fa fa-user' style='font-size: 30px; margin-top: 15px; color: #DDD;'></i>
                    </div>
                </div>
                <div class="chatTitle">
                    <div class="chatTitleText">This Is Some Chat</div>
                    <div class="chatSubTitleText">Chat Subtitle Text Goes Here</div>
                </div>
                <div class="qLogoFrame">
                    <img src="QLogo.png" class="qButtonImage">
                </div>
            </div>
            <div class="contentFrame">

                <div class="messageFrame">


                </div>

            </div>
            <div class="contentAction">
                <div class="actionButton">

                </div>
                <div class="inputFrame">
                    <input type="text" class="messageInput" placeholder="Type a message">
                </div>
                <div class="sendButton">
                    <span class="fa-stack fa-lg" style="font-size: 30px; text-shadow: 0px 0px 10px rgba(0,0,0,0.2);">
                      <i class="fa fa-circle fa-stack-2x" style="color: #0099FF;"></i>
                      <i class="fa fa-send fa-stack-1x fa-inverse" style="color: #FFF;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="appOverlay">
    <div class="appOverlaySpacer">
    </div>
    <div class="appOverlayPanel animated">
        <div class="overlayHeader">
            <div class="overlayQIcon">
                <img src="QLogo.png" style="width: 100px; height: 100px;">
            </div>
            <div class="qWelcome">
                <div class="qWelcomeTitle">Hi There!</div>
                <div class="qWelcomeSubTitle">What can I help you with today?</div>
                <div class="qStatus">ONLINE</div>
            </div>
        </div>

    </div>
</div>
</body>
</html>