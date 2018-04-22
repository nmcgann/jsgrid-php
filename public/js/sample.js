$(function() {

    function escapeHTML (s, noEscapeQuotes) {
        var map = { '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'};
            return s.replace(noEscapeQuotes ? /[&<>]/g : /[&<>'"]/g, function(c) {
                return map[c];
            });
    }
    var escapeCell = function(value, item){
        return $("<td>").append(escapeHTML(value));
    };

    $.ajax({
        type: "GET",
        url: "/jsgrid/countries/"
    }).done(function(countries) {

        countries.unshift({ id: "0", name: "" });

        var grid = $("#jsGrid").jsGrid({
            height: "80%",
            width: "100%",
            filtering: true,
            inserting: true,
            editing: true,
            sorting: true,
            paging: true,
            pageLoading: true,
            autoload: true,
            pageSize: 10,
            pageButtonCount: 5,
            deleteConfirm: "Do you really want to delete client?",
            controller: {
                loadData: function(filter) {
                    var d = $.Deferred();
                    
                    $.ajax({
                        type: "GET",
                        url: "/jsgrid/clients/",
                        data: filter
                    }).done(function(response) {
                        d.resolve(response);
                    }).fail(function(response) {
                        console.log(response.responseText);
                        alert('fail to fetch data');
                        d.reject();
                    });
                    
                    return d.promise();
                },
                insertItem: function(item) {
                    var d = $.Deferred();
                    $.ajax({
                        type: "POST",
                        url: "/jsgrid/clients/",
                        data: item
                    }).done(function(response) {
                        d.resolve(response);
                    }).fail(function(response) {
                        console.log(response.responseText);
                        alert('fail to insert new');
                        d.reject();
                    });
                    
                    return d.promise();
                },
                updateItem: function(item) {
                    var d = $.Deferred();
                    
                    $.ajax({
                        type: "PUT",
                        url: "/jsgrid/clients/",
                        data: item
                    }).done(function(response) {
                        d.resolve();
                    }).fail(function(response) {
                        console.log(response.responseText);
                        alert('fail to update');
                        d.reject();
                    });
                    
                    return d.promise();
                },
                deleteItem: function(item) {
                    var d = $.Deferred();
                    
                    $.ajax({
                        type: "DELETE",
                        url: "/jsgrid/clients/",
                        data: item
                    }).done(function(response) {
                        d.resolve();
                    }).fail(function(response) {
                        console.log(response.responseText);
                        alert('fail to delete');
                        d.reject();
                    });
                    
                    return d.promise();
                }
            },
            fields: [
                { name: "name", title: "Name", type: "text", width: 150, cellRenderer: escapeCell },
                { name: "age", title: "Age", type: "number", width: 50, filtering: false },
                { name: "address", title: "Address", type: "text", width: 200, cellRenderer: escapeCell },
                { name: "country_id", title: "Country", type: "select", width: 100, items: countries, valueField: "id", textField: "name" },
                { name: "married", type: "checkbox", title: "Is Married", sorting: false, filtering: false },
                { type: "control" }
            ]
        });

    }).fail(function(response) {
            console.log(response.responseText);
                alert('fail to read countries');
    });


});