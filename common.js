var loggedIn = false,
    Username;

var unexpectedError = "An unexpected error occured. If you keep seeing this message, please reload the page or contact technical support.",
    connectionError = "Could not connect. Please check your internet connection or try reloading the page.",
    incompleteFormError = "Please fill out the form completely before submitting.";

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

/*
Description: Updates the dashboard to reflect the current login state.
Input: None
Output: None
*/
function updateDashboard() {
    if(loggedIn){
        $('#HeaderUser').text(Username);
        $('.Dashboard').addClass('Loggedin');
    } else {
        $('.Dashboard').removeClass('Loggedin');
    }
    
    // Enable/Disable buttons based on login state
    $('.Dashboard > button.LoginRequired').addClass('locked').prop("disabled",true);
    $('.Loggedin > button.LoginRequired').removeClass('locked').prop("disabled",false);
}

function startup() {
    updateDashboard();
    
    $('#ratesDomestic').click();
    $('#ratesEconomic').click();
    
    $(function() {
        var options = {
            changeYear: true,
            yearRange: "0:+2",
            minDate: 0
        }
        $(".getDate").datepicker(options);
    });
}

function login() {
    var user = $('#loginUser')[0].value;
    var pass = $('#loginPass')[0].value;
    
    if( (user.length < 1) || (pass.length < 1) ){
        alert('Please enter both a Username and a Password.');
        return;
    }
        
    $.post( "login.php", { user: user, pass: pass }, function( response ) {
        try {
            response = jQuery.parseJSON(response);
        } catch(e) {
            response = {};
        }
        
        if(response.error){
            alert(response.error);
        } else if(response.success){
            loggedIn = true;
            Username = user;
            updateDashboard();
            changeView('Dashboard');
        } else {
            alert(unexpectedError);
        }
    })
    .fail(function(){
        alert(connectionError);
    });
}

function register() {
    var user = $('#registerUser')[0].value;
    var pass1 = $('#registerPass1')[0].value;
    var pass2 = $('#registerPass2')[0].value;
    
    if( (user.length < 1) || (pass1.length < 1) || (pass2.length < 1) ){
        alert('Please enter your desired Username and the Password twice.');
        return;
    }
    
    if(pass1 != pass2) {
        alert('The passwords do not match. Please reenter them.');
        return;
    }
    
    $.post( "register.php", { user: user, pass: pass1 }, function( response ) {
        try {
            response = jQuery.parseJSON(response);
        } catch(e) {
            response = {};
        }
        
        if(response.error){
            alert(response.error);
        } else if(response.success){
            alert('You are now registered. You will now be returned to the dashboard where you can login.');
            changeView('Dashboard');
        } else {
            alert(unexpectedError);
        }
    })
    .fail(function(){
        alert(connectionError);
    });
}

function logout() {
    if(!confirm('Are you sure you want to logout?')){
        return;
    }
    
    loggedIn = false;
    Username = '';
    document.cookie = encodeURIComponent('user') + "=deleted; expires=" + new Date(0).toUTCString();
    document.cookie = encodeURIComponent('pass') + "=deleted; expires=" + new Date(0).toUTCString();
    window.location.reload()
}

function getRates() {
    var classInt, typeInt;
    
    var table = $('.RatesTable')[0];
    
    if($('#ratesDomestic')[0].checked){
        typeInt = DomesticInt;
    } else if($('#ratesInternational')[0].checked){
        typeInt = InternationalInt;
    } else {
        return;
    }
    
    if($('#ratesEconomic')[0].checked){
        classInt = EconomicInt;
    } else if($('#ratesBusiness')[0].checked){
        classInt = BusinessInt;
    } else {
        return;
    }
    
    $.get( "getRates.php", { class: classInt, type: typeInt } )
        .done(function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                alert(unexpectedError);
                return;
            }
            
            if(response.error){
                alert(response.error);
            } else {
                // Clear the table
                while(table.rows.length > 1){
                    table.deleteRow(1);
                }
                
                // Add a row to the table for each object in the response
                for(i=1; i<=response.length; i++){
                    table.insertRow(i);
                    for(j=0; j<4; j++) { table.rows[i].insertCell(j); }
                    table.rows[i].cells[0].innerHTML = response[i-1]['From'];
                    table.rows[i].cells[1].innerHTML = response[i-1]['To'];
                    table.rows[i].cells[2].innerHTML = response[i-1]['Price'];
                    table.rows[i].cells[3].innerHTML = response[i-1]['Time'];
                }
            }
        })
        .fail(function(){
            alert(connectionError);
        });

}

