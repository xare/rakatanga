setCookie = (cookieName, cookieValue, expirationDays) => {
    let date = new Date();
    date.setTime(date.getTime() + (expirationDays * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = cookieName + "=" + cookieValue + ";" + expires + "; path=/";
}

getCookie = (cookieName) => {
    const name = cookieName + "=";
    const cookieDecoded = decodeURIComponent(document.cookie);
    const cookieArray = cookieDecoded.split(";");
    let value;
    cookieArray.forEach(val => {
        if (val.indexOf(name) === 0) value = val.substring(name.length);
    });
    return value;
}

document.querySelector('[data-action="js-cookies-button"]').addEventListener("click", () => {
    document.querySelector('#cookies').style.display = "none";
    setCookie("cookie", true, 90);
});



cookieMessage = () => {


    if (!getCookie("cookie"))
        document.querySelector('#cookies').style.display = "block";
}

window.addEventListener("load", cookieMessage);