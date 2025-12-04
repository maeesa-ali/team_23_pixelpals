let basket = JSON.parse(localStorage.getItem("basket")) || []; //retrieves basket from local storage

//Renders the basket items
function renderBasket()
{
    const basketContainer = document.getElementById("basketItems"); //container for basket items
    basketContainer.innerHTML = ""; //clears existing items

    if ( basket.length === 0)
    {
        basketContainer.innerHTML = "<p>Your basket is empty.</p>"; //displays empty basket message
        updateTotals(); //updates totals to zero
        return;
    }

    basket.forEach(item => //iterates through each item in the basket
    {
        basketContainer.innerHTML += //adds each item to the basket container
        `
            <div class="itemCard" data-id="${item.id}"> 
                <img src="${item.image}" alt="Product"> <!-- Product image -->

                <div class="itemInfo">
                    <h3>${item.name}</h3> <!-- Product name -->

                   <div class="quantityControls">
                        <button class="decreaseQty">-</button> <!-- Decrease quantity button -->
                        <span>${item.quantity}</span> <!-- Product quantity -->
                        <button class="increaseQty">+</button> <!-- Increase quantity button -->
                    </div>
                </div>

                <div class="itemPrice">£${item.price.toFixed(2)}</div> <!-- Product price -->
            </div>
        `;
    });

    updateTotals();
}

function updateTotals() //updates subtotal and total prices
{
    let subtotal = 0;

    basket.forEach(item => //iterates through each item in the basket
    {
        subtotal += item.price * item.quantity; //calculates subtotal
    });

    //updates subtotal and total display
    document.getElementById("subtotal").textContent = "£" + subtotal.toFixed(2);
    document.getElementById("delivery").textContent = "£0.00";
    document.getElementById("total").textContent = "£" + subtotal.toFixed(2);
}

function saveBasket() //saves the basket to local storage
{
    localStorage.setItem("basket", JSON.stringify(basket));
}

document.addEventListener("click", function(e) //handles quantity button clicks
{
    const card = e.target.closest(".itemCard"); //finds the closest item card
    if (!card) return;

    const id = parseInt(card.getAttribute("data-id")); //gets the item id
    let item = basket.find(i => i.id === id); //finds the item in the basket

    // + button
    if (e.target.classList.contains("increaseQty"))
    {
        item.quantity++;
    }

    // - button
    if (e.target.classList.contains("decreaseQty"))
    {
        item.quantity--;
        if (item.quantity <= 0) //removes item if quantity is zero
        {
            basket = basket.filter(item => item.id !== id);
        }
    }

    saveBasket();
    renderBasket();
});

// Example items to populate basket for demonstration
if (basket.length === 0) {
    basket = [
        {
            id: 1,
            name: "Example Product 1",
            price: 19.99,
            quantity: 1,
            image: "https://bluemoji.io/cdn-proxy/646218c67da47160c64a84d5/671ffb924cdb9c1818e2724c_100.png"
        },
        {
            id: 2,
            name: "Example Product 2",
            price: 29.99,
            quantity: 2,
            image: "https://i.etsystatic.com/21680765/r/il/6f8fc0/2717896892/il_1080xN.2717896892_plui.jpg"
        }
    ];

    saveBasket();
}

renderBasket();