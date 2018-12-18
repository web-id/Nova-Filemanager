export function urlCDNCrop(image, sizeX, sizeY) {
    let token = 'ayso32m5n';
    let urlSite = window.location.origin;
    return 'https://'+token+'.cloudimg.io/crop/'+sizeX+'x'+sizeY+'/x/'+urlSite+image;
}