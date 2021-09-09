addLoadEvent(() => {
    let doSort = (orderMethod) => {
        let order = localStorage.getItem(orderMethod).split(",");
            
        let switching = true;

        while(switching){
            switching = false;

            let productCards = document.querySelectorAll("article.type-product");

            for(var i = 0; i < productCards.length - 1; i++){
                var shouldSwitch = false;
                let idCurrent = productCards[i].id.substring(5);
                let idNext = productCards[i + 1].id.substring(5);

                let indexCurrent = order.indexOf(idCurrent);
                let indexNext = order.indexOf(idNext);

                if(indexCurrent > indexNext){
                    shouldSwitch = true;
                    break;
                }
            }
            if(shouldSwitch){
                productCards[i].parentNode.insertBefore(productCards[i + 1], productCards[i]);
                switching = true;
            }
        }
    } 

    let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/sort/get-sorted-products";
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if(result.code == 200){
              localStorage.setItem("dateDescOrder", result.date_desc);  
              localStorage.setItem("dateAscOrder", result.date_asc);  
              localStorage.setItem("priceOrder", result.price);  
              localStorage.setItem("popularityOrder", result.popularity);  
            }
        });

    let form = document.querySelector(".sort-form-wrapper form.elementor-form");

    if(form){
        form.addEventListener("change", (event) => {
            let productGrid = document.querySelector(".product-grid");
            productGrid.style.opacity = 0.5;
            productGrid.classList.add("loader");
    
            let order = event.target.value;
    
            if(order == "date-desc"){
                console.log("Sortiraj po datumu silazno");
                doSort("dateDescOrder");
            }else if(order == "date-asc"){
                console.log("Sortiraj po datumu uzlazno");
                doSort("dateAscOrder");
            }else if(order == "price"){
                console.log("Sortiraj po cijeni uzlazno");
                doSort("priceOrder");
            }else if(order == "popularity"){
                console.log("Sortiraj po popularnosti uzlazno");
                doSort("popularityOrder");
            }
    
            productGrid.style.opacity = 1;
            productGrid.classList.remove("loader");
        });
    }
});