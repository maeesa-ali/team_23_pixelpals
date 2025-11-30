
// Set up products 
const products2 = [
    { name: "Green headphones", description: "Comfortable outer ear headphones for kids", price: 12.00, minAge: 3, maxAge: 10 },
    { name: "Red gaming headset", description: "Ergonomic headset designed for young gamers.", price: 20.00, minAge: 5, maxAge: 14 },
    { name: "Blue gaming controller", description: "Small controller designed to fit in the hands of younger children", price: 18.00, minAge: 5, maxAge: 10 },
    { name: "Yellow and red keyboard", description: "Keyboard with easy to press keys suitable for a variety of ages", price: 15.00, minAge: 3, maxAge: 14 }
];
document.addEventListener("DOMContentLoaded", () => {

    const products = [
        { name: "Green headphones", price: 12.00 },
        { name: "Red gaming chair", price: 20.00 },
        { name: "Blue keyboard", price: 18.00 },
        { name: "Kids gaming headset", price: 15.00 }
    ];


    // Recommended Products 
    function loadRecommended() {
        const grid = document.getElementById("recommendedGrid");
        if (!grid) return;
    
        grid.innerHTML = "";
    
        products2.slice(0, 4).forEach(p => {
            const card = document.createElement("div");
            card.classList.add("rec-card");
            card.innerHTML = `
                <div class="rec-image-placeholder"></div>
                <h4>${p.name}</h4>
                <p>£${p.price.toFixed(2)}</p>
    
                <a href="product.html?name=${encodeURIComponent(p.name)}">
                   <button class="veiw-product">veiw product</button>
                </a>
            `;
            grid.appendChild(card);
        });
    }

    // Main Products Display 
    function displayProducts(list) {
        const grid = document.getElementById("productGrid");
        if (!grid) return;

        grid.innerHTML = "";
        list.forEach(product => {
            const card = document.createElement("div");
            card.classList.add("product-card");
            card.innerHTML = `
                <div class="image-placeholder"></div>
                <h4>${product.name}</h4>
                <p>£${product.price.toFixed(2)}</p>
                <p>Recommended age: ${product.minAge}–${product.maxAge}</p>
                <p>${product.description}</p>
               <a href="product.html?name=${encodeURIComponent(product.name)}">
               <button class="veiw-product">veiw product</button>
               </a>
            `;
            grid.appendChild(card);
        });
    }

    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("searchForm");

    if (searchInput && searchForm) {

        // Pre-fill from URL
        const urlParams = new URLSearchParams(window.location.search);
        const searchTermFromHome = urlParams.get('q') || "";
        searchInput.value = searchTermFromHome;

        // If search came from homepage its filterd immediately
        if (searchTermFromHome) {
            const filtered = products2.filter(p =>
                p.name.toLowerCase().includes(searchTermFromHome.toLowerCase()) ||
                p.description.toLowerCase().includes(searchTermFromHome.toLowerCase())
            );
            displayProducts(filtered);
        } else {
            displayProducts(products2);
        }
        
function setupFilters(products, displayProducts) {

    // Make radio buttons deselectable
    
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


    // Custom price toggle
    const customPriceToggle = document.getElementById("customPriceToggle");
    const customPriceFields = document.getElementById("customPriceFields");
    
 

    function updatePriceFields() {
        if (customPriceToggle.checked) {
            customPriceFields.style.opacity = "1";
            customPriceFields.style.pointerEvents = "auto";
        } else {
            customPriceFields.style.opacity = "0.4";
            customPriceFields.style.pointerEvents = "none";
        }
    }

    document.querySelectorAll("input[name='pricePreset']").forEach(radio => {
        radio.addEventListener("change", updatePriceFields);
    });


    //Custom Age toggle
    const customAgeToggle = document.getElementById("customAgeToggle");
    const customAgeFields = document.getElementById("customAgeFields");

    function updateAgeFields() {
        if (customAgeToggle.checked) {
            customAgeFields.style.opacity = "1";
            customAgeFields.style.pointerEvents = "auto";
        } else {
            customAgeFields.style.opacity = "0.4";
            customAgeFields.style.pointerEvents = "none";
        }
    }

    document.querySelectorAll("input[name='agePreset']").forEach(radio => {
        radio.addEventListener("change", updateAgeFields);
    });


    //Apply filters button
    const applyButton = document.getElementById("applyFilters");
    if (!applyButton) return;

    applyButton.addEventListener("click", () => {

        let filtered = [...products2];

        //PRICE FILTER
        const pricePreset = document.querySelector("input[name='pricePreset']:checked");

        

        let priceMin = 0;
        let priceMax = Infinity;

        if (pricePreset) {

            if (pricePreset.value === "custom") {
                // Use custom price values
                priceMin = parseFloat(document.getElementById("priceFromCustom").value.replace("£", "")) || 0;
                priceMax = parseFloat(document.getElementById("priceToCustom").value.replace("£", "")) || Infinity;

            } else {
                // Handle preset like: "10-20"
                const [min, max] = pricePreset.value.split("-").map(Number);
                priceMin = min;
                priceMax = max;
            }
        }


        //AGE FILTER
        const agePreset = document.querySelector("input[name='agePreset']:checked");

        let ageMin = 0;
        let ageMax = Infinity;

        if (agePreset) {

            if (agePreset.value === "custom") {

                ageMin = parseInt(document.getElementById("minAge").value) || 0;
                ageMax = parseInt(document.getElementById("maxAge").value) || Infinity;

            } else {
                // Preset like "3" means 3+
                ageMin = parseInt(agePreset.value);
                ageMax = Infinity;
            }
        }


        //APPLY FILTERS
        filtered = filtered.filter(p => {

            const priceMatch = p.price >= priceMin && p.price <= priceMax;

            const ageMatch = p.maxAge >= ageMin && p.minAge <= ageMax;

            return priceMatch && ageMatch;
        });


        displayProducts(filtered);
    });
}

        // Search submit
        searchForm.addEventListener("submit", e => {
            e.preventDefault();
            const inputVal = searchInput.value.toLowerCase().trim();
            const words = inputVal.split(/\s+/);

            const filtered = products2.filter(p => {
                const name = p.name.toLowerCase();
                const desc = p.description.toLowerCase();
                return words.some(word =>
                    name.includes(word) ||
                    desc.includes(word)
                );
            });

            displayProducts(filtered);
        });
    }

   

    function detectCategory(product) {
        const name = product.name.toLowerCase();
    
        if (name.includes("headphone")) return "headphones";
        if (name.includes("headset")) return "headsets";
        if (name.includes("controller")) return "controllers";
        if (name.includes("keyboard")) return "keyboards";
    
        return "other"; // anything not matching above
    }
    
    document.querySelectorAll(".cat-btn").forEach(btn => {
        btn.addEventListener("click", () => {
    
            const category = btn.dataset.category;
    
            const filtered = products2.filter(p => detectCategory(p) === category);
    
            displayProducts(filtered);
        });
    });


    // Load recommended products 
    loadRecommended();

    setupFilters(products2, displayProducts);

});

