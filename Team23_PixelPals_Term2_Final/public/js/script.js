let products = [];


// ---------------- FETCH PRODUCTS FROM DATABASE ----------------
fetch("get_products.php")
.then(response => response.json())
.then(data => {

    console.log(data);
    products = data;

    displayProducts(products);
    loadRecommended(products);
    setupFilters(products, displayProducts);

});


// ---------------- FILTER SYSTEM ----------------
function setupFilters(products, displayProducts) {

    function makeDeselectable(name) {
        const radios = document.querySelectorAll(`input[name='${name}']`);
        let lastChecked = null;

        radios.forEach(radio => {
            radio.addEventListener("click", function () {
                if (this === lastChecked) {
                    this.checked = false;
                    lastChecked = null;
                } else {
                    lastChecked = this;
                }
            });
        });
    }

    makeDeselectable("pricePreset");
    makeDeselectable("agePreset");

    const applyButton = document.getElementById("applyFilters");
    if (!applyButton) return;

    applyButton.addEventListener("click", () => {

        let filtered = [...products];

        // PRICE FILTER
        const pricePreset = document.querySelector("input[name='pricePreset']:checked");

        let priceMin = 0;
        let priceMax = Infinity;

        if (pricePreset) {

            if (pricePreset.value === "custom") {

                priceMin = parseFloat(document.getElementById("priceFromCustom").value) || 0;
                priceMax = parseFloat(document.getElementById("priceToCustom").value) || Infinity;

            } else {

                const [min, max] = pricePreset.value.split("-").map(Number);
                priceMin = min;
                priceMax = max;

            }

        }

        // AGE FILTER
        const agePreset = document.querySelector("input[name='agePreset']:checked");

        let ageMin = 0;
        let ageMax = Infinity;

        if (agePreset) {

            if (agePreset.value === "custom") {

                ageMin = parseInt(document.getElementById("minAge").value) || 0;
                ageMax = parseInt(document.getElementById("maxAge").value) || Infinity;

            } else {

                ageMin = parseInt(agePreset.value);

            }

        }

        filtered = filtered.filter(p => {

            const priceMatch = p.Price >= priceMin && p.Price <= priceMax;
            const ageMatch = p.max_age >= ageMin && p.min_age <= ageMax;

            return priceMatch && ageMatch;

        });

        displayProducts(filtered);

    });

}


// ---------------- RECOMMENDED PRODUCTS ----------------
function loadRecommended(products) {

    const grid = document.getElementById("recommendedGrid");
    if (!grid) return;

    grid.innerHTML = "";

    products.slice(0,4).forEach(p => {

        const card = document.createElement("div");
        card.classList.add("rec-card");

        card.innerHTML = `
        <img src="${p.image || 'images/default.png'}" alt="${p.ProductName}" class="product-image"/>
        <h4>${p.ProductName}</h4>
        <p>£${parseFloat(p.Price).toFixed(2)}</p>
        <a href="product.php?id=${p.ProductID}">
            <button class="view-product">view product</button>
        </a>
        `;

        grid.appendChild(card);

    });

}
// ---------------- DISPLAY PRODUCTS ----------------
function displayProducts(list) {

    const grid = document.getElementById("productGrid");
    if (!grid) return;

    grid.innerHTML = "";

    list.forEach(product => {

        const card = document.createElement("div");
        card.classList.add("product-card");

        card.innerHTML = `
        <img src="${product.image || 'images/default.png'}" alt="${product.ProductName}" class="product-image"/>

        <p><strong>${product.ProductName}</strong></p>

        <p>Category: ${product.Category}</p>

        <p>£${parseFloat(product.Price).toFixed(2)}</p>

        <p>Stock: ${product.Stock > 0 ? product.Stock + " available" : "Out of stock"}</p>

        <p>Recommended age: ${product.minAge}–${product.maxAge}</p>

        <p>${product.Description}</p>

        <a href="product.php?id=${product.ProductID}">
            <button class="view-product">view product</button>
        </a>
        `;

        grid.appendChild(card);

    });

}

// ---------------- SEARCH ----------------
document.addEventListener("DOMContentLoaded", () => {

    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("searchForm");

    if (searchInput && searchForm) {

        searchForm.addEventListener("submit", e => {

            e.preventDefault();

            const inputVal = searchInput.value.toLowerCase().trim();
            const words = inputVal.split(/\s+/);

            const filtered = products.filter(p => {

                const name = p.ProductName.toLowerCase();
                const desc = p.Description.toLowerCase();

                return words.some(word =>
                    name.includes(word) || desc.includes(word)
                );

            });

            displayProducts(filtered);

        });

    }

});


// ---------------- CATEGORY FILTER ----------------
function detectCategory(product) {

    const name = product.name.toLowerCase();

    if (name.includes("Ergonomic Chairs")) return "Ergonomic Chairs";
    if (name.includes("Gaming Desks")) return "Gaming Desks";
    if (name.includes("Moniter Stands")) return "Moniter Stands";
    if (name.includes("Wrist And Arm Support")) return "Wrist And Arm Support";
    if (name.includes("Gaming Accsessories")) return "Gaming Accsessoriest";
    

    return "other";

}


document.querySelectorAll(".cat-btn").forEach(btn => {

    btn.addEventListener("click", () => {

        const category = btn.dataset.category;

        const filtered = products.filter(p => detectCategory(p) === category);

        displayProducts(filtered);

    });

});


// ---------------- PRODUCT PAGE ----------------
function loadProductPage() {

    const container = document.getElementById("productDetails");
    const mainImage = document.getElementById("mainImage");
    const thumbnailContainer = document.getElementById("thumbnailContainer");

    if (!container) return;

    const url = new URLSearchParams(window.location.search);
    const productName = url.get("name");

    if (!productName) {
        container.innerHTML = "<p>No product selected.</p>";
        return;
    }

    const product = products.find(
        p => p.Product.Name.toLowerCase() === ProductName.toLowerCase()
    );

    if (!product) {
        container.innerHTML = "<p>Product not found.</p>";
        return;
    }

    mainImage.src = product.image;
    mainImage.alt = product.ProductName;

    thumbnailContainer.innerHTML = "";

    for (let i = 1; i <= 4; i++) {

        const thumb = document.createElement("img");

        thumb.src = product.image;
        thumb.classList.add("thumb");

        thumb.addEventListener("click", () => {

            mainImage.src = thumb.src;

        });

        thumbnailContainer.appendChild(thumb);

    }

    container.innerHTML = `

    <div class="product-page-card">

    <h2>${product.ProductName}</h2>

    <p>${product.Description}</p>

    <p><strong>Category:</strong> ${product.Category}</p>

    <p><strong>Stock:</strong> ${product.Stock}</p>

    <p><strong>Price:</strong> £${parseFloat(product.Price).toFixed(2)}</p>

    <p><strong>Age Range:</strong> ${product.min_age}–${product.max_age}</p>

    <button class="view-product">Add to basket</button>

    </div>

    `;

}


loadProductPage();
