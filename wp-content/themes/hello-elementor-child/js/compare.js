addLoadEvent( () => {
    var addToCompare = (event) => {
        console.log("Klik");
        let btn = event.currentTarget;
        let productGrid = document.querySelector(".product-grid");
        if(!productGrid){
            productGrid = btn.closest(".elementor-tab-content");
        }
        console.log(productGrid);
        productGrid.style.opacity = 0.5;
        productGrid.classList.add("loader");
        
        console.log(btn);
        let productId = btn.id.substring(8);
        let data = { id: productId };
        let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/compare/add-to-compare";
        let args = {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        };
        
        fetch(url, args)
            .then(response => response.json())
            .then(result => {
                if(result["code"] == 200){
                    btn.removeEventListener("click", addToCompare);
                    btn.textContent = "Vidi usporedbu";
                    btn.href = "http://localhost/redesign.kontaktne-lece.eu/usporedba/";
                    if(productGrid){
                        productGrid.style.opacity = 1;
                        productGrid.classList.remove("loader");
                    }
                    alert("Proizvod dodan u usporedbu");
                }
            });
    }

    let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/compare/get-compared-products";
    let compareArray = null;

    fetch(url)
        .then(response => response.json())
        .then(result => {
            compareArray = result["ids"] ? [...result.ids] : null;
            let compareButtons = document.querySelectorAll(".compare-button-wrapper a.elementor-button" );
            compareButtons.forEach(btn => {
                let productId = btn.id.substring(8);
                if(compareArray && compareArray.includes(productId)){
                    btn.textContent = "Vidi usporedbu";
                    btn.href = "http://localhost/redesign.kontaktne-lece.eu/usporedba/";
                }else{
                    /*btn.addEventListener("click", event => {
                        console.log("Klik");
                        let productGrid = document.querySelector(".product-grid");
                        productGrid.style.opacity = 0.5;
                        productGrid.classList.add("loader");

                        let data = { id: productId };
                        let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/compare/add-to-compare";
                        let args = {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(data)
                        };
                        
                        fetch(url, args)
                            .then(response => response.json())
                            .then(result => {
                                if(result["code"] == 200){
                                    btn.removeEventListener("click");
                                    btn.textContent = "Vidi usporedbu";
                                    btn.href = "http://localhost/redesign.kontaktne-lece.eu/usporedba/";
                                    productGrid.style.opacity = 1;
                                    productGrid.classList.remove("loader");
                                    alert("Proizvod dodan u usporedbu");
                                }
                            });
                    });*/
                    btn.addEventListener("click", addToCompare);
                }
            });      
        });

        let removeFromCompareButtons = document.querySelectorAll(".table__compare_delete-btn");
        removeFromCompareButtons.forEach(btn => {
            btn.addEventListener("click", (event) => {
                let button = event.target;
                let productId = button.dataset.id;
                let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/compare/remove-from-compare?product-id=" + productId;

                fetch(url)
                    .then(response => response.json())
                    .then(result =>{
                        if(result.code == 200){
                            button.parentElement.parentElement.style.display = "none";
                        }
                    });
            });
        });
});