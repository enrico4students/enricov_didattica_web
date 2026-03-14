<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$oldSessionId = $_SESSION["last_known_session_id"] ?? session_id();
$message = "";

$users = [
    "anna" => [
        "password" => "stud123",
        "role" => "studente",
        "name" => "Anna Rossi"
    ],
    "luca" => [
        "password" => "doc123",
        "role" => "docente",
        "name" => "Luca Bianchi"
    ],
    "sara" => [
        "password" => "admin123",
        "role" => "amministratore",
        "name" => "Sara Verdi"
    ]
];

if (isset($_GET["action"])) {

    if ($_GET["action"] === "logout") {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 3600,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: " . strtok($_SERVER["REQUEST_URI"], "?"));
        exit;
    }

    if ($_GET["action"] === "clear") {
        $_SESSION = [];
        $message = "Variabili di sessione svuotate.";
    }

    if ($_GET["action"] === "counter") {
        if (!isset($_SESSION["counter"])) {
            $_SESSION["counter"] = 1;
        } else {
            $_SESSION["counter"]++;
        }
        $message = "Contatore aggiornato.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["do_login"])) {

        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";

        if (isset($users[$username]) && $users[$username]["password"] === $password) {

            $oldSessionId = session_id();

            session_regenerate_id(true);

            $_SESSION["username"] = $username;
            $_SESSION["name"] = $users[$username]["name"];
            $_SESSION["role"] = $users[$username]["role"];
            $_SESSION["login_time"] = time();
            $_SESSION["session_id_before_login"] = $oldSessionId;
            $_SESSION["session_id_after_login"] = session_id();

            $message = "Login effettuato correttamente.";
        } else {
            $message = "Credenziali non valide.";
        }
    }
}

$_SESSION["last_known_session_id"] = session_id();

$currentSessionId = session_id();
$isLogged = isset($_SESSION["username"]);
$role = $_SESSION["role"] ?? null;
$name = $_SESSION["name"] ?? "ospite";

