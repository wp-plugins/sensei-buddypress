jQuery(document).ready(function () {

    var receiver = getParameterByName('receiver');
    jQuery('#buddypress #send_message_form #send-to-input').val(receiver);

    // get query string values by name
    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

});