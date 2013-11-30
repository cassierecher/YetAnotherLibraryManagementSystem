/* Suppression */

//Suppress the keys whose codes are passed as a parameter
var suppressKeys = function(preventionCodes){
	$(window).keydown(function(event){
		if(preventionCodes.indexOf(event.keyCode) > -1){	//if the code is present in the prevention list
			event.preventDefault();
			return false;
		}
	});
};

/* modal toggles */
var logInModal = function(){
    $('#loginModal').modal('toggle');
}

var newBookModal = function(){
    $('#newBookModal').modal('toggle');
}

var editBookModal = function(idToEdit){
    var bookToEdit = bookListViewArray()[idToEdit];
    $('#editBookTitle').val(bookToEdit.Title);
    $('#editBookAuthor').val(bookToEdit.Author);
    $('#editBookPublisher').val(bookToEdit.Publisher);
    $('#editBookIssueDate').val(bookToEdit.Issue_Date);

    currentEditTargetId = bookToEdit.UID;

    $('#editBookModal').modal('toggle');
};

var editBookModalClose = function(){
    $('#editBookModal').modal('toggle');  
};

var newCustomerModal = function(){
    $('#newCustomerModal').modal('toggle');
};

var editCustomerModal = function(idToEdit){    
    var customerToEdit = customerListViewArray()[idToEdit];
    $('#editCustomerFirstName').val(customerToEdit.First_Name);
    $('#editCustomerLastName').val(customerToEdit.Last_Name);
    currentEditTargetId = customerToEdit.UID;

    $('#editCustomerModal').modal('toggle');
};

var editCustomerModalClose = function(){
    $('#editCustomerModal').modal('toggle');  
};

var bookDetailsModal = function(data){

    data = $.parseJSON(data)[0];

    $('#bookDetailsModal').modal();
    $('#bookDetailsHeader').text(data.Title);
    $('#bookDetailsAuthor').text(data.Author);
    $('#bookDetailsPublisher').text(data.Publisher);
    $('#bookDetailsIssueDate').text(data.Issue_Date);
    if(data.BookPDF != "") {
	    var myPDF = new PDFObject({ 

		url: 'uploads/bookpdfs/' + data.BookPDF,
		pdfOpenParams: { zoom: '25', scrollbars: '1', toolbar: '1', navpanes: '1'},
		height: '400px'
    	     }).embed('bookDetailsPDF');
     } else {
	$('#bookDetailsPDF').text("There is no PDF associated with this document."); 
     }
	if (data.BookCover != "") {
		$('#bookDetailsCover').attr('src', 'uploads/bookcovers/' + data.BookCover);
	} else {
		$('#bookDetailsCover').attr('src', 'uploads/bookcovers/comic_sansLOLOLOLOL.png');
	}


    if(data.CID === '0'){
        $('#bookDetailsLocation').text('On the library shelf');
    }
    else{
         $('#bookDetailsLocation').text('Checked out to user with ID ' + data.CID);
    }
};

var bookDetailsModalClose  = function(data){
    $('#bookDetailsModal').modal('toggle');
};


var customerDetailsModal = function(data){
    $('#customerDetailsHeader').text(data.First_Name + ' ' + data.Last_Name);
    $('#customerDetailsAccountCreationDate').text(data.Creation_Date);
    $('#customerDetailsID').text(data.UID);

    checkoutList();
    if(data.Book_List.length == 0){
        checkoutList('None');
    }
    else{
        checkoutList('<ul></ul>');

        for(var k=0; k < data.Book_List.length; k++){
            $.get('backend/viewbook.php?id=' + data.Book_List[k]).then(function(data){
                data = $.parseJSON(data)[0];
                checkoutList(checkoutList().substring(0, checkoutList().length-5) + '<li>' + data.Title + '</li></ul>');
            });
        }
    }

    $('#customerDetailsModal').modal();
};

/* authentication stuff */
var logIn = function(name, pass){
   //try user first, i guess?
    userAndAdminLogin(name, pass).then(function(data){
        //success
            var alertMessage = 'Login attempt failed!';
            if(data === "admin"){
                updateCustomers();
                loggedInUser(false);
                loggedInAdmin(true);
                alertMessage = 'Admin login successful!';
            }
            if(data === "user"){
                loggedInUser(true);
                loggedInAdmin(false);
                alertMessage = 'Login successful!';
            }
        triggerAlert(alertMessage, 'green');
    },
    function(){
        triggerAlert('Login attempt failed!', 'red');
       //failure
    });
};

var loggedInUser = ko.observable(false);
var loggedInAdmin = ko.observable(false);


/*  API access functions
    In general, expect these to return jqXHR objects for use with deferreds.
    This is just a nice wrapper for ugly API calls.
 */

