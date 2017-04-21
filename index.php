<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Vyzkoušej si svůj SQL um!</title>
        <link rel="stylesheet" href="./css/bootstrap.min.css"/>
        <link rel="stylesheet" href="./css/codemirror.css"/>
        <link rel="stylesheet" href="./theme/dracula.css"/>
    </head>
    <body>
        <div class="container" style="margin-top: 12px;">
            <div class="row">
                <!-- Query | Table | Export -->
                <div class="col-md-9">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 8px;">
                        <li role="presentation" class="active"><a href="#tab_query" aria-controls="tab_query" role="tab" data-toggle="tab">Dotaz</a></li>
                        <li role="presentation"><a href="#tab_export" aria-controls="tab_export" role="tab" data-toggle="tab">Export</a></li>
                        <li role="presentation"><a href="#tab_about" aria-controls="tab_about" role="tab" data-toggle="tab">O aplikaci</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="tab_query">
                            <div>
                                <label for="query_input" class="sr-only">SQL Dotaz:</label>
                                <textarea class="" id="query_input">SELECT * FROM employees;</textarea>
                            </div>
                            <div class="text-center" style="margin-top: 5px;">
                                <button id="query_run" class="btn-lg btn-success">Spustit</button>
                            </div>
                            <hr />
                            <!-- Loading gif -->
                            <div class="text-center hidden" id="query_loading">
                                <img src="lupa1s.svg" /></div>
                            <!-- Message Box - Alers -->
                            <div class="alert" id="query_messagebox"></div>
                            <!-- Table - Data result -->
                            <div id="query_res"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_export">
                            <pre><code id="export_json"></code></pre>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_about">
                            <h2>O Aplikaci</h2>
                            <p>
                                Aplikace byla vytvořena za účelem procvičení SQL, pro studenty. Zda-li se dále bude rozvíjet, není jisté ale pokud by někdo měl zájem
                                aplikace je úmístěna na GitHub.
                                <!-- TODO: Doplnit link na git -->
                            </p>
                            <p>
                                Tuto open-source webovou aplikaci jsem vytvořil já Jakub Janeček roku 2017.
                            </p>
                            <h2>Omezení</h2>
                            <p>
                                Aplikace má omezené možnosti kvůli bezpečnosti - byla tvořena jako drobný a jednoduchý projekt, takže proto je zaměřena na pouze SELECT dotazy
                                a jejich upravování pomocí WHERE a podobně.
                            </p>
                            <p>
                                Dalším omezením je výpis pouze posledních 25 záznamů z celého výsledku, kvůli snížení náročnosti. <em>(Pokud chcete kompletní data, můžete je získat v záložce export ve formátu JSON.)</em>
                            </p>
                            <h2>Tabulky:</h2>
                            <p>
                                Momentálně je dostupná pouze tabulka <code>employees</code>.
                            </p>
                            <p>
                                Rád bych, ale časem rozšířil databázi a k těmto rozšířením taktéž přidal další scénaře.
                            </p>
                            <h2>Scénaře</h2>
                            <p>
                                Scénaře jsou jednoduché úkoly. Pokud spustíte SQL dotaz, který bude mít výsledek shodný jako je uložen k danému scénaři tak máte splněno.
                                To znamená, že k některým scénařům existuje více řešení.
                                <br />
                                <br />
                                <em>- Ukládá se k danému scénaři vždy poslední správný SQL dotaz.</em><br />
                                <em>- Vše ohledně postupu se ukládá do Cookies (SESSION + Cookie).</em>
                            </p>
                            <h2>Export</h2>
                            <p>
                                Export slouží k případnému získání celého výsledku. Taktéž ho využívám při debugování aplikace a vytvářní nových scénářů.
                                <br />
                                <br />
                                Pokud by vám přišlo, že Vaše řešení je správné ale aplikace ho nerozeznala můžete vytvořit issue a toto mi k tomu přidat, rád se na to podvám.
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Template -->
                <div class="col-md-3">
                    <h3>Scénaře</h3>

                    <h4>Aktivní scénář:</h4>
                    <strong><span id="template_active">-</span></strong>
                    <p id="template_active_desc"></p>

                    <strong>Stav:</strong> <span id="template_status"><span class="glyphicon glyphicon-remove"></span> Nesplněno</span>
                    <hr />

                    <div id="template_correct_sql" class="hidden"></div>

                    <div class="list-group" id="template_list"></div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Template detail -->
        <div class="modal fade" id="modal_template_detail" tabindex="-1" role="dialog" aria-labelledby="ModalTemplateDetail">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal_template_detail_title"></h4>
                    </div>
                    <div class="modal-body" id="modal_template_detail_content">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zavřít</button>
                        <button type="button" class="btn btn-primary" data-id="" id="modal_template_detail_activate">Aktivovat</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="./js/jquery-3.1.1.min.js"></script>
        <script src="./js/bootstrap.min.js"></script>        
        <script src="./js/codemirror.js"></script>
        <script src="./js/sql.js"></script>
        <script src="./js/script.js"></script>
    </body>
</html>