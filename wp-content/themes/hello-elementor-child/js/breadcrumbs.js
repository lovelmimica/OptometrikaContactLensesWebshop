addLoadEvent( () => {
    if(window.location == "http://localhost/redesign.kontaktne-lece.eu/my-account/reminders/"){
        let breadcrumbNav = document.querySelector("nav.woocommerce-breadcrumb");
        console.log(breadcrumbNav.textContent)
        breadcrumbNav.innerHTML = breadcrumbNav.innerHTML.replace("My account", "<a href='http://localhost/redesign.kontaktne-lece.eu/my-account/'>My account</a>");
    } 
});