var bookSortCol = 'title';
var bookSortDir = 'ASC';
var bookSearchText = '';
var bookSearchContext = '';

var getBooks = function(){
    if(bookSearchText === ''){
        return $.get('backend/searchbooks.php?col=' + bookSortCol + '&sortMode=' + bookSortDir);
    }
    else{
        return $.get('backend/searchbooks.php?col=' + bookSortCol + '&sortMode=' + bookSortDir + '&q=' + bookSearchText + '&searchField=' + bookSearchContext);
    }
};

var postNewBook = function(title, author, publisher, issuedate){
    return $.post('backend/admin/admin_addbook.php', {t: title, a: author, p: publisher, i: issuedate});
};

var userSortCol = 'last';
var userSortDir = 'ASC';
var userSearchText = '';

var getUsers = function(){
    if(userSearchText === ''){
        return $.get('backend/admin/admin_searchcustomers.php?col=' + userSortCol + '&sortMode=' + userSortDir);
    }
    else{
        return $.get('backend/admin/admin_searchcustomers.php?col=' + userSortCol + '&sortMode=' + userSortDir + '&q=' + userSearchText + '&searchField=id');
    }
};

var postEditBook = function(idnum, title, author, publisher, issuedate){
    return $.post('backend/admin/admin_editbook.php', {id: idnum, t: title, a: author, p: publisher, i: issuedate});
};


var postNewCustomer = function(firstName, lastName, password){
    return $.post('backend/admin/admin_addcustomer.php', {f: firstName, l: lastName, p: password});
};

var postEditCustomer = function(idnum, firstName, lastName){
    return $.post('backend/admin/admin_editcustomer.php', {id: idnum, f: firstName, l: lastName});
};

//tries to log the user in
var userAndAdminLogin = function(uname, pword){
    return $.post('backend/include/login.php',{username: uname, password: pword});
};

var performBookAdd = function(){
    var titl = $('#newBookTitle');
    var auth = $('#newBookAuthor');
    var publ = $('#newBookPublisher');
    var issu = $('#newBookIssueDate');

    var form = new FormData();
    form.append('bookcover', $('#newImageUploader')[0].files[0]);
    form.append('pdf', $('#newPdfUploader')[0].files[0]);
    form.append('t', titl.val());
    form.append('a', auth.val());
    form.append('p', publ.val());
    form.append('i', issu.val());

    $.ajax({
        url: 'backend/admin/admin_addbook.php',
        data: form,
        processData: false,
        contentType: false,
        type: 'POST'
    }).then(function(){
        triggerAlert('Book added successfully.', 'green');
        titl.val('');
        auth.val('');
        publ.val('');
        issu.val('');
        updateBooks();
    },
    function(){
        triggerAlert('Attempt to add book failed!', 'red');
    });
};

currentEditTargetId = -1;

var performBookEdit = function(){
    var titl = $('#editBookTitle');
    var auth = $('#editBookAuthor');
    var publ = $('#editBookPublisher');
    var issu = $('#editBookIssueDate');
    var form = new FormData();

    form.append('bookcover', $('#editImageUploader')[0].files[0]);
    form.append('pdf', $('#editPdfUploader')[0].files[0]);
    form.append('t', titl.val());
    form.append('a', auth.val());
    form.append('p', publ.val());
    form.append('i', issu.val());
    form.append('id', currentEditTargetId);

    $.ajax({
        url: 'backend/admin/admin_editbook.php',
        data: form,
        processData: false,
        contentType: false,
        type: 'POST'
    }).then(function(){
        triggerAlert('Book edited successfully.', 'green');
        titl.val('');
        auth.val('');
        publ.val('');
        issu.val('');
        updateBooks();
    },
    function(){
        triggerAlert('Attempt to edit book failed!', 'red');
    });

};


var performCustomerAdd = function(){
    var first = $('#newCustomerFirstName');
    var last = $('#newCustomerLastName');
    var pass = $('#newCustomerPassword');

    postNewCustomer(first.val(), last.val(), pass.val()).then(function(){
        triggerAlert('Customer added successfully. Username is ' + first.val() + last.val(), 'green');
        first.val('');
        last.val('');
        pass.val('');
        updateCustomers();
    },
    function(){
        triggerAlert('Attempt to add customer failed!', 'red');
    });
};

var performCustomerEdit = function(){
    var first = $('#editCustomerFirstName').val();
    var last = $('#editCustomerLastName').val();

    postEditCustomer(currentEditTargetId, first, last).then(function(){
        triggerAlert('Customer edited successfully.', 'green');
        updateCustomers();
    },
    function(){
        triggerAlert('Attempt to edit customer failed!', 'red');
    });
};

