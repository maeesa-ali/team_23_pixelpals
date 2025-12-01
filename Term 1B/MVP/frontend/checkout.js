let basket = JSON.parse(localStorage.getItem("basket")) || []; //retrieves basket from local storage

function renderSummaryy() //renders order summary on checkout page
{
    const container = document.getElementById("summaryItems"); //container for summary items
    container.innerHTML = ""; //clears existing items

    let subtotal = 0;

    basket.forEach(item => //iterates through each item in the basket
    {
        subtotal += item.price * item.quantity; //calculates subtotal

        container.innerHTML +=
        `
            <div class="summaryItem">
                <img src="${item.image}" alt="Product"> <!-- Product image -->
                
                <div class="summaryItemInfo">
                    <strong>${item.name}</strong> <!-- Product name -->
                    <span>Quantity: ${item.quantity}</span> <!-- Product quantity -->
                </div>

                <div class="summaryItemPrice">£${(item.price.toFixed(2))}</div> <!-- Product price -->
            </div>
        `;
    });

    document.getElementById("subtotal").textContent = "£" + subtotal.toFixed(2); //updates subtotal display
    document.getElementById("delivery").textContent = "£0.00";
    document.getElementById("total").textContent = "£" + subtotal.toFixed(2); //updates total display
    document.getElementById("finalTotal").textContent = "£" + subtotal.toFixed(2); //updates final total display
}

document.querySelectorAll(".clearButton").forEach(button => //adds event listeners to clear buttons
{
    button.addEventListener("click", () => //on click
    {
        const target = document.getElementById(button.dataset.target); //gets target input field
        if (target) {target.value ="";} //clears the input field if it exists
    });
});

renderSummaryy();

//once checkout button is clicked, fields are validated
document.getElementById("placeOrderButton").addEventListener("click", validateCheckout);

function showError(message) //prints error message to user
{
    document.getElementById("checkoutError").textContent = message;
}

function validateCheckout()
{
    //get field values from user input
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phoneNumber").value.trim();
    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const address = document.getElementById("address").value.trim();
    const cardNumber = document.getElementById("cardNumber").value.trim();
    const expiry = document.getElementById("expiryDate").value.trim();
    const cvc = document.getElementById("CVC").value.trim();

    //If fields are empty
    if (!email || !phone || !firstName || !lastName || !address || !cardNumber || !expiry || !cvc)
    {
        return showError("Atleast one field is empty");
    }

    //If email does not include '@' or '.'
    if (!email.includes("@") || !email.includes("."))
    {
        return showError("Invalid email format");
    }

    //if phone number is not 10-15 digits long
    if (phone.length < 10 || phone.length > 15 || !/^\d+$/.test(phone))
    {
        return showError("Invalid phone number");
    }

    //If card number is not 16 digits long
    if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber))
    {
        return showError("Invalid card number");
    }

    //if expiry date is not in MM/YY format
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry))
    {
        return showError("Invalid expiry date format");
    }

    //if CVC is not 3 digits long
    if (cvc.length !== 3 || !/^\d+$/.test(cvc))
    {
        return showError("Invalid CVC");
    }

    //If all validations pass
    showError(""); //clear any previous error messages

    //Proceed with checkout process (not implemented here)
    alert("Checkout successful!"); //placeholder alert
}
