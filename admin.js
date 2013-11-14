var adminUser = '',
    adminPass = '';

var unexpectedError = "An unexpected error occured. If you keep seeing this message, please reload the page or contact technical support.",
    connectionError = "Could not connect. Please check your internet connection or try reloading the page.";

var DomesticInt = 1,
    InternationalInt = 2,
    EconomicInt = 1,
    BusinessInt = 2;
    
/*
Description: Changes the current view
Input: Class of the view to change to
Output: None
*/
function changeView( viewClass ) {
    $.when(
        // Hide any currently active views
        $('.View.Active').fadeOut()
                         .removeClass('Active')
    ).then( function() {
        // Show the view being switched to
        $('.View.' + viewClass ).fadeIn()
                         .addClass('Active');
    });
}

function login() {
    adminUser = $('#loginUser')[0].value;
    adminPass = $('#loginPass')[0].value;
    
    if( (adminUser.length < 1) || ( ($('#loginType')[0].value == 'SkyHigh') && (adminPass.length < 1) ) ){
        alert('Please enter both a username and password.');
        return;
    }
    
    var request = {};
    request.adminUser = adminUser;
    request.adminPass = adminPass;
    
    if($('#loginType')[0].value == 'SkyHigh'){
        request.action = 'adminLogin';
        request = JSON.stringify(request);
        $.post( "admin.php", { request: request }, function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                response = {};
            }
            
            if(response.error){
                alert(response.error);
            } else if(response.success){
                changeView('Dashboard');
            } else {
                alert(unexpectedError);
            }
        })
        .fail(function(){
            alert(connectionError);
        });
    } else {
        request.action = 'setupServer';
        request = JSON.stringify(request);
        $.post( "admin.php", { request: request }, function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                response = {};
            }
            
            if(response.error){
                alert(response.error);
            } else if(response.results){
                $('.resultsFrame').html(response.results);
                changeView('SetupResults');
            } else {
                alert(unexpectedError);
            }
        })
        .fail(function(){
            alert(connectionError);
        });
    }
}
