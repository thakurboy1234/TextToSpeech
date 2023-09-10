// Get the play button element
var playButton = document.getElementById('play-button');

// Add a click event listener to the play button
playButton.addEventListener('click', function() {
    // Get the ID of the current article from the URL
    var articleId = window.location.pathname.split('/').pop();

    // Send an AJAX request to get the text content of the article
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/api/articles/' + 1 + '/text');
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Convert the text content to speech using the Web Speech API
            var utterance = new SpeechSynthesisUtterance(xhr.responseText);
            window.speechSynthesis.speak(utterance);
        } else {
            console.log('Error: ' + xhr.status);
        }
    };
    xhr.send();
});
