addLoadEvent( () => {
    let repeatLastOrderBtn = document.querySelector(".button__repeat-last-order_wrapper");
    repeatLastOrderBtn.addEventListener("click", () =>{
        let nonce = document.querySelector("meta[name='nonce']");
        let loggedIn = document.querySelector("body.logged-in") ? true : false;
        let urlSuffix = loggedIn ? "?_wpnonce=" + nonce.content : "";
        let url = "http://localhost/redesign.kontaktne-lece.eu/wp-json/v1/cart/repeat-last-order" + urlSuffix;
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if(result.code == 200){
                    window.location.href = "http://localhost/redesign.kontaktne-lece.eu/cart";
                }else if(result.code == 403){
                    window.location.href = "http://localhost/redesign.kontaktne-lece.eu/my-account/";
                }else if(result.code == 404){
                    alert(result.message, "#f90404");
                }
            }); 
    });
});