function loadProductPage() {
    const container = document.getElementById("productDetails");
    if (!container) return; // not on product page

    const url = new URLSearchParams(window.location.search);
    const productName = url.get("name");
    if (!productName) {
        container.innerHTML = "<p>No product selected.</p>";
        return;
    }

    const product = products2.find(
        p => p.name.toLowerCase() === productName.toLowerCase()
    );

    if (!product) {
        container.innerHTML = "<p>Product not found.</p>";
        return;
    }

    container.innerHTML = `
        <div class="product-page-card">
            <div class="image-placeholder large"></div>
            <h2>${product.name}</h2>
            <p>${product.description}</p>
            <p><strong>Price:</strong> £${product.price.toFixed(2)}</p>
            <p><strong>Age Range:</strong> ${product.minAge}–${product.maxAge}</p>
            <button class="add-to-basket">Add to basket</button>
        </div>
    `;
}

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("add-to-basket")) {
        const productCard = e.target.closest(".product-page-card");
        if (!productCard) return;

        const name = productCard.querySelector("h2").textContent;
        const description = productCard.querySelector("p").textContent;
        const priceText = productCard.querySelector("p strong:nth-of-type(1)").nextSibling.textContent.trim();
        const price = parseFloat(priceText.replace("£","")) || 0;

        // Get existing basket from localStorage
        let basket = JSON.parse(localStorage.getItem("basket")) || [];

        // Add current product
        basket.push({ name, description, price });

        // Save updated basket
        localStorage.setItem("basket", JSON.stringify(basket));

        alert(`${name} added to basket!`);
    }
});



loadProductPage();
