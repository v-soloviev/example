var StreamSocketClient = {
    comet: null,
    url: '',
    channels: [],
    
    init: function(options){
        $.extend(true, StreamSocketClient, options);
        
        if (! StreamSocketClient.channels.length)
            return;
        
        StreamSocketClient.connection();
    },
    connection: function(){
        StreamSocketClient.comet = $.ajax({
            type: 'POST',
            url: StreamSocketClient.url,
            data: {
                'StreamSocketComet': 1,
                'StreamSocketChannels': StreamSocketClient.channels
            },
            headers: {
                'X-StreamSocket': true,
            },
            dataType: 'json',
            timeout: 30000,
            success: StreamSocketClient.parseData,
            error: function(){
                setTimeout(StreamSocketClient.connection, 1000);
            }
        });
    },
    parseData: function(response){
        for (channel in response) {
            var data = response[channel];
            for (k in data) {
                var func = data[k]['handler']
                if (typeof func == 'undefined')
                    continue;
                
                delete(data[k]['handler']);
                StreamSocketClient._invoke(func, StreamSocketClient._toArray(data[k]));
            }    
        }
        
        setTimeout(StreamSocketClient.connection, 1000);
    },
    _toArray: function(obj){
        var result;
        
        if (Object.prototype.toString.call(obj) === '[object Array]')
            return obj;
        
        result = [];
        for (key in obj)
            result.push(obj[key]);
        
        return result;
    },
    _isFunction: function(obj) {
        return Object.prototype.toString.call(obj) === '[object Function]';
    },
    _invoke: function(methodName, params){
        var arr = methodName.split('.');
        var parent = ((function () { return this; }).call(null)); // get global context
        var funcName = arr.pop();
        
        for(var i = 0; i < arr.length; i++) {
            parent = parent[arr[i]];
        }
        
        if (! StreamSocketClient._isFunction(parent[funcName]))
            return false;
        
        return parent[funcName].apply(parent, params);
    }
}