$responseHeaders = headers_list();

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function showArray($arr)
{
    return htmlspecialchars(print_r($arr, true), ENT_QUOTES, "UTF-8");
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Demo PHP Sessioni + HTTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            background: #f4f6f8;
            color: #222;
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        .box {
            background: #ffffff;
            border: 1px solid #cfd8dc;
            padding: 16px;
            margin-bottom: 18px;
            border-radius: 8px;
        }

        .row {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .col {
            flex: 1 1 420px;
        }

        pre {
            background: #eef2f5;
            border: 1px solid #d6dde3;
            padding: 12px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg {
            padding: 10px;
            background: #fff8e1;
            border: 1px solid #e0c97f;
            margin-bottom: 16px;
        }

        .ok {
            color: #0a6b1a;
            font-weight: bold;
        }

        .no {
            color: #8b1e1e;
            font-weight: bold;
        }

        .button-bar a,
        .button-bar button {
            display: inline-block;
            margin: 6px 8px 6px 0;
        }

        button {
            padding: 8px 12px;
            cursor: pointer;
        }

        input[type="text"],
        input[type="password"],
        select {
            padding: 7px;
            width: 240px;
            max-width: 100%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #ccd5db;
            padding: 8px;
            text-align: left;
        }

        .small {
            font-size: 0.95em;
            color: #444;
        }

        .area {
            padding: 10px;
            margin-top: 10px;
            border-left: 4px solid #607d8b;
            background: #f7fafc;
        }
    </style>
</head>
<body>

    <h1>Demo didattica PHP: sessioni, cookie, header HTTP e login</h1>

    <?php if ($message !== ""): ?>
        <div class="msg"><?php echo h($message); ?></div>
    <?php endif; ?>

    <div class="box">
        <h2>1. Stato generale</h2>
        <table>
            <tr>
                <th>Elemento</th>
                <th>Valore</th>
            </tr>
            <tr>
                <td>Utente autenticato</td>
                <td>
                    <?php if ($isLogged): ?>
                        <span class="ok">Sì</span>
                    <?php else: ?>
                        <span class="no">No</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Nome utente visualizzato</td>
                <td><?php echo h($name); ?></td>
            </tr>
            <tr>
                <td>Ruolo corrente</td>
                <td><?php echo h($role ?? "nessuno"); ?></td>
            </tr>
            <tr>
                <td>Session name</td>
                <td><?php echo h(session_name()); ?></td>
            </tr>
            <tr>
                <td>Session ID corrente</td>
                <td><?php echo h($currentSessionId); ?></td>
            </tr>
            <tr>
                <td>Session ID prima del login</td>
                <td><?php echo h($_SESSION["session_id_before_login"] ?? "non disponibile"); ?></td>
            </tr>
            <tr>
                <td>Session ID dopo il login</td>
                <td><?php echo h($_SESSION["session_id_after_login"] ?? "non disponibile"); ?></td>
            </tr>
            <tr>
                <td>Ultimo accesso memorizzato</td>
                <td>
                    <?php
                    if (isset($_SESSION["login_time"])) {
                        echo h(date("d/m/Y H:i:s", $_SESSION["login_time"]));
                    } else {
                        echo "non disponibile";
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="row">

        <div class="col">
            <div class="box">
                <h2>2. Login</h2>

                <?php if (!$isLogged): ?>
                    <form method="post" action="">
                        <p>
                            <label>Username<br>
                                <input type="text" name="username" required>
                            </label>
                        </p>
                        <p>
                            <label>Password<br>
                                <input type="password" name="password" required>
                            </label>
                        </p>
                        <p>
                            <button type="submit" name="do_login" value="1">Effettuare login</button>
                        </p>
                    </form>

                    <div class="small">
                        Utenti di prova:<br>
                        anna / stud123<br>
                        luca / doc123<br>
                        sara / admin123
                    </div>
                <?php else: ?>
                    <p>Login attivo come <strong><?php echo h($name); ?></strong> con ruolo <strong><?php echo h($role); ?></strong>.</p>
                    <div class="button-bar">
                        <a href="?action=logout"><button type="button">Logout</button></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col">
            <div class="box">
                <h2>3. Azioni sulla sessione</h2>
                <div class="button-bar">
                    <a href="?action=counter"><button type="button">Incrementare contatore</button></a>
                    <a href="?action=clear"><button type="button">Svuotare $_SESSION</button></a>
                    <a href="<?php echo h(strtok($_SERVER["REQUEST_URI"], "?")); ?>"><button type="button">Ricaricare pagina pulita</button></a>
                </div>
                <p class="small">
                    Usare queste azioni per osservare come cambiano `$_SESSION`, il cookie di sessione e gli header.
                </p>
            </div>
        </div>

    </div>

    <div class="box">
        <h2>4. Contenuti diversi per i 3 profili</h2>

        <div class="area">
            <h3>Area comune</h3>
            <p>Questa parte è visibile a tutti, anche senza login.</p>
        </div>

        <?php if ($isLogged && $role === "studente"): ?>
            <div class="area">
                <h3>Area studente</h3>
                <p>Visualizzare compiti, materiali del corso e risultati personali.</p>
            </div>
        <?php endif; ?>

        <?php if ($isLogged && $role === "docente"): ?>
            <div class="area">
                <h3>Area docente</h3>
                <p>Gestire materiali didattici, valutazioni e informazioni delle classi.</p>
            </div>
        <?php endif; ?>

        <?php if ($isLogged && $role === "amministratore"): ?>
            <div class="area">
                <h3>Area amministratore</h3>
                <p>Gestire utenti, ruoli, configurazione del sito e funzioni riservate.</p>
            </div>
        <?php endif; ?>

        <?php if (!$isLogged): ?>
            <div class="area">
                <h3>Area riservata</h3>
                <p>Effettuare login per visualizzare le sezioni specifiche del proprio profilo.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">

        <div class="col">
            <div class="box">
                <h2>5. Contenuto di $_SESSION</h2>
                <pre><?php echo showArray($_SESSION); ?></pre>
            </div>
        </div>

        <div class="col">
            <div class="box">
                <h2>6. Cookie ricevuti dal browser</h2>
                <pre><?php echo showArray($_COOKIE); ?></pre>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col">
            <div class="box">
                <h2>7. Informazioni sulla richiesta HTTP</h2>
                <table>
                    <tr>
                        <th>Chiave</th>
                        <th>Valore</th>
                    </tr>
                    <tr>
                        <td>Metodo HTTP</td>
                        <td><?php echo h($_SERVER["REQUEST_METHOD"] ?? ""); ?></td>
                    </tr>
                    <tr>
                        <td>Request URI</td>
                        <td><?php echo h($_SERVER["REQUEST_URI"] ?? ""); ?></td>
                    </tr>
                    <tr>
                        <td>HTTP Host</td>
                        <td><?php echo h($_SERVER["HTTP_HOST"] ?? ""); ?></td>
                    </tr>
                    <tr>
                        <td>User-Agent</td>
                        <td><?php echo h($_SERVER["HTTP_USER_AGENT"] ?? ""); ?></td>
                    </tr>
                    <tr>
                        <td>Referer</td>
                        <td><?php echo h($_SERVER["HTTP_REFERER"] ?? "non presente"); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col">
            <div class="box">
                <h2>8. Header HTTP che PHP sta inviando</h2>
                <pre><?php echo showArray($responseHeaders); ?></pre>
            </div>
        </div>

    </div>

    <div class="box">
        <h2>9. Come usare questa pagina nei Developer Tools</h2>
        <p>
            Aprire i Developer Tools con F12. Nella scheda Network ricaricare la pagina e selezionare la richiesta corrente.
            Negli header della richiesta osservare il cookie di sessione. Negli header della risposta osservare eventuali
            Set-Cookie. Nella scheda Application o Storage osservare il cookie PHPSESSID e verificare se cambia dopo il login.
        </p>
    </div>

    <div class="box">
        <h2>10. Esperimenti utili</h2>
        <p>
            Effettuare prima un accesso come studente, poi logout, poi accesso come docente, poi come amministratore.
            Osservare come cambia la parte centrale della pagina. Premere poi "Incrementare contatore" e verificare
            l'aggiornamento di `$_SESSION`. Infine controllare se il session ID cambia dopo il login grazie a
            `session_regenerate_id(true)`.
        </p>
    </div>

</body>
</html>