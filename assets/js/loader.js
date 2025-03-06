function loader() {
    
    let l = document.getElementById("loader-div");
    l.style.display = "block";
    function disLoader(){
        l.style.display = "none";
    }
    function disableLoader() {
        l.style.opacity = "0";
        setTimeout(disLoader, 500);
    }
    window.addEventListener("load", disableLoader());
}
setTimeout(loader ,500)