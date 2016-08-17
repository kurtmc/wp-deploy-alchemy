function php(url, data) {
	var res;
	jQuery.ajax({
		url: url,
		data: data,
		type: "POST",
		async: false, // this makes the ajax-call blocking
		dataType: 'json',
		success: function (response) {
			res = response;
		},
		error: function (response, error) {
			console.log(response);
			console.log(error);
			console.log("Failed because: " + error);
		}

	});
	return res;
}

function validateForm() {
	// Check if logged in
	var loggedIn = php("../../content/customisations/change_password/check_login.php")["valid"];
	console.log(loggedIn);
	if (!loggedIn) { // If not logged in redirect to login page
		window.location = "..";
		alert("You must login first!");
		return false;
	}

	// Check password is correct
	var cur = document.forms["myForm"]["current"].value;
	var valid = php("../../content/customisations/change_password/check_password.php", {"password": cur})["valid"];
	if (!valid) {
		alert("Current password is not correct! Please re-enter your current password and try again.");
		return false;
	}

	// Check matching passwords
	var pass = document.forms["myForm"]["password"].value;
	var conf = document.forms["myForm"]["confirm"].value;
	if (pass == null || pass == "" || (pass != conf)) {
		alert("New password and confirm password must match!");
		return false;
	}
	return true;
}
