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
            break;
        case 'addRate':
            $('#formFields').html(
                         "Airline: <input type='text' id='airline'><br>" +
                         "Type: <select id='type'>" + 
                            "<option value='" + DomesticInt + "'>Domestic</option>" +
                            "<option value='" + InternationalInt + "'>International</option>" +
                         "</select><br>" +
                         "Class: <select id='class'>" + 
                            "<option value='" + EconomicInt + "'>Economic</option>" +
                            "<option value='" + BusinessInt + "'>Business</option>" +
                         "</select><br>" +
                         "From: <input type='text' id='from'><br>" +
                         "To: <input type='text' id='to'><br>" +
                         "Price: <input type='text' id='price'><br>" +
                         "Time: <input type='text' id='time'><br>"
                         );
            break;
        case 'viewRate':
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
                    var rates = jQuery.parseJSON(response.result);
                    if(rates.length < 1){
                        $('#formFields').html('No rates exist.');
                    } else {
                        var source = "<table class='AdminTable'><tr><td>ID</td><td>Airline</td><td>Class</td>"+
                                     "<td>Type</td><td>From</td><td>To</td><td>Price</td><td>Time</td></tr>";
                        for(var i=0; i<rates.length; i++){
                            source += "<tr><td>" + rates[i].ID + "</td><td>" +
                                       rates[i].Airline + "</td><td>";
                            if(rates[i].Type == DomesticInt){ source += "Domestic"; }
                            if(rates[i].Type == InternationalInt){ source += "International"; }
                            source += "</td><td>";
                            if(rates[i].Class == EconomicInt){ source += "Economic"; }
                            if(rates[i].Class == BusinessInt){ source += "Business"; }
                            source += "</td><td>" + rates[i].From + "</td><td>" + rates[i].To;
                            source += "</td><td>" + rates[i].Price + "</td><td>" + rates[i].Time;
                            source += "</td></tr>";
                        }
                        source += "</table><br><br>ID of rate to delete: " +
                                  "<input type='text' id='idNum'>";
                        $('#formFields').html(source);
                    }
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'importRates':
            $('#formFields').html(
                "Paste previously exported rates in the text box below:<br>" +
                "<input type='text' id='data'>"
                );
            break;
        case 'exportRates':
            $('#formFields').html(
                "Click Submit to open a window that you can copy rates from.<br>" +
                "You can paste the exported rates into a text file or send them via email."
                );
            break;
        case 'addLogo':
            $('#formFields').html(
                "Airline: <input type='text' id='airline'><br><br>" +
                "Image: <input type='file' id='image'>"
                );
            break;
        case 'viewLogo':
            var request = JSON.stringify({ adminUser: adminUser, adminPass: adminPass, action: action });
            $.post( "admin.php", { request: request }, function( response ) {
                try {
                    response = jQuery.parseJSON(response);
                } catch(e) {
                    response = {};
                }
                
                if(response.error){
                    alert(response.error);
                } else if(response.result){
                    
                    var logos = response.result,
                        path = './resources/logos/';
                    
                    if(logos.length < 1){
                        $('#formFields').html('No logos have been uploaded.');
                    } else {
                        var source = "<table class='AdminTable'><tr><td>Airline</td><td>Thumbnail</td></tr>";
                        for(var i=0; i<logos.length; i++){
                            var imgFile = path + logos[i],
                                airline = logos[i].substring( 0, logos[i].length-4 );
                            
                            source += "<tr><td>" + airline + "</td>" +
                                      "<td><a href='javascript:window.open(\"" + imgFile + "\")'>" +
                                      "<img src='" + imgFile + "' class='logoThumb'></a></td></tr>";
                        }
                        source += "</table><br><br>Name of airline to delete: " +
                                  "<input type='text' id='airline'>";
                        $('#formFields').html(source);
                    }
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'addBooking':
            $('#formFields').html(
                "Username: <input type='text' id='user'><br>" +
                "Date: <input type='text' id='date' class='getDate'><br>" +
                "Adults: <input type='text' id='adults'><br>" +
                "Children: <input type='text' id='children'><br>" +
                "Infants: <input type='text' id='infants'><br>" +
                "RateID: <input type='text' id='rateID'>"
            );
            
            $(function() {
                var options = {
                    changeYear: true,
                    yearRange: "0:+2",
                    minDate: 0
                }
                $(".getDate").datepicker(options);
            });
            
            break;
    }
}

function submitForm(){
    var request = { adminUser: adminUser, adminPass: adminPass, action: selectedAction };
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
        case 'viewRate':
            request.action = 'deleteRate';
            request.idNum = $('#idNum')[0].value;
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
                    alert('Rate deleted successfully.');
                    changeUI(selectedAction);
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'addRate':
            request.airline = $('#airline')[0].value;
            request.type = $('#type')[0].value;
            request.classVal = $('#class')[0].value;
            request.from = $('#from')[0].value;
            request.to = $('#to')[0].value;
            request.price = $('#price')[0].value;
            request.timeVal = $('#time')[0].value;
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
                    alert('Rate added successfully.');
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'importRates':
            var importData, goodRates = 0, badRates = 0;
            var errorDisplayed = false;                        
            try {
                importData = jQuery.parseJSON( $('#data')[0].value );
                if(importData.length < 1){ throw ''; }
            } catch(e) {
                alert('There are no rates to import. Please make sure you copied all of the previously exported data.');
                break;
            }
            
            request.action = 'addRate';
            
            for(var i=0; i<importData.length; i++){
                request.airline = importData[i].Airline;
                request.type = importData[i].Type;
                request.classVal = importData[i].Class;
                request.from = importData[i].From;
                request.to = importData[i].To;
                request.price = importData[i].Price;
                request.timeVal = importData[i].Time;
                request = JSON.stringify( request );
                
                $.post( "admin.php", { request: request }, function( response ) {
                    try {
                        response = jQuery.parseJSON(response);
                    } catch(e) {
                        response = {};
                    }

                    if(response.success){
                        goodRates++;
                    } else {
                        badRates++;
                    }
                })
                .fail(function(){
                    if(!errorDisplayed){
                        alert(connectionError);
                        errorDisplayed = true;
                    }
                })
                .always(function(){
                    if( (goodRates+badRates) == importData.length){
                        if(goodRates == importData.length){
                            alert('All rates imported successfully.');
                        } else {
                            alert(goodRates + ' of ' + (goodRates+badRates) + ' rates imported. ' +
                                  'Some of the rates could not be imported.');
                        }
                    }
                });
            }
            break;
        case 'exportRates':
            request.action = 'viewRate';
            request = JSON.stringify( request );
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
                    var rates = jQuery.parseJSON(response.result);
                    if(rates.length < 1){
                        alert('There are no rates to export.');
                    } else {
                        prompt('Press Ctrl+C (Windows) or '+String.fromCharCode(8984)+'+C (Mac) to copy the exported data. '+
                               'Click OK or Cancel when you are done.', response.result);
                    }
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'addLogo':
            var reader = new FileReader();
            reader.onload = function(e){
                var airline = $('#airline').val(),
                    imageData = event.target.result;
                
                request.airline = airline;
                request.imageData = imageData;
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
                        alert('Logo uploaded successfully.');
                    } else {
                        alert(unexpectedError);
                    }
                })
                .fail(function(){
                    alert(connectionError);
                });
            }
            reader.readAsDataURL( document.getElementById('image').files[0] );

            break;
        case 'viewLogo':
            request.action = 'deleteLogo';
            request.airline = $('#airline').val();
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
                    alert('Logo deleted successfully.');
                    changeUI(selectedAction);
                } else {
                    alert(unexpectedError);
                }
            })
            .fail(function(){
                alert(connectionError);
            });
            break;
        case 'addBooking':
            request.user = $('#user').val();
            request.dateVal = $('.getDate').val();
            request.adults = $('#adults').val();
            request.children = $('#children').val();
            request.infants = $('#infants').val();
            request.rateID = $('#rateID').val();
            
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
                    alert('Booking added successfully.');
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