function changeBookFlight() {
    var fromArray = [], toArray = [];
    $.get( "getRates.php", { all: 1 } )
        .done(function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                alert(unexpectedError);
                return;
            }
            
            if(response.error){
                alert(response.error);
            } else {
                var fromOptions = $('#bookFrom')[0].options,
                    toOptions = $('#bookTo')[0].options;
                
                while(fromOptions.length > 0)
                    fromOptions.remove(0);
                
                while(toOptions.length > 0)
                    toOptions.remove(0);
            
                for(i=0; i<response.length; i++){
                    if(fromArray.indexOf(response[i]['From']) < 0){
                        fromArray.push(response[i]['From']);
                        fromOptions.add( new Option(response[i]['From'],response[i]['From']) );
                    }
                    
                    if(toArray.indexOf(response[i]['To']) < 0){
                        toArray.push(response[i]['To']);
                        toOptions.add( new Option(response[i]['To'],response[i]['To']) );
                    }
                }
                
                changeView('BookFlight');
            }
        })
        .fail(function(){
            alert(connectionError);
        });
}

function findFlight() {
    var classInt, typeInt;
    
	var destination = $("#bookTo").val(),
        origin = $("#bookFrom").val();
	
    if($('#bookDomestic')[0].checked){
        typeInt = DomesticInt;
    } else if($('#bookInternational')[0].checked){
        typeInt = InternationalInt;
    } else {
        alert(incompleteFormError);
        return;
    }
    
    if($('#bookEconomic')[0].checked){
        classInt = EconomicInt;
    } else if($('#bookBusiness')[0].checked){
        classInt = BusinessInt;
    } else {
        alert(incompleteFormError);
        return;
    }
	
	$.get( "getRates.php", { class: classInt, type: typeInt } )
        .done(function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                alert(unexpectedError);
                return;
            }
            
            if(response.error){
                alert(response.error);
            } else {
                var resultArray = [];
                for(i=0; i<response.length; i++){
                    if( (response[i]['From'] == origin) && (response[i]['To'] == destination) )
                        resultArray.push( response[i] );
                }
                
                if(resultArray.length < 1){
                    alert('No flights found.');
                    return;
                }
                
                var source = '<h3>' + resultArray.length + ' flight(s) found:</h3>';
                for(i=0; i<resultArray.length; i++){
                    source += '<a href="javascript:bookFlight(' + resultArray[i].ID;
                    source += ')">From ' + resultArray[i].From + ' to ' + resultArray[i].To;
                    source += ' at ' + resultArray[i].Time + ' for $' + resultArray[i].Price;
                    source += '</a><br><br>';
                }
                $('#flightList').html( source );
                changeView('FlightList');
            }
        })
        .fail(function(){
            alert(connectionError);
        });
}

