
// Put the first result of a Google Image search within the tag with the specified id
var retrieveGoogleImage = function (search, id)
{

    let options =
    {
        cx: Google_cx,
        q: search,
        searchType: 'image',
        fileType: 'jpg',
        key: Google_key
    };
    makeGoogleAPIRequest(options, function (data)
    {
        let url = getImageUrl(data);
        document.getElementById(id).innerHTML = '<img class="rotate" src=\'' + url + '\' height="200" width="300" >';
    })
};

// Call the Google custom search REST API with the defined options, using a callback function to handle the result
let makeGoogleAPIRequest = function (options, callback)
{
    let url, xhr, item, first;

    url = "https://www.googleapis.com/customsearch/v1";
    first = true;

    for (item in options)
    {
        if (options.hasOwnProperty(item))
        {
            url += (first ? "?" : "&") + item + "=" + options[item];
            first = false;
        }
    }

    xhr = new XMLHttpRequest();
    xhr.onload = function ()
    {
        callback(this.response);
    };
    xhr.open('get', url, true);
    xhr.send();

};

let getImageUrl = function (json)
{
    let object = JSON.parse(json);
    // Check if the Google API key is still valid in the free tier
    if (object.hasOwnProperty('items'))
    {
        let url = object.items[0].link;
        imageExists(url, function (exists)
        {
            if (!exists)
                this.src = object.items[1].link
        });
        return url;
    }
    // Default image, meaning the google API free tier has expired, change API key
    return 'http://assets.bubblear.com/wp-content/uploads/2016/07/06124753/empty-wallet.jpg';
};

let imageExists = function (url, callback)
{
    let img = new Image();
    img.onload = function ()
    {
        callback(true);
    };
    img.onerror = function ()
    {
        callback(false);
    };
    img.src = url;
};