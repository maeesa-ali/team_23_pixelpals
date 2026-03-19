//login.html handler
const loginForm = document.getElementById("loginForm"); //if on login page, this will exist
if (loginForm) 
{
    loginForm.addEventListener("submit", function(event)
    {
        if (!validateLogin()) //calls validation function on form submission, if validation fails, prevent form submission
        {
            event.preventDefault();
        }
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

//login validation function
function validateLogin()
{
    const username = document.getElementById("username").value.trim(); //gets username input value
    const password = document.getElementById("password").value; //gets password input value#

    if (!username || !password)
        {
            displayError("Atleast one field is empty");
            return false; //returns false to prevent form submission
        }

    displayError(""); //clears any previous error messages if validation passes
    return true; //returns true to allow form submission and trigger success handler
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

    displayError(""); //clears any previous error messages if validation passes
    return true; //returns true to allow form submission and trigger success handler
}