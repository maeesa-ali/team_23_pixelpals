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

//register.html handler
const regForm = document.getElementById("regForm"); //if on registration page, this will exist
if (regForm) 
{
    regForm.addEventListener("submit", function(event) //calls validation function on form submission
    {
    if (!validateRegistration()) //if validation fails, prevent form submission
    {
        event.preventDefault();
    }
    });

    const adminCheckbox = document.getElementById("adminCheckbox");
    const adminPasswordRow = document.getElementById("adminPasswordRow");
    const adminPassword = document.getElementById("admin_Password");

    adminCheckbox.addEventListener("change", function () 
    {
        if (this.checked) 
        {
            adminPasswordRow.style.display = "block";
            adminPassword.required = true;
        } 
        else 
        {
            adminPasswordRow.style.display = "none";
            adminPassword.required = false;
            adminPassword.value = "";
        }
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
    localStorage.setItem("isLoggedIn", "true");
    window.location.href = "index.html"; //redirects to home page
}

function successRegister(email)
{
    syncAccountEmail(email); //syncs the email to localStorage for use across pages
     window.location.href = "login.html"; //redirects to login page
}

//login validation function
function validateLogin()
{
    const username = document.getElementById("username").value.trim(); //gets username input value
    const password = document.getElementById("password").value; //gets password input value#
    const savedPassword = localStorage.getItem("password"); //retrieves the saved password from localStorage
    const savedUsername = localStorage.getItem("username"); //retrieves the saved username from localStorage

    if (username !== savedUsername || password !== savedPassword) //checks if the entered username and password match the saved credentials
    {
        displayError("Incorrect username or password");
        return false; //returns false to prevent form submission
    }

    if (!username || !password)
        {
            displayError("Atleast one field is empty");
            return false; //returns false to prevent form submission
        }

    displayError(""); //clears any previous error messages if validation passes
    success(); //redirects to home page
}

//registration validation function
function validateRegistration()
{
    //get field values from user input
    const username = document.getElementById("username").value.trim();
    const firstName = document.getElementById("first_Name").value.trim();
    const lastName = document.getElementById("last_Name").value.trim();
    const dob = document.getElementById("dob").value;
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_Password").value;
    const adminPassword = document.getElementById("admin_Password").value;
    const adminCheckbox = document.getElementById("adminCheckbox");

    if (!username || !firstName || !lastName || !dob || !email || !password || !confirmPassword)
    {
        displayError("Atleast one field is empty");
        return false; //returns false to prevent form submission
    }

    if (!email.includes("@"))
    {
        displayError("Invalid email format");
        return false; //returns false to prevent form submission
    } 

    if (password.length < 8)
    {
        displayError("Password must be at least 8 characters long");
        return false; //returns false to prevent form submission
    } 
    
    if (password !== confirmPassword)
    {
        displayError("Passwords do not match");
        return false; //returns false to prevent form submission
    }

    if (adminCheckbox.checked && adminPassword != "password")
    {
        displayError("Admin password is incorrect");
        return false; //returns false to prevent form submission
    }

    localStorage.setItem("password", password); //stores the password in localStorage
    localStorage.setItem("username", username); //stores the username in localStorage

    displayError(""); //clears any previous error messages if validation passes
    return true; //returns true to allow form submission and trigger success handler
    successRegister(email); //redirects to login page
}

const ACCOUNT_STORAGE_KEY = "accountProfile"; //key for storing account profile in localStorage
const AUTH_EMAIL_KEY = "currentUserEmail"; //key for storing current authenticated user's email in localStorage

function syncAccountEmail(email) //syncs the email to localStorage for use across pages
{
    const trimmedEmail = email.trim();
    if (!trimmedEmail) return;

    localStorage.setItem(AUTH_EMAIL_KEY, trimmedEmail); //stores the current user's email in localStorage

    let profile = {};
    try 
    {
        profile = JSON.parse(localStorage.getItem(ACCOUNT_STORAGE_KEY)) || {}; //retrieves the account profile from localStorage or initializes it as an empty object
    } 
    catch 
    {
        profile = {};
    }

    if (!profile.email) //if the profile doesn't already have an email, set it and save back to localStorage
    {
        profile.email = trimmedEmail;
        localStorage.setItem(ACCOUNT_STORAGE_KEY, JSON.stringify(profile)); //saves the updated profile back to localStorage
    }
}
