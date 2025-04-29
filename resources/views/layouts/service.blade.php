{{-- <input type="hidden" name="empid" id="empid" value="{{ ($employee) ? $employee->id : null; }}"> --}}
<input type="hidden" name="usersid" id="usersid" value="{{ Auth::user()->id }}">
<input type="hidden" name="isadmin" id="isadmin" value="{{ Auth::user()->isAdmin }}">
<input type="hidden" name="isusername" id="isusername" value="{{ Auth::user()->username }}">
<script>
    // Riski Maulana Rahman
    admin = $('#isadmin').val();
    baseurl = window.location.origin;
    apiurl = window.location.origin+'/api';
    valusername = $('#valusername').val();
    usersid = parseInt($('#usersid').val());
    empid = parseInt($('#empid').val());

    function store(module) {
        var store = new DevExpress.data.CustomStore({
            key: "id",
            load: function() {
                return sendRequest(apiurl + "/"+module);
            },
            insert: function(values) {
                return sendRequest(apiurl + "/"+module, "POST", values);
            },
            update: function(key, values) {
                return sendRequest(apiurl + "/"+module+"/"+key, "PUT", values);
            },
            remove: function(key) {
                return sendRequest(apiurl + "/"+module+"/"+key, "DELETE");
            },
        });

        return store;
    }

    function storedetail(module,param) {
        var storedetail = new DevExpress.data.CustomStore({
            key: "id",
            load: function() {
                return sendRequest(apiurl + "/"+module+"/"+param);
            },
            insert: function(values) {
                values.req_id = param;
                return sendRequest(apiurl + "/"+module, "POST", values);
            },
            update: function(key, values) {
                return sendRequest(apiurl + "/"+module+"/"+key, "PUT", values);
            },
            remove: function(key) {
                return sendRequest(apiurl + "/"+module+"/"+key, "DELETE");
            },
        });

        return storedetail;
    }

    function storewithmodule(module,modulename,param) {
        var storedetail = new DevExpress.data.CustomStore({
            key: "id",
            load: function() {
                return sendRequest(apiurl + "/"+module+"/"+param+"/"+modulename);
            },
            insert: function(values) {
                values.req_id = param;
                values.modulename = modulename;
                return sendRequest(apiurl + "/"+module, "POST", values);
            },
            update: function(key, values) {
                return sendRequest(apiurl + "/"+module+"/"+key, "PUT", values);
            },
            remove: function(key) {
                return sendRequest(apiurl + "/"+module+"/"+key, "DELETE");
            },
        });

        return storedetail;
    }

    function sendRequest(url, method, data) {
        var d = $.Deferred();
    
        method = method || "GET";

        var csrfToken = '{{ csrf_token() }}';
    
        $.ajax(url, 
        {
            method: method || "GET",
            data: JSON.stringify(data),
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            contentType: 'application/json',
            dataType: 'json',
            cache: false,
            xhrFields: { withCredentials: true }
        }).done(function(result) {

            d.resolve(method === "GET" ? result.data : result);

            var type = (result.status == "success" ? "success" : "error"),
            text = result.message;
            time = (result.status == "success" ? 2000 : 10000)

            // if(method !== "GET" && result.status == "success") {
            //     logSuccess(valusername, method, url, data);
            // } else if(method !== "GET" && result.status == "error") {
            //     logError(valusername, method, url, text);
            // }

            if(result.status == "show" || result.status == 'prompt') {

            } else {
                if(type == 'error') {
                    DevExpress.ui.dialog.alert(text, type);
                } else {
                    DevExpress.ui.notify(text, type, time);
                }
            }
        }).fail(function(xhr) {
            d.reject(xhr.responseJSON ? xhr.responseJSON.Message : xhr.statusText);
        });
    
        return d.promise();
    }

    function sendRequestalt(url, method, data) {
        var d = $.Deferred();
    
        method = method || "GET";

    
        $.ajax(url+'?_token=' + '{{ csrf_token() }}', 
        {
            method: method || "GET",
            data: data,
            headers: {"Accept": "application/json"},
            processData: false,
            contentType: false,
            cache: false,
            xhrFields: { withCredentials: true }
        }).done(function(result) {
            d.resolve(method === "GET" ? result.data : result);
    
            var type = (result.status == "success" ? "success" : "error"),
            text = result.message;
            time = (result.status == "success" ? 2000 : 5000)
    
            // if(method !== "GET" && result.status == "success") {
            //     logSuccess(valusername, method, url, data);
            // } else if(method !== "GET" && result.status == "error") {
            //     logError(valusername, method, url, text);
            // }
            
            if(result.status == "show" || result.status == 'prompt') {
            
            } else {

                DevExpress.ui.notify(text, type, time);
            }
        }).fail(function(xhr) {
            d.reject(xhr.responseJSON ? xhr.responseJSON.Message : xhr.statusText);
        });
    
        return d.promise();
    }

    //List
    function listOption(url,key,sort) {
        action = {
            store: new DevExpress.data.CustomStore({
                key: key,
                loadMode: "raw",
                load: function() {
                    return $.post(apiurl + url);
                }
            }),
            sort: sort
        }
        return action;
    }

    function listOptionWeb(url,key,sort) {
        action = {
            store: new DevExpress.data.CustomStore({
                key: key,
                loadMode: "raw",
                load: function() {
                    return $.get(apiurl + url);
                }
            }),
            sort: sort
        }
        return action;
    }

    function confirmAndSendSubmission(reqid, modelclass, actionForm, valapprovalAction, valApprovalType, valremarks) {
        var btnSubmit = $('#btn-submit');

        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to send this submission?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, send it!'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadingScreen();
                sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                    requestStatus: 1,
                    action: actionForm,
                    approvalAction: (valapprovalAction == null) ? 1 : parseInt(valapprovalAction),
                    approvalType: valApprovalType,
                    remarks: valremarks
                }).then(function (response) {
                    if (response.status == 'error') {
                        btnSubmit.prop('disabled', false);
                        hideLoadingScreen();
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved',
                            text: 'The submission has been submitted.',
                        });
                        popup.hide();
                        hideLoadingScreen();
                    }
                });
            } else {
                btnSubmit.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Cancelled',
                    text: 'The submission has been cancelled.',
                    confirmButtonColor: '#3085d6'
                });
                hideLoadingScreen();
            }
        });
    }

    //log
    function logSuccess(valusername, method, url, data, token) {
        var d = $.Deferred();
    
        var logUrl = window.location.origin+'/api';
    
        $.ajax(logUrl+"/logsuccess", 
        {
            method: "POST",
            data: {user:valusername,url:url,action:method,values:JSON.stringify(data)},
            headers: {"Accept": "application/json","Authorization" : "Bearer "+token},
            cache: false,
        });
    
        return d.promise();
    
    }
    
    function logError(valusername, method, url, text, token) {
        var d = $.Deferred();
    
        var logUrl = window.location.origin+'/api';
    
        $.ajax(logUrl+"/logerror", 
        {
            method: "POST",
            data: {user:valusername,url:url,action:method,values:JSON.stringify(text)},
            headers: {"Accept": "application/json","Authorization" : "Bearer "+token},
            cache: false,
        });
    
        return d.promise();
    
    }
</script>