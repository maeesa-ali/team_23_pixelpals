// Account page logic (Edit / Save / Cancel)

// Form fields (matching your HTML input ids)
const fieldIds = 
[
  "username",
  "first_Name",
  "last_Name",
  "email",
  "dob",
  "address1",
  "address2",
  "city",
  "postcode",
  "country",
  "old_Password",
  "new_Password",
  "confirm_Password"
];

const fields = fieldIds.map((id) => document.getElementById(id)); // Cache input elements
const message = document.querySelector(".message"); // Message area for feedback

// Your buttons (Edit, Save Changes, Cancel)
const [editBtn, saveBtn, cancelBtn] = document.querySelectorAll(".actions button");

let originalData = {}; // To store original data for canceling edits

// Default empty profile
const defaults = 
{
  username: "",
  email: "",
  first_Name: "",
  last_Name: "",
  dob: "",
  address1: "",
  address2: "",
  city: "",
  postcode: "",
  country: "",
  old_Password: "",
  new_Password: "",
  confirm_Password: ""
};

function setInputsDisabled(disabled) // Enable or disable all form inputs
{
  fields.forEach((input) => {
    input.disabled = disabled;
  });
}

function setMessage(text, isError = false) // Display a message to the user
{
  message.textContent = text;
  message.style.color = isError ? "#B00020" : "#0C6A2A";
}

function readForm() // Read current values from the form into an object
{
  const data = {};
  fieldIds.forEach((id) => 
    {
        data[id] = document.getElementById(id).value.trim(); // Trim whitespace for cleaner data
    });
  return data;
}

function writeForm(data) // Populate form fields with data
{
  fieldIds.forEach((id) => 
    {
        document.getElementById(id).value = data[id] || "";
    });
}

function clearInputErrors() // Reset input borders to default
{
  fields.forEach((input) => 
    {
        input.style.borderColor = "#cccccc";
    });
}

function markError(id) // Highlight an input field with an error
{
  const input = document.getElementById(id);
  input.style.borderColor = "#D93131";
}

function validate(data) // Validate form data and return an object of errors
{
  const errors = {};

  if (!data.username) errors.username = "Username is required.";
  if (!data.first_Name) errors.first_Name = "First name is required.";
  if (!data.last_Name) errors.last_Name = "Last name is required.";

  if (!data.email) errors.email = "Email is required.";
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) errors.email = "Invalid email.";

  if (!data.address1) errors.address1 = "Address line 1 is required.";
  if (!data.city) errors.city = "City is required.";
  if (!data.postcode) errors.postcode = "Postcode is required.";
  if (!data.country) errors.country = "Country is required.";

  if (data.dob) 
  {
    const chosen = new Date(data.dob);
    const today = new Date();
    chosen.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    if (chosen > today) errors.dob = "DOB cannot be in the future.";
  }

  return errors;
}

function init() // Initialize the page with profile data and set up initial state
{
  const profile = {...defaults}; // Start with default values
  originalData = { ...profile };
  writeForm(profile);

  // Start in read-only mode
  setInputsDisabled(true);
  saveBtn.style.display = "none";
  cancelBtn.style.display = "none";
  setMessage("Your personal details", false);
}

// Event listeners for buttons
editBtn.addEventListener("click", () => 
{
  originalData = readForm();
  clearInputErrors();
  setInputsDisabled(false);

  editBtn.style.display = "none";
  saveBtn.style.display = "inline-block";
  cancelBtn.style.display = "inline-block";

  setMessage("Editing details.", false);
});

// Cancel button reverts changes and goes back to read-only mode
cancelBtn.addEventListener("click", () => 
{
  writeForm(originalData);
  clearInputErrors();
  setInputsDisabled(true);

  editBtn.style.display = "inline-block";
  saveBtn.style.display = "none";
  cancelBtn.style.display = "none";

  setMessage("Changes cancelled.", false);
});

// Save button validates input and either shows errors or saves changes
saveBtn.addEventListener("click", () => 
{
  clearInputErrors();
  const data = readForm();
  const errors = validate(data);

  const oldPassword = document.getElementById("old_Password").value;
  const newPassword = document.getElementById("new_Password").value;
  const confirmPassword = document.getElementById("confirm_Password").value;

  if (oldPassword || newPassword || confirmPassword)// If any password fields are filled, validate the password change
  {

    if (newPassword.length < 8) // Check new password length
    {
      setMessage("New password must be at least 8 characters.", true);
      return;
    }

    if (newPassword !== confirmPassword) // Check if new password and confirm password match
    {
      setMessage("New passwords do not match.", true);
      return;
    }
  }

  if (Object.keys(errors).length > 0) // If there are validation errors, mark the fields and show a message
  {
    Object.keys(errors).forEach(markError);
    setMessage("Please fill required fields correctly.", true);
    return;
  }

  if (oldPassword || newPassword || confirmPassword) // If password change is attempted, password is updated
  {
    document.getElementById("old_Password").value = "";
    document.getElementById("new_Password").value = "";
    document.getElementById("confirm_Password").value = "";
  }

  originalData = { ...data };
  setInputsDisabled(true);

  editBtn.style.display = "inline-block";
  saveBtn.style.display = "none";
  cancelBtn.style.display = "none";

  setMessage("Account details saved.", false);
});

init();