//settings
var keysToSuppress = [13]; //13 = enter


//declare some KO stuff
var bookListViewArray = ko.observableArray();
var customerListViewArray = ko.observableArray();

//valid params for type: strings red, green, blue, yellow
var triggerAlert = function(message, type){

    var alertBox = $('#generalAlert');

    //set the color
    alertBox.removeClass('alert-success alert-info alert-warning alert-danger');
    if(type === 'red')
        alertBox.addClass('alert-danger');
    if(type === 'green')
        alertBox.addClass('alert-success');
    if(type === 'blue')
        alertBox.addClass('alert-info');
    if(type === 'yellow')
        alertBox.addClass('alert-warning');

    //set the message text
    $('#generalAlertText').text(message);

    //show the alert and fade it back in
    alertBox.show();

    window.setTimeout(function(){
       alertBox.hide();
    }, 2500);
};

var updateBooks = function(input){

    getBooks(true).then(function(data){
        bookListViewArray.removeAll();
        data = $.parseJSON(data);
        for(i = 0; i < data.length; i++){
            data[i]['editListOrder'] = 'edit' + i;
            data[i]['deleteListOrder'] = 'delete' + i;
            data[i]['viewListOrder'] = 'view' + i;
            data[i]['checkOutListOrder'] = 'checkout' + i;
            bookListViewArray.push(data[i]);
        }

        //now, update the buttons
        var allEditButtons = $('.bookEditButton');
        var allDeleteButtons = $('.bookDeleteButton');
        var allDetailsLinks = $('.viewDetails');
        var allCheckOutButtons = $('.bookCheckOutButton');

        allEditButtons.click(function(e){
            e.preventDefault();
            var indexToEdit = parseInt(this.id.substring(4));
            editBookModal(indexToEdit);
        });

        allDeleteButtons.click(function(e){
            e.preventDefault();
            //get the number position of the id to remove
            var indexToRemove = parseInt(this.id.substring(6));

            if(bookListViewArray()[indexToRemove].CID != 0){
                triggerAlert('You cannot delete a book that is checked out!', 'red');
            }
            else{
                $.post('backend/admin/admin_deletebook.php', {id: bookListViewArray()[indexToRemove].UID}).then(function(data){
                    //success
                    triggerAlert('Book deleted.', 'yellow');
                    updateBooks();
                },
                function(data){
                    //failure
                    triggerAlert('The delete attempt failed.', 'red');
                });
            }
        });

        allDetailsLinks.click(function(e){
            e.preventDefault();
            var indexToView = parseInt(this.id.substring(4));

            $.get('./backend/viewbook.php?id=' + bookListViewArray()[indexToView].UID).then(function(data){
                //this appears good - need to launch a modal that will display the important stuff all pretty-like
                bookDetailsModal(data);
            });
        });

        allCheckOutButtons.click(function(e){
            e.preventDefault();
            var indexToCheckOut = parseInt(this.id.substring(8));
            console.log(bookListViewArray()[indexToCheckOut].UID);
            $.post('backend/user/user_checkoutbook.php', {b: bookListViewArray()[indexToCheckOut].UID}).then(function(data){
                triggerAlert('Checkout succeeded.', 'green');
                updateBooks();
            },
            function(){
                triggerAlert('Checkout failed!', 'red');
            });
        });

    });
};

var updateCustomers = function(){
    getUsers().then(function(data){
        customerListViewArray.removeAll();
        data = $.parseJSON(data);
        for(i = 0; i < data.length; i++){
            data[i]['editCustListOrder'] = 'editC' + i;
            data[i]['deleteCustListOrder'] = 'deleteC' + i;
            data[i]['viewCustListOrder'] = 'viewC' + i;
            customerListViewArray.push(data[i]);
        }

        //now, update the buttons
        var allEditButtons = $('.custEditButton');
        var allDeleteButtons = $('.custDeleteButton');
        var allDetailsLinks = $('.custViewLink');

        allEditButtons.click(function(e){
            e.preventDefault();
            var indexToEdit = parseInt(this.id.substring(5));
            editCustomerModal(indexToEdit);
        });

        allDeleteButtons.click(function(e){
            e.preventDefault();
            //get the number position of the id to remove
            var indexToRemove = parseInt(this.id.substring(7));

            if(customerListViewArray()[indexToRemove].Book_List.length != 0){
                triggerAlert('You cannot delete a customer that has books out!', 'red');
            }
            else{
                $.post('backend/admin/admin_deletecustomer.php', {id: customerListViewArray()[indexToRemove].UID}).then(function(data){
                    //success
                    triggerAlert('Customer deleted.', 'yellow');
                    updateCustomers();
                },
                function(data){
                    //failure
                    triggerAlert('The delete attempt failed.', 'red');
                });
            }
        });

        allDetailsLinks.click(function(e){
            e.preventDefault();
            var indexToView = parseInt(this.id.substring(5));
            customerDetailsModal(customerListViewArray()[indexToView]);
        });
    });
};


