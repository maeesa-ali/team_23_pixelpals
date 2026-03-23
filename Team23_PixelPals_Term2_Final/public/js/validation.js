// The login page and signup page share this file, so each block first checks whether its form exists.
const loginForm = document.getElementById("loginForm");
if (loginForm) 
{
    loginForm.addEventListener("submit", function(event)
    {
        // Stop the post if the basic client-side checks fail.
        if (!validateLogin())
        {
            event.preventDefault();
        }
    });
}

// The signup form gets its own validation and admin-toggle behaviour from the same shared file.
const regForm = document.getElementById("regForm");
if (regForm) 
{
    regForm.addEventListener("submit", function(event)
    {
    // Stop the post if the signup fields fail the client-side checks.
    if (!validateRegistration())
    {
        event.preventDefault();
    }
    });

    const adminCheckbox = document.getElementById("adminCheckbox");
    const adminPasswordRow = document.getElementById("adminPasswordRow");
    const adminPassword = document.getElementById("admin_Password");

    if (adminCheckbox && adminPasswordRow && adminPassword) {
        // The admin access-code field is only needed when the user is trying to create an admin account.
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
}

// Keep the auth error output in one place so both forms write to the same on-page message area.
function displayError(message)
{
    document.getElementById("error").textContent = message;
}

// Login only needs a very small validation pass before the server does the real authentication work.
function validateLogin()
{
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    if (!username || !password)
        {
            displayError("Atleast one field is empty");
            return false;
        }

    displayError("");
    return true;
}

// Signup validates the main account fields before the server checks duplicates and writes to the database.
function validateRegistration()
{
    // Pull the current field values once so the checks below stay readable.
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
        return false;
    }

    if (!email.includes("@"))
    {
        displayError("Invalid email format");
        return false;
    } 

    if (password.length < 8)
    {
        displayError("Password must be at least 8 characters long");
        return false;
    } 
    
    if (password !== confirmPassword)
    {
        displayError("Passwords do not match");
        return false;
    }

    // Admin signup needs the extra access-code field as well.
    const adminCheckbox = document.getElementById("adminCheckbox");
    const adminPassword = document.getElementById("admin_Password");
    if (adminCheckbox && adminCheckbox.checked && adminPassword && !adminPassword.value)
    {
        displayError("Admin access code is required");
        return false;
    }

    displayError("");
    return true;
}
