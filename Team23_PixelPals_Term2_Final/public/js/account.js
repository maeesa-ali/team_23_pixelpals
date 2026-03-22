document.addEventListener("DOMContentLoaded", () => 
{
  // Grab the account form controls up front because the rest of this file just toggles their state.
  const accountForm = document.getElementById("accountForm");
  const accountMessage = document.getElementById("accountMessage");
  const editBtn = document.getElementById("editAccountBtn");
  const saveBtn = document.getElementById("saveAccountBtn");
  const cancelBtn = document.getElementById("cancelAccountBtn");

  if (!accountForm || !accountMessage || !editBtn || !saveBtn || !cancelBtn) 
  {
    return;
  }

  // Keep the editable field list in one place so the edit/cancel/save flow stays easy to maintain.
  const fieldIds = ["username", "first_Name", "last_Name", "email", "dob"];
  const fields = fieldIds
    .map((id) => document.getElementById(id))
    .filter(Boolean);

  let originalData = {};

  // Helper to flip the form between read-only and editable mode.
  function setInputsDisabled(disabled) 
  {
    fields.forEach((input) => 
    {
      input.disabled = disabled;
    });
  }

  // This message area gives quick feedback without needing a page reload.
  function setMessage(text, isError = false) 
  {
    accountMessage.textContent = text;
    accountMessage.style.color = isError ? "#B00020" : "#0C6A2A";
  }

  // Snapshot the current form values so cancel can restore them later.
  function readForm() 
  {
    const data = {};
    fields.forEach((input) => 
    {
      data[input.id] = input.value.trim();
    });
    return data;
  }

  // Write a saved snapshot back into the form fields.
  function writeForm(data) 
  {
    fields.forEach((input) => 
    {
      input.value = data[input.id] || "";
    });
  }

  // Reset all temporary border highlighting before a fresh validation pass.
  function clearInputErrors() 
  {
    fields.forEach((input) => 
    {
      input.style.borderColor = "#cccccc";
    });
  }

  // Highlight whichever field failed validation.
  function markError(id) 
  {
    const input = document.getElementById(id);
    if (input) 
    {
      input.style.borderColor = "#D93131";
    }
  }

  // Front-end validation mirrors the basic account rules before the form is submitted.
  function validate(data) 
{
    const errors = {};

    if (!data.username) errors.username = "Username is required.";
    if (!data.first_Name) errors.first_Name = "First name is required.";
    if (!data.last_Name) errors.last_Name = "Last name is required.";

    if (!data.email) 
    {
      errors.email = "Email is required.";
    } 
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) 
    {
      errors.email = "Invalid email.";
    }

    if (data.dob) 
    {
      const chosen = new Date(data.dob);
      const today = new Date();
      chosen.setHours(0, 0, 0, 0);
      today.setHours(0, 0, 0, 0);
      if (chosen > today) 
      {
        errors.dob = "DOB cannot be in the future.";
      }
    }

    return errors;
  }

  // One toggle controls both the input disabled state and the button visibility.
  function setEditing(isEditing) 
  {
    setInputsDisabled(!isEditing);
    editBtn.hidden = isEditing;
    saveBtn.hidden = !isEditing;
    cancelBtn.hidden = !isEditing;
    saveBtn.disabled = !isEditing;
    cancelBtn.disabled = !isEditing;
  }

  // Start the page in read-only mode using the values that came from PHP.
  originalData = readForm();
  setEditing(false);

  // Edit just unlocks the fields and remembers the current values in case the user cancels.
  editBtn.addEventListener("click", () => 
  {
    originalData = readForm();
    clearInputErrors();
    setEditing(true);
    setMessage("Editing details.");
  });

  // Cancel restores the last saved snapshot and returns the form to read-only mode.
  cancelBtn.addEventListener("click", () => 
  {
    writeForm(originalData);
    clearInputErrors();
    setEditing(false);
    setMessage("Changes cancelled.");
  });

  // Before submit, run the same basic client-side checks and keep the form open if anything is wrong.
  accountForm.addEventListener("submit", (event) => 
  {
    setInputsDisabled(false);
    clearInputErrors();

    const data = readForm();
    const errors = validate(data);

    if (Object.keys(errors).length > 0) 
    {
      event.preventDefault();
      setEditing(true);
      Object.keys(errors).forEach(markError);
      setMessage("Please fill required fields correctly.", true);
      return;
    }

    setMessage("Saving account details...");
  });
});
