$.fn.colorize = function(rgb, flat) {
    var img = this.get(0);

    if(this.is('img')) {
        try {
            var canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;

            var context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, img.width, img.height);

            var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            var pixels = imageData.data;
            for (var i = 0, il = pixels.length; i < il; i += 4) {

                if(flat === true) {
                    pixels[i] = rgb['r'];
                    pixels[i + 1] = rgb['g'];
                    pixels[i + 2] = rgb['b'];
                }
                else {
                    pixels[i] = pixels[i] * rgb['r'] / 255;
                    pixels[i + 1] = pixels[i + 1] * rgb['g'] / 255;
                    pixels[i + 2] = pixels[i + 2] * rgb['b'] / 255;
                }
            }

            context.putImageData(imageData, 0, 0);
            return canvas;
        }
        catch(e) {
            console.log(e);
        }

    }

    return null;

}