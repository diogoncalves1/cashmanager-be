const HTTP = new XMLHttpRequest;

function getCookie(name) {
    let cookies = document.cookie;
    var cookiesArr = cookies.split(";");
    for (i = 0; i < cookiesArr.length; i++) {
        let cookie = cookiesArr[i].trim();
        if (cookie.indexOf(name) === 0) {
            cookie = cookie.split("=");
            return decodeURIComponent(cookie[1]);
        }
    }
}

function unserialize(serialized) {
    let obj = {};

    // Remover a parte inicial ':5:{' e o final '}'
    serialized = serialized.slice(serialized.indexOf("{") + 1, serialized.lastIndexOf("}"));

    // Dividir pelos pontos e vírgulas (que separa chave-valor)
    let keyValuePairs = serialized.split(';');

    let i = 0;
    while (i < keyValuePairs.length) {
        // Verifique se o par tem 2 partes (chave e valor)
        if (keyValuePairs[i].trim() && keyValuePairs[i + 1].trim()) {
            let key = keyValuePairs[i].match(/s:(\d+):"([^"]+)"/);
            let value = keyValuePairs[i + 1].match(/s:(\d+):"([^"]+)"/);

            // Se encontrou chave e valor válidos, adicione ao objeto
            if (key && value) {
                obj[key[2]] = value[2];
            }
        }
        // Avance para o próximo par chave-valor
        i += 2;
    }

    return obj;
}