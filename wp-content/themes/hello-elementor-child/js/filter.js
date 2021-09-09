addLoadEvent(() =>{
    var filterForm = document.querySelector(".product-filter-form");

    //TODO: Do compresive testing

    let filterTitleBlock = document.querySelector(".elementor-widget.filter-title-wrapper");
    if(filterTitleBlock) {
        filterTitleBlock.addEventListener("click", (event) => {
            let screenWidth = window.screen.width;
            if(screenWidth < 768){
                let dropdownIcon = filterTitleBlock.querySelector("i.fas");
                if(filterForm.style.height == "auto"){
                    filterForm.style.height = 0;
                    filterTitleBlock.style.borderBottomLeftRadius = "10px";
                    filterTitleBlock.style.borderBottomRightRadius = "10px";
                    
                    dropdownIcon.classList.remove("fa-times");
                    dropdownIcon.classList.add("fa-angle-down");
                }else{
                    filterForm.style.height = "auto";
                    filterTitleBlock.style.borderBottomLeftRadius = 0;
                    filterTitleBlock.style.borderBottomRightRadius = 0;

                    dropdownIcon.classList.remove("fa-angle-down");
                    dropdownIcon.classList.add("fa-times");
                }
            }
        });
    }

    let doFilter = () => {
        let filteredProducts = localStorage.getItem("filteredProducts") ? JSON.parse(localStorage.getItem("filteredProducts")) : new Object();
        let productCards = document.querySelectorAll("article.type-product");
        if(Object.keys(filteredProducts).length == 0){
            productCards.forEach(productCard => productCard.style.display = "block");
        }else{
           productCards.forEach(productCard => {
                let id = parseInt(productCard.id.substring(5));
                let display = "block";
                Object.keys(filteredProducts).forEach((attributeName) => {
                    if(document.querySelector("input[name='" + attributeName + "']") && Array.isArray(filteredProducts[attributeName]) && filteredProducts[attributeName].length > 0 && !filteredProducts[attributeName].includes(id)) display = "none";
                });
                productCard.style.display = display;
           });
        }
    }

    let checkedFilters = localStorage.getItem("checkedFilters") ? JSON.parse(localStorage.getItem("checkedFilters")) : new Array();
    checkedFilters.forEach(filter => {
        let filterNode = document.querySelector(`input[name="${filter.name}"][value="${filter.value}"]`);
        if(filterNode) filterNode.checked = true;
    });

    doFilter();

    if(filterForm){
        filterForm.addEventListener("change", (event) => {
            let productGrid = document.querySelector(".product-grid");
            productGrid.style.opacity = 0.5;
            productGrid.classList.add("loader");
    
            let attributeName = event.target.getAttribute("name");
            let attributeValue = event.target.value;
            let category = event.currentTarget.dataset.category;
            let checked = event.target.checked;
    
            let checkedFilters = localStorage.getItem("checkedFilters") ? JSON.parse(localStorage.getItem("checkedFilters")) : new Array();
            let checkedFilterObject = { name: attributeName, value: attributeValue };
            
            if(checked == true){
                checkedFilters.push(checkedFilterObject);
            }else{
                checkedFilters = checkedFilters.filter(object => {
                    return !(object.name == attributeName && object.value == attributeValue);
                });
            }
            localStorage.setItem("checkedFilters", JSON.stringify(checkedFilters));
    
            let url = `http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/filter/get-filtered-products?attributeName=${attributeName}&attributeValue=${attributeValue}&category=${category}`;
    
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    let filteredProducts = localStorage.getItem("filteredProducts") ? JSON.parse(localStorage.getItem("filteredProducts").split(",")) : new Object();
                    if(checked == true){
                        if(Array.isArray(filteredProducts[attributeName]) == false) filteredProducts[attributeName] = new Array();

                        filteredProducts[attributeName].push(...result.product_ids);
                    }else{
                        result.product_ids.forEach(id => {
                            let index = filteredProducts[attributeName].indexOf(id);
                            if(index != -1) filteredProducts[attributeName].splice(index, 1);
                        });
                    }
                    localStorage.setItem("filteredProducts", JSON.stringify(filteredProducts));

                    doFilter();
    
                    productGrid.style.opacity = 1;
                    productGrid.classList.remove("loader");
            });
        });

        let formLabels = filterForm.querySelectorAll("label");

        formLabels.forEach(label => label.addEventListener("click", (event) => {
            event.currentTarget.previousElementSibling.click();
        }));
    }
});