var executeSearch = function(context){

    bookSearchContext = context;
    bookSearchText = $('#searchText').val();
    updateBooks();
};



var bookTabActive = ko.observable(true);
var userTabActive = ko.observable(false);

var checkoutList = ko.observable('');


//execution
$(document).ready(function(){
	//handle key suppression
	suppressKeys(keysToSuppress);

	//activate tabs
	$('#myTab a').click(function (e) {
	e.preventDefault()
	$(this).tab('show')
	});

    //apply handlers to buttons
    $('#loginButton').click(function (e){
        logInModal();
    });

    $('#logoutButton').click(function (e){
        $.get('backend/include/logout.php', function(){
            location.reload();
        });
    });

    $('#performLoginButton').click(function (e){
        logIn($('#loginUsername').val(), $('#loginPassword').val());
        logInModal();
    });

    $('#addBookDropItem').click(function (e){
        newBookModal();
    });

    //performBookAdd
    $('#performBookAddButton').click(function (e){
        performBookAdd();
        //might want to put some validation in here
        newBookModal();
    });

    $('#performBookEditButton').click(function (e){
        performBookEdit();
        //might want to put some validation here
        editBookModalClose();
    });


    $('#addCustomerDropItem').click(function (e){
        newCustomerModal();
    });

    $('#performCustomerAddButton').click(function (e){
        performCustomerAdd();
        //might want to put some validation in here
        newCustomerModal();
    });

    $('#performCustomerEditButton').click(function (e){
        performCustomerEdit();
        //might want to put some validation here
        editCustomerModalClose();
    });


    $('#bookListViewTab').click(function (e){
        bookTabActive(true);
        userTabActive(false);
    });

    $('#userListViewTab').click(function (e){
        bookTabActive(false);
        userTabActive(true);
    });

    /* Sort Handlers */
    $('.bookSorter').click(function(e){
        e.preventDefault();

        var newSortCol = this.id.substring(9); //the id of the element contains the col string

        if(bookSortCol === newSortCol){
            //if the user wants to flip the direction
            if(bookSortDir === "ASC"){
                bookSortDir = "DESC";
            }
            else{
                bookSortDir = "ASC";
            }
        }
        else{
            bookSortCol = newSortCol;
            bookSortDir = "ASC";
        }
        updateBooks();
    });

   $('.userSorter').click(function(e){
        e.preventDefault();

        var newSortCol = this.id.substring(9);

        if(userSortCol === newSortCol){
            //direction flip
            if(userSortDir === 'ASC'){
                userSortDir = 'DESC';
            }
            else{
                userSortDir = 'ASC';
            }
        }
        else{
            userSortCol = newSortCol;
            userSortDir = 'ASC';
        }
        updateCustomers();
    }); 

    /* End Sort Handlers */



    //take care of alert box stuff    
    var alertBox = $('#generalAlert');
    $('#generalAlertClose').click(function(e){
        e.preventDefault();
        alertBox.fadeTo('slow', 0, function(){
            alertBox.hide();
        });
    }); 
    alertBox.hide();    //should be hidden by default


    /* Search Functionality */
    $('#titleSearch').click(function(){
        executeSearch('title');
    });

    $('#authorSearch').click(function(){
        executeSearch('author');
    });

    $('#clearSearch').click(function(e){
        e.preventDefault();
        $('#searchText').val('');
        bookSearchText = '';
        bookSearchContext = '';
        updateBooks();
    });

    $('#userIdSearch').click(function(e){
        userSearchText = $('#userSearchText').val();
        updateCustomers();
    });

    $('#clearUserSearch').click(function(e){
        e.preventDefault();
        $('#userSearchText').val('');
        userSearchText = '';
        updateCustomers();
    });
    /* End Search Functionality */

	//set up knockout variables and bindings
    var viewmod = { bookListViewArray: bookListViewArray, customerListViewArray: customerListViewArray, loggedInUser: loggedInUser, loggedInAdmin: loggedInAdmin, bookTabActive: bookTabActive, userTabActive: userTabActive, checkoutList: checkoutList,
    userIsLoggedIn: ko.computed(function(){
            return this.loggedInUser() || this.loggedInAdmin();
        }),
    userIsNotLoggedIn: ko.computed(function(){
            return (!(this.loggedInUser())) && (!(this.loggedInAdmin()));
        })
     };

    ko.applyBindings(viewmod);

    $.get('backend/include/logout.php');

    updateBooks();
});