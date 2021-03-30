export default (base64, mime, fileName) => {
  mime = mime || '';

  const sliceSize = 1024;

  const byteChars = window.atob(base64.split(',')[1]);
  const byteArrays = [];

  for (let offset = 0, len = byteChars.length; offset < len; offset += sliceSize) {
    let slice = byteChars.slice(offset, offset + sliceSize);

    let byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    let byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
  }

  const file = new Blob(byteArrays, {type: mime});

  file.name = fileName;

  return file;
}
