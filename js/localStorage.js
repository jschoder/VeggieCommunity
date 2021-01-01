vc.localStorage = {
    
    add: function(name, value) {
        if (localStorage) {
            var storageValue = localStorage.getItem(name);
            if (storageValue === null || storageValue === '') {
                var storage = [];
            } else {
                var storage = storageValue.split(',');
            }
            storage.push(value);
            localStorage.setItem(name, storage.toString());
        }
    },
    
    remove: function(name, value) {
        if (localStorage) {
            var storageValue = localStorage.getItem(name);
            if (storageValue != null) {
                var storage = storageValue.split(',');
                for(var i = storage.length - 1; i >= 0; i--) {
                    if(storage[i] === value) {
                       storage.splice(i, 1);
                    }
                }
                localStorage.setItem(name, storage.toString());
            }
        }
    }
};