addLoadEvent( () => {
    let secondEyeFormEnableCbs = document.querySelectorAll(".show-second-eye-params-cb-wrapper input[type='checkbox']");
    if(secondEyeFormEnableCbs){
        secondEyeFormEnableCbs.forEach(secondEyeFormEnable => {
            let secondEyeForm = document.querySelector(".second-eye-params");
            if(secondEyeFormEnable.checked) secondEyeForm.style.display = "block";

            secondEyeFormEnable.addEventListener("change", (event) => {
                secondEyeForm.style.display = secondEyeFormEnable.checked ? "block" : "none";  
                secondEyeFormEnableCbs.forEach(checkbox => {
                    if(checkbox != secondEyeFormEnable) checkbox.checked = !checkbox.checked;
                });
            });
        });
    }

    let updatePriceNode = (newPriceNode) => {
        let priceNodeWrapper = document.querySelector(".custom-cart-widget a");
        let priceNode = document.querySelector(".custom-cart-widget .woocommerce-Price-amount");
        
        let newPriceNodeDoc = new DOMParser().parseFromString(newPriceNode, "text/html");

        priceNode.remove();
        priceNodeWrapper.appendChild(newPriceNodeDoc.body.firstChild);
    }

    let addToCartButton = document.querySelector("#add-to-cart-button");
    if(addToCartButton){
        addToCartButton.addEventListener("click", () =>{            
            let variationForms = document.querySelector(".variation-form-wrapper .variations_form");
            if(variationForms){
                //First eye params
                let firstEyeForm = document.querySelector(".first-eye-params .variations_form");
                
                let productIdFirst = firstEyeForm.querySelector("input[name='product_id']").value;
                let quantityFirst = firstEyeForm.querySelector("input.qty").value;
                let variationIdFirst = firstEyeForm.querySelector("input[name='variation_id']").value;
    
                if(variationIdFirst == 0){
                    alert("Odaberite sve potrebne parametre kontaktnih leća", "#f90404");
                    return false;
                }
    
                let dataFirst = {
                    product_id: productIdFirst,
                    quantity: quantityFirst,
                    variation_id: variationIdFirst
                };
    
                let argsFirst = {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(dataFirst)
                }
    
                //Second eye params 
                let secondEyeForm = document.querySelector(".second-eye-params");
                if(secondEyeForm.style.display == "block"){
                    let productIdSecond = secondEyeForm.querySelector("input[name='product_id']").value;
                    let quantitySecond = secondEyeForm.querySelector("input.qty").value;
                    let variationIdSecond = secondEyeForm.querySelector("input[name='variation_id']").value;
    
                    if(variationIdSecond == 0){
                        alert("Odaberite sve potrebne parametre kontaktnih leća", "#f90404");
                        return false;
                    }
    
                    let dataSecond = {
                        product_id: productIdSecond,
                        quantity: quantitySecond,
                        variation_id: variationIdSecond
                    };
        
                    var argsSecond = {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(dataSecond)
                    }
                }
    
                let nonce = document.querySelector("meta[name='nonce']");
                let loggedIn = document.querySelector("body.logged-in") ? true : false;
                let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
                let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/cart/add-to-cart" + urlSuffix;
    
                fetch(url, argsFirst)
                .then(response => response.json())
                .then(result => {
                    console.log(secondEyeForm);
                    if(secondEyeForm.style.display == "block"){
                        fetch(url, argsSecond)
                        .then(response => response.json())
                        .then(result => {
                            updatePriceNode(result.price);
                            alert("Proizvod dodan u kosaricu"); 
                        });
                    }else{
                        if(result.code == 200){
                            updatePriceNode(result.price);
                            alert("Proizvod dodan u kosaricu");
                        }else{
                            alert(result.message, "#f90404");
                        }
                    }
                });          
            }else{
                let productId = document.querySelector(".cart button[name='add-to-cart']").value;
                let quantity = document.querySelector(".simple-add-to-cart-wrapper input.qty").value;

                let data = {
                    product_id: productId,
                    quantity: quantity,
                    variation_id: 0
                };

                let args = {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                };

                let nonce = document.querySelector("meta[name='nonce']");
                let loggedIn = document.querySelector("body.logged-in") ? true : false;
                let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
                let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/cart/add-to-cart" + urlSuffix;

                fetch(url, args)
                .then(response => response.json())
                .then(result => {
                    updatePriceNode(result.price);
                    alert("Proizvod dodan u kosaricu");
                });
            }
        });
    }

    let archiveAddToCartButtons = document.querySelectorAll(".product .archive_add_to_cart");
    
    if(archiveAddToCartButtons){
        archiveAddToCartButtons.forEach(archiveAddToCartButton => {
            archiveAddToCartButton.addEventListener("click", event => {
                let productId = archiveAddToCartButton.closest("article.elementor-post").id.substring(5);
                let quantity = 1;
                
                let data = {
                    product_id: productId,
                    quantity: quantity,
                    variation_id: 0
                };

                let args = {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                };

                let nonce = document.querySelector("meta[name='nonce']");
                let loggedIn = document.querySelector("body.logged-in") ? true : false;
                let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
                let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/cart/add-to-cart" + urlSuffix;

                fetch(url, args)
                .then(response => response.json())
                .then(result => {
                    updatePriceNode(result.price);
                    alert("Proizvod dodan u kosaricu");
                });
            });
        });
    }
});

var timeout;

decrementQuantity = (event) => {
    if(timeout !== undefined) clearTimeout(timeout);

    let updateCartButton = document.querySelector(".woocommerce button[name='update_cart']");
    let quantityInput = event.target.parentElement.querySelector(".quantity input[type='number']");
    let min = quantityInput.getAttribute("min");
    let value = quantityInput.value;

    if(value == min ) value = min;
    else if(value == 1) value = 1;
    else value--;

    quantityInput.value = value;
    if(updateCartButton){
        updateCartButton.removeAttribute("disabled");
        timeout = setTimeout(() => updateCartButton.click(), 1000);
    }
}

incrementQuantity = (event) => {
    if(timeout !== undefined) clearTimeout(timeout);

    let updateCartButton = document.querySelector(".woocommerce button[name='update_cart']");
    let quantityInput = event.target.parentElement.querySelector(".quantity input[type='number']");
    let max = quantityInput.getAttribute("max");
    let value = quantityInput.value;
    
    if(value == max) value = max;
    else value++;

    quantityInput.value = value;
    if(updateCartButton){
        updateCartButton.removeAttribute("disabled");
        timeout = setTimeout(() => updateCartButton.click(), 1000);
    } 
}