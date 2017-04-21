function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
$(document).ready(function() {
    /**
     * Maximální počet výsledků zobrazených v tabulce
     * @type {number}
     */
    const max_results = 25;

    var editor = CodeMirror.fromTextArea(document.getElementById('query_input'), {
        lineNumbers: true,
        theme: 'dracula',
        mode: 'text/x-mysql'
    });

    if (getCookie('completed') == '') {
        setCookie('completed', JSON.stringify([]), 365);
    }

    //HERE: Functions

    /**
     * Funkce pro vytvoření záhlaví a jednotlivých řádků tabulky
     *
     * @param cols
     * @param data
     */
    function createTables(cols, data) {
        var table = $('#query_res');

        // Hlavička tabulky - názvy sloupců
        var tableContent = '<tr>';
        for (var i = 0; i < cols.length; i++) {
            tableContent += '<th>'+cols[i]+'</th>';
        }
        tableContent += '</tr>';

        // Jednotlivé řádky - data
        var limiter = 0;
        if (data.length > max_results) {
            limiter = data.length - max_results;
        }
        for (var j = limiter; j < data.length; j++) {
            tableContent += '<tr>';
            for (var i = 0; i < cols.length; i++) {
                tableContent += '<td>'+data[j][cols[i]]+'</td>';
            }
            tableContent += '</tr>';
        }
        table.html('<table class="table table-striped">'+tableContent+'</table>');

    }

    /**
     * Funkce pro bindování eventů na jednotlivé šablony
     */
    function createEventsOnTemplatesList() {
        $('a[id^="template_item_"]').on('click', function() {
            $.getJSON('/api.php/?request=get_template_detail&tid='+$(this).data('id')).done(function(data) {
                $('#modal_template_detail').modal('show');
                $('#modal_template_detail_title').html(data.data.title);
                $('#modal_template_detail_content').html(data.data.content);
                $('#modal_template_detail_activate').data('id', data.data.id);
            });
        });
    }

    function getInfoOfTemplates() {
        $.getJSON('/api.php/?request=get_info_of_templates').done(function(data) {
            // Označit aktivní šablonu
            if (data.data.active_tid != undefined) {
                //Elementy
                var e_correct_sql = $('#template_correct_sql');
                var e_status = $('#template_status');
                $('[id^="template_item_"]').removeClass('active');
                $('#template_item_'+data.data.active_tid).addClass('active');
                $('#template_active').html(data.data.active_title);
                $('#template_active_desc').html(data.data.active_content);

                if (data.data.completed.hasOwnProperty(data.data.active_tid)) { // Pokud je splněna aktuální šablona
                    // Změna ikony
                    e_status.html('<span class="glyphicon glyphicon-ok"></span> Splněno');
                    // Vložení správného SQL dotazu a zviditelnění bloku
                    e_correct_sql.html('<pre><code>'+data.data.completed[data.data.active_tid]+'</code></pre><hr />');
                    e_correct_sql.removeClass('hidden');
                    // Přidat do Cookies pro zobrazení splněných v seznamu šablon
                    var completed = JSON.parse(getCookie('completed'));
                    if (completed.indexOf(data.data.active_tid) != -1) {
                        setCookie('completed', JSON.stringify(completed), 365);
                    }
                    else {
                        completed.push(data.data.active_tid);
                        setCookie('completed', JSON.stringify(completed), 365);
                    }
                }
                else {
                    e_status.html('<span class="glyphicon glyphicon-remove"></span> Nesplněno');
                    e_correct_sql.html('');
                    e_correct_sql.addClass('hidden');
                }
            }
            else {
                $('[id^="template_item_"]').removeClass('active');
            }
            // Označit hotové šablony
            var completed = JSON.parse(getCookie('completed'));
            if (completed.length > 0) {
                for (var i = 0; i < completed.length; i++) {
                    var id = completed[i];
                    var ele = $('[id^="template_item_icon_'+id+'"]');
                    ele.addClass('glyphicon');
                    ele.addClass('glyphicon-ok');
                }
            }
            else {
                var ele = $('[id^="template_item_icon_"]');
                ele.removeClass('glyphicon');
                ele.removeClass('glyphicon-ok');
            }
        });
    }

    //HERE: Database

    $('#query_run').on('click', function() {
        var msg = $('#query_messagebox');
        var gif = $('#query_loading');
        var table = $('#query_res');
        var sql = editor.getValue();

        msg.addClass('hidden');
        table.addClass('hidden');
        gif.removeClass('hidden');

        $.getJSON('/api.php/?request=query&sql='+sql).done(function(data) {
            msg.removeClass('hidden');
            table.removeClass('hidden');
            gif.addClass('hidden');

            if (data.code == 0) {
                msg.removeClass('alert-danger');
                msg.addClass('alert-success');
                msg.html('<span class="glyphicon glyphicon-ok"></span> Výsledků: <strong>'+data.data.length+'</strong>');
            }
            else {
                msg.removeClass('alert-success');
                msg.addClass('alert-danger');
                msg.html('<span class="glyphicon glyphicon-exclamation-sign"></span> <strong>'+data.status+':</strong> '+data.data);
            }

            createTables(data.cols, data.data);
            $('#export_json').html(JSON.stringify(data.data));
            getInfoOfTemplates();
        });

    });

    //HERE: Templates

    $.getJSON('/api.php/?request=get_templates').done(function(data) {
        var buffer = '';
        for (var i = 0; i < data.data.length; i++) {
            var id = data.data[i].id;
            buffer += '<a id="template_item_'+id+'" href="#" data-id="'+id+'" class="list-group-item">'+data.data[i].title+'<span class="pull-right" id="template_item_icon_'+id+'"></span></a>';
        }
        $('#template_list').html(buffer);
        createEventsOnTemplatesList();
        getInfoOfTemplates();
    });

    $('#modal_template_detail_activate').on('click', function() {
        $.getJSON('/api.php/?request=activate_template&tid='+$(this).data('id')).done(function(data) {

        });
        $('#modal_template_detail').modal('hide');

        getInfoOfTemplates();
    });

});

