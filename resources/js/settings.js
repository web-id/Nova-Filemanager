export function urlCDNCrop(image, sizeX, sizeY) {
    let token = 'ayso32m5n';
    let urlSite = window.location.origin;
    let realImage = urlSite;
    if(image.startsWith("/storage")) {
        realImage += image;
    } else if(image.startsWith("storage")) {
        realImage += '/' + image;
    } else {
        realImage += '/' + 'storage/' + image;
    }
    return 'https://' + token + '.cloudimg.io/crop/' + sizeX + 'x' + sizeY + '/x/' + realImage;
}

export function urlCDNResize(image, type, size) {
    let token = 'ayso32m5n';
    let urlSite = window.location.origin;
    let realImage = urlSite;
    if(image.startsWith("/storage")) {
        realImage += image;
    } else if(image.startsWith("storage")) {
        realImage += '/' + image;
    } else {
        realImage += '/' + 'storage/' + image;
    }
    return 'https://' + token + '.cloudimg.io/' + type + '/' + size + '/x/' + realImage;
}