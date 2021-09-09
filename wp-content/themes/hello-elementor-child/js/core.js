function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
      window.onload = func;
    } else {
      window.onload = function() {
        if (oldonload) {
          oldonload();
        }
        func();
      }
    }
  }

window.alert = (message, backgroundColor = "#234595") => {
    let messageBox = document.querySelector("#message-box");
    messageBox.style.zIndex = 5000;
    messageBox.innerHTML = message;
    messageBox.style.backgroundColor = backgroundColor;
    messageBox.style.opacity = 100;
    setTimeout(() => {
      messageBox.style.opacity = 0
      messageBox.style.zIndex = 0;
    }, 4000);
}