function bookFlight( id ) {
    if(!confirm('Do you want to book this flight?'))
        return;
    
    var date = $("#bookDate").val(),
        numAdults = $("#bookAdults").val(),
        numChildren = $("#bookChildren").val(),
        numInfants = $("#bookInfants").val();
    
    $.post( "bookFlight.php", { date: date, adults: numAdults, children: numChildren, infants: numInfants, rateID: id }, function( response ) {
        try {
            response = jQuery.parseJSON(response);
        } catch(e) {
            response = {};
        }
        
        if(response.error){
            alert(response.error);
        } else if(response.BookingID){
            var flightType = $('[name=type]:checked').val(),
                flightClass = $('[name=class]:checked').val();

            var TotalPrice = response.rate.Price*numAdults;
            
            var source = '<img class="logoIMG" src="./resources/logos/' + response.rate.Airline + 
                         '.png" onerror="this.remove()" /><br>' +
                         '<b>From:</b> ' + response.rate.From + '<br><br>' +
                         '<b>To:</b> ' + response.rate.To + '<br><br>' +
                         '<b>Type:</b> ' + flightType + '<br><br>' +
                         '<b>Class:</b> ' + flightClass + '<br><br>' +
                         '<b>Traveling Date:</b> ' + date + '<br><br>' +
                         '<b>Totoal Price:</b> ' + TotalPrice + '<br><br>' +
                         '<b>Departure Time:</b> ' + response.rate.Time + '<br><br>' +
                         '<b>Adults:</b> ' + numAdults + '<br><br>' +
                         '<b>Children:</b> ' + numChildren + '<br><br>' +
                         '<b>Infants:</b> ' + numInfants + '<br><br>' +
                         '<b>Ticket Number:</b> ' + response.BookingID + '<br><br>' +
                         '<b>Booked By:</b> ' + Username + '<br><br>';
            
            
            $('#TicketContent').html( source );
            
            $('#ticketClose').off('click')
                             .click( function() { changeView('Dashboard'); } );
            changeView('TicketSummary');
        } else {
            alert(unexpectedError);
        }
    })
    .fail(function(){
        alert(connectionError);
    });
}

