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
                        <li role="presentation"><a href="#tab_settings" aria-controls="tab_settings" role="tab" data-toggle="tab">Nastavení</a></li>
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
                                aplikace je úmístěna na <a href="https://github.com/geekmoss/sql_um_test">GitHub</a>.
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
                        <div role="tabpanel" class="tab-pane" id="tab_settings">
                            <div class="alert" id="admin_message"></div>
                            <!-- Rollback & Set Token -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>Rollback</h3>
                                    <p>Obnovení dat v tabulce <code>employees</code>.</p>
                                    <div class="text-center">
                                        <button class="btn btn-danger disabled" disabled>ROLLBACK</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h3>Token</h3>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="token_input" />
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info" id="token_button">Potvrdit</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- New Template & .... -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>Nový scénář</h3>
                                    <div>
                                        <label for="new_template_name">Název:</label>
                                        <input type="text" id="new_template_name" class="form-control" />

                                        <label for="new_template_content">Popis / Zadání:</label>
                                        <textarea class="form-control" id="new_template_content"></textarea>

                                        <div class="btn-group btn-group-justified" data-toggle="buttons" style="margin-top: 8px;">
                                            <label  id="new_template_reslabel_ta" class="btn btn-primary active">
                                                <input type="radio" name="new_template_type_result" id="new_template_res_ta" value="ta" autocomplete="off" checked>Vlastní výsledek
                                            </label>
                                            <label id="new_template_reslabel_export" class="btn btn-primary">
                                                <input type="radio" name="new_template_type_result" id="new_template_res_export" value="export" autocomplete="off">Aktuální výsledek v exportu
                                            </label>
                                        </div>
                                        <div id="new_template_div_ta" style="margin-top: 8px;">
                                            <label for="new_template_ta">Výsledek:</label>
                                            <textarea class="form-control" id="new_template_ta"></textarea>
                                        </div>

                                        <div class="text-center" style="margin-top: 18px;">
                                            <button id="new_template_save" class="btn btn-success" style="padding-right: 35px; padding-left: 35px;">Uložit</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">

                                </div>
                            </div>
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

    <?php if (file_exists('./analytics.php')) { include './analytics.php'; } ?>
    </body>
</html>