var adminUser = '',
    adminPass = '';

var unexpectedError = "An unexpected error occured. If you keep seeing this message, please reload the page or contact technical support.",
    connectionError = "Could not connect. Please check your internet connection or try reloading the page.";

var DomesticInt = 1,
    InternationalInt = 2,
    EconomicInt = 1,
    BusinessInt = 2;

var selectedAction;

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
                $('#action').change()
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

function changeUI( action ){
    selectedAction = action;    
    switch(action){
        case 'addAdmin':
        case 'addUser':
            $('#formFields').html(
                         "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                         "Username: <input type='text' id='user'><br>"+
                         "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                         "Password: <input type='password' id='pass1'><br>"+
                         "Confirm Password: <input type='password' id='pass2'><br>"
                         );
            break;
        case 'viewAdmin':
        case 'viewUser':
            var request = JSON.stringify({ adminUser: adminUser, adminPass: adminPass, action: action });
            $.post( "admin.php", { request: request }, function( response ) {
                try {
                    response = jQuery.parseJSON(response);
                } catch(e) {
                    response = {};
                    return;
                }

                if(response.error){
                    alert(response.error);
                } else if(response.result){
                    var users = jQuery.parseJSON(response.result);
                    if(users.length < 1){
                        $('#formFields').html('No users exist.');
                    } else {
                        var source = "<table class='AdminTable'><tr><td>Username</td>"+
                                     "<td>Password (Hashed)</td></tr>";
                        for(var i=0; i<users.length; i++){
                            source += "<tr><td>" + users[i].Username + "</td>" +
                                      "<td>" + users[i].Password + "</td></tr>";
                        }
                        source += "</table><br><br>Username of account to delete: " +
                                  "<input type='text' id='user'>";
                        $('#formFields').html(source);
                    }
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
    }
}

function submitForm(){
    var request = { adminUser: adminUser, adminPass: adminPass };
    switch(selectedAction){
        case 'addAdmin':
        case 'addUser':
            var user = $('#user')[0].value;
            var pass1 = $('#pass1')[0].value;
            var pass2 = $('#pass2')[0].value;
            
            if(pass1 != pass2) {
                alert('The passwords do not match. Please reenter them.');
                break;
            }
            
            request.action = selectedAction;
            request.user = user;
            request.pass = pass1;
            request = JSON.stringify( request );
            
            $.post( "admin.php", { request: request }, function( response ) {
                try {
                    response = jQuery.parseJSON(response);
                } catch(e) {
                    response = {};
                }

                if(response.error){
                    alert(response.error);
                } else if(response.success){
                    alert('Account created successfully.');
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'viewAdmin':
        case 'viewUser':
            if(selectedAction == 'viewAdmin'){
                request.action = 'deleteAdmin';
            } else {
                request.action = 'deleteUser';
            }
            request.user = $('#user')[0].value;
            request = JSON.stringify( request );
            $.post( "admin.php", { request: request }, function( response ) {
                try {
                    response = jQuery.parseJSON(response);
                } catch(e) {
                    response = {};
                }

                if(response.error){
                    alert(response.error);
                } else if(response.success){
                    alert('Account deleted successfully.');
                    changeUI(selectedAction);
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
    }
}