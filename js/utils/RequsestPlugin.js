async function ajax(url, method, payload){
    var options = {type:method, processData:false,contentType: "application/json"};

    if(payload && typeof payload !== "string") {
        options.data = JSON.stringify(payload);
    }

    return new Promise(function(resolve, reject) {

        var xhr = new XMLHttpRequest();
        xhr.open(method,url,true);

        if(payload && typeof payload !== "string") {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            var urlEncodedData = "";
            var urlEncodedDataPairs = [];
            for(var key in payload) {
                urlEncodedDataPairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(payload[key]));
            }
            urlEncodedData = urlEncodedDataPairs.join('&').replace(/%20/g, '+');
            xhr.send(urlEncodedData);

        }else{
            xhr.send(null);
        }

        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE ) {
                if(this.status.toString().match(/2\d{2}/)) {
                    try {
                        var obj = JSON.parse(xhr.responseText);
                        return resolve(obj);
                    } catch (e) {
                        //if the response is not json. we ignored it instead of throw error
                        return resolve({});
                    }
                }else{
                    return reject(new Error("AJAX POST failed due to: " + ((xhr.responseText || xhr.statusText) || "unknown reason")))
                }
            }
        }
    });
}

const RequsestPlugin =  {

    install:async function(Vue, options){

        Vue.prototype.launchRequest = async function(url,method,payload){
            var result = null;
            const baseurl  = options.baseurl || "";
            switch(method){
                case "POST":
                    result = await ajax(baseurl + url,"POST",payload);
                    break;
                case "GET":
                    result = await ajax(baseurl + url,"GET",null);
                    break;
            }
            return result;
        };

    }
}

export {RequsestPlugin}