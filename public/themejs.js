const htmlTag = document.currentScript.getAttribute('htmlTag');
const endPoint = document.currentScript.getAttribute('endPoint');
var user_data = "";
async function getShopData(shop = null) {    
    const baseURL = 'https://almeapp.co.in/'+endPoint;
    const alme_user_token = localStorage.getItem('alme_user_token');
    const params = {
        "token": alme_user_token,
        "shop": shop
    }
    
    const opts = { 
        method: 'POST', 
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }, 
        body: JSON.stringify(params) 
    }
    
    const response = await fetch(baseURL, opts);
    const body = await response.json();

    return {
        body: body,
        user_token: alme_user_token,
    };
}

window.onload = async function(e) {
    var user_data = await getShopData(Shopify.shop);
    document.getElementById(htmlTag).innerHTML = user_data.body.html;
}