function changeViewBookedFlights() {
    
    var table = $('.BookedFlightsTable')[0];
    
    $.get( "getRates.php", { all: 1 } )
        .done(function( response ) {
            try {
                response = jQuery.parseJSON(response);
            } catch(e) {
                alert(unexpectedError);
                return;
            }
            
            if(response.error){
                alert(response.error);
            } else {
                
                var rateCache = {};
                
                // Store rate objects indexed by ID so they can be looked up quickly
                for(i in response){
                    rateCache[ response[i]['ID'] ] = response[i];
                }
                
                $.get( "getBookedFlights.php", { } )
                    .done(function( response ) {
                        try {
                            response = jQuery.parseJSON(response);
                        } catch(e) {
                            alert(unexpectedError);
                            return;
                        }
                        
                        if(response.error){
                            alert(response.error);
                        } else {
                            // Clear the table
                            while(table.rows.length > 1){
                                table.deleteRow(1);
                            }
                            
                            if(response.length < 1){
                                table.insertRow(1);
                                table.rows[1].insertCell(0);
                                table.rows[1].cells[0].colSpan = 14;
                                table.rows[1].cells[0].innerHTML = '<center>You have no booked flights.</center>';
                            }
                            
                            // Add a row to the table for each object in the response
                            for(i=0; i<response.length; i++){

                                var ID = response[i]['RateID'],
                                    TotalPrice = rateCache[ID]["Price"]*response[i]["Adults"];
                                
                                table.insertRow(i+1);
                                for(j=0; j<14; j++) { table.rows[i+1].insertCell(j); }
                                table.rows[i+1].cells[0].innerHTML = rateCache[ID]["From"];
                                table.rows[i+1].cells[1].innerHTML = rateCache[ID]["To"];
                                if(rateCache[ID]["Class"] == EconomicInt){ table.rows[i+1].cells[2].innerHTML = 'Economic'; }
                                if(rateCache[ID]["Class"] == BusinessInt){ table.rows[i+1].cells[2].innerHTML = 'Business'; }
                                if(rateCache[ID]["Type"] == DomesticInt){ table.rows[i+1].cells[3].innerHTML = 'Domestic'; }
                                if(rateCache[ID]["Type"] == InternationalInt){ table.rows[i].cells[3].innerHTML = 'International'; }
                                table.rows[i+1].cells[4].innerHTML = response[i]["Date"];
                                table.rows[i+1].cells[5].innerHTML = rateCache[ID]["Time"];
                                table.rows[i+1].cells[6].innerHTML = TotalPrice;
                                table.rows[i+1].cells[7].innerHTML = response[i]["Adults"];
                                table.rows[i+1].cells[8].innerHTML = response[i]["Children"];
                                table.rows[i+1].cells[9].innerHTML = response[i]["Infants"];
                                table.rows[i+1].cells[10].innerHTML = response[i]["BookingID"];
                                table.rows[i+1].cells[11].innerHTML = '<a href="Javascript:viewTicket(\'' + 
                                                                      JSON.stringify( response[i] ).replace(/"/g, '&quot;') +
                                                                      '\',\'' + JSON.stringify( rateCache[ID] ).replace(/"/g, '&quot;') +
                                                                      '\')">View Ticket</a>';
                                table.rows[i+1].cells[12].innerHTML = '<a href="Javascript:cancelFlight(' + response[i]["BookingID"] +
                                                                      ')">Cancel Flight</a>';
                                if(response[i]["hasReceipt"] == 1){
                                table.rows[i+1].cells[13].innerHTML = '<a href="Javascript:viewReceipt(' + response[i]["BookingID"] +
                                                                      ')">View Receipt</a>';
                                }
                            }
                            
                            changeView('ViewBookedFlights');
                        }
                    })
                    .fail(function(){
                        alert(connectionError);
                    });
            }
        })
        .fail(function(){
            alert(connectionError);
        });
}

function viewTicket( booking, rate ){
    bookingObj = jQuery.parseJSON(booking);
    rateObj = jQuery.parseJSON(rate);
    
    var flightType = '', flightClass = '',
        TotalPrice = rateObj.Price*bookingObj.Adults;
        
    if(rateObj.Class == EconomicInt){ flightClass = 'Economic'; }
    if(rateObj.Class == BusinessInt){ flightClass = 'Business'; }
    if(rateObj.Type == DomesticInt){ flightType = 'Domestic'; }
    if(rateObj.Type == InternationalInt){ flightType = 'International'; }
    
    var source = '<img class="logoIMG" src="./resources/logos/' + rateObj.Airline + 
                 '.png" onerror="this.remove()" /><br>' +
                 '<b>From:</b> ' + rateObj.From + '<br><br>' +
                 '<b>To:</b> ' + rateObj.To + '<br><br>' +
                 '<b>Type:</b> ' + flightType + '<br><br>' +
                 '<b>Class:</b> ' + flightClass + '<br><br>' +
                 '<b>Traveling Date:</b> ' + bookingObj.Date + '<br><br>' +
                 '<b>Totoal Price:</b> ' + TotalPrice + '<br><br>' +
                 '<b>Departure Time:</b> ' + rateObj.Time + '<br><br>' +
                 '<b>Adults:</b> ' + bookingObj.Adults + '<br><br>' +
                 '<b>Children:</b> ' + bookingObj.Children + '<br><br>' +
                 '<b>Infants:</b> ' + bookingObj.Infants + '<br><br>' +
                 '<b>Ticket Number:</b> ' + bookingObj.BookingID + '<br><br>' +
                 '<b>Booked By:</b> ' + bookingObj.Username + '<br><br>';
            
            
            $('#TicketContent').html( source );
            
            $('#ticketClose').off('click')
                             .click( function() { changeView('ViewBookedFlights'); } );
            changeView('TicketSummary');
}

function cancelFlight( id ){
    if(!confirm('Are you sure you want to cancel this booking?'))
        return;
    
    $.post( "cancelFlight.php", { ID: id }, function( response ) {
        try {
            response = jQuery.parseJSON(response);
        } catch(e) {
            response = {};
        }
        
        if(response.error){
            alert(response.error);
        } else if(response.success){
            // Hide the booked flights view so we don't see the table before its finished rendering
            $.when(
                $('.View.Active').fadeOut()
                         .removeClass('Active')
            ).then( function() {
                changeViewBookedFlights();
            });
        } else {
            alert(unexpectedError);
        }
    })
    .fail(function(){
        alert(connectionError);
    });
}

function viewReceipt( id ){
    $.post( "getReceipt.php", { ID: id }, function( response ) {
        try {
            response = jQuery.parseJSON(response);
        } catch(e) {
            response = {};
        }
        
        if(response.error){
            alert(response.error);
        } else if(response.GUID){
            var pdfURL = window.location.pathname + 'resources/receiptCache/' + escape(response.GUID) + '.pdf',
                viewerURL = './Viewer.js/#' + pdfURL;
            window.open(viewerURL);
        } else {
            alert(unexpectedError);
        }
    })
    .fail(function(){
        alert(connectionError);
    });
}
