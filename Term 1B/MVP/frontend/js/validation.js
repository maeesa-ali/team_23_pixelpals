//login.html handler
const loginForm = document.getElementById("loginForm"); //if on login page, this will exist
if (loginForm) 
{
    loginForm.addEventListener("submit", function(event)
    {
        event.preventDefault(); //stops page reload
        validateLogin(); //calls validation function
    });
}

//login.html handler
const regForm = document.getElementById("regForm"); //if on registration page, this will exist
if (regForm) 
{
    regForm.addEventListener("submit", function(event)
    {
        event.preventDefault(); //stops page reload
        validateRegistration(); //calls validation function
    });
}

//error message display function
function displayError(message)
{
    document.getElementById("error").textContent = message; //displays error message
}

//success message display function
function success()
{
     window.location.href = "index.html"; //redirects to home page
}

function successRegister()
{
     window.location.href = "login.html"; //redirects to login page
}

//login validation function
function validateLogin()
{
    const email = document.getElementById("loginEmail").value.trim(); //gets email input value
    const password = document.getElementById("loginPassword").value; //gets password input value

    if (!email || !password) return displayError("Atleast one field is empty"); //checks for empty fields

    if (!email.includes("@")) return displayError("Invalid email format"); //checks the email field has @ in it

    displayError(""); //clears any previous error messages if validation passes
    success(); //redirects to home page
}

//registration validation function
function validateRegistration()
{
    //get field values from user input
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPassword").value;

    if (!email || !password) return displayError("Atleast one field is empty"); //checks for empty fields

    if (!email.includes("@")) return displayError("Invalid email format"); //checks the email field has @ in it

    if (password.length < 8) return displayError("Password must be at least 8 characters long"); //checks password length

    displayError(""); //clears any previous error messages if validation passes
    successRegister(); //redirects to login page
}
