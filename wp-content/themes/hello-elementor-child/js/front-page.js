addLoadEvent( () => {
    console.log("Front page js loaded");
    let onsaleTabContents = document.querySelectorAll(".onsale-products-section .elementor-tab-content");

    onsaleTabContents.forEach( onsaleTabContent => {
        let dataTab = onsaleTabContent.dataset.tab;
        let tabTitle = document.querySelector(".onsale-products-section .elementor-tab-title[data-tab='" + dataTab + "']");
        if(onsaleTabContent.querySelector(".elementor-posts-nothing-found")){
            if(dataTab == 1) document.querySelector(".onsale-products-section").remove();
            else tabTitle.remove();
        }
    });

    let newTabContents = document.querySelector(".new-products-section .elementor-tab-content");
    if(newTabContents){
        newTabContents.forEach( newTabContent => {
            let dataTab = newTabContent.dataset.tab;
            let tabTitle = document.querySelector(".new-products-section .elementor-tab-title[data-tab='" + dataTab + "']");
            if(newTabContent.querySelector(".elementor-posts-nothing-found")){
                if(dataTab == 1) document.querySelector(".onsale-products-section").remove();
                else tabTitle.remove();
            }
        });
    }
});