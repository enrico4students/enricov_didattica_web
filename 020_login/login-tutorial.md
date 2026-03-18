# LEZIONE: sessioni, login e contenuti personalizzati in PHP

lezioncina  per chi conosce già le basi di PHP ma non ha ancora consolidato la gestione pratica di sessioni, login e contenuti differenziati in base all’utente.


1. Obiettivo della lezione

Capire come realizzare in PHP un piccolo sito che:

* permette il login
* mantiene l’utente autenticato tra una pagina e l’altra
* usa le variabili di sessione
* mostra contenuti diversi a seconda del profilo dell’utente
* protegge le pagine che non devono essere viste dagli utenti non autenticati

Come esempio verrà usato un sito con 3 profili:

* studente
* docente
* amministratore

Ogni profilo vedrà sezioni diverse della stessa pagina.

2. Idea generale

Quando un utente effettua il login, il server deve “ricordare” chi è anche nelle richieste successive.

HTTP, da solo, non ricorda nulla tra una richiesta e la successiva. Ogni richiesta è indipendente. Per questo motivo si usano le sessioni.

La sessione permette al server di associare a un visitatore un insieme di dati, per esempio:

* username
* id utente
* ruolo
* eventuali preferenze

Questi dati vengono salvati lato server. Il browser conserva solo un identificatore della sessione, di solito in un cookie.

In pratica:

* l’utente inserisce username e password
* PHP controlla se sono corretti
* se sono corretti, salva i dati dell’utente in $_SESSION
* nelle pagine successive PHP legge $_SESSION e capisce chi è l’utente

3. Che cos’è una sessione in PHP

In PHP la sessione si usa con:

```php
session_start();
```

Questa istruzione deve essere eseguita prima di leggere o scrivere le variabili di sessione.

Le variabili di sessione si trovano nell’array associativo:

```php
$_SESSION
```

Esempio:

```php
session_start();

$_SESSION["username"] = "mario";
$_SESSION["ruolo"] = "docente";
```

In un’altra pagina:

```php
session_start();

echo $_SESSION["username"];
echo $_SESSION["ruolo"];
```

Importante: senza `session_start()` la sessione non viene inizializzata correttamente, quindi `$_SESSION` non funzionerà come previsto.

session_start() è approfondita in sezioni successive

4. Come funziona la sessione dal punto di vista logico

Il meccanismo concettuale è questo:

* il browser visita il sito
* PHP crea una sessione
* il server assegna un identificatore univoco alla sessione
* il browser riceve questo identificatore, spesso tramite cookie
* alle richieste successive il browser rimanda quell’identificatore
* il server riconosce la sessione e recupera i dati associati

Quindi la sessione non è “dentro la pagina”, ma è un dato mantenuto dal server per riconoscere l’utente.

5. Struttura del piccolo sito di esempio

Si può immaginare una struttura di questo tipo:

* index.php
* login.php
* verifica_login.php
* dashboard.php
* logout.php
* auth.php

Funzione dei file:

* index.php: pagina iniziale, pubblica
* login.php: form di accesso
* verifica_login.php: controlla credenziali e crea la sessione
* dashboard.php: pagina con contenuti diversi in base al ruolo
* logout.php: chiude la sessione
* auth.php: file di supporto con controlli comuni

6. Primo esempio minimo di sessione

Prima di parlare di login, conviene vedere un esempio molto semplice.

File: prova_sessione.php

```php
<?php
session_start();

if (!isset($_SESSION["contatore"])) {
    $_SESSION["contatore"] = 1;
} else {
    $_SESSION["contatore"]++;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prova sessione</title>
</head>
<body>
    <h1>Prova sessione</h1>
    <p>Numero visite nella sessione corrente: <?php echo $_SESSION["contatore"]; ?></p>
</body>
</html>
```

Cosa fa:

* alla prima visita crea `$_SESSION["contatore"]`
* alle visite successive incrementa il valore
* il server “ricorda” il contatore solo per quella sessione

Questo esempio fa capire l’idea fondamentale: la sessione permette di conservare informazioni tra richieste diverse.

7. Login: idea logica

Il login è un controllo di identità.

Flusso tipico:

* mostrare un modulo con username e password
* inviare i dati a una pagina PHP
* verificare se le credenziali sono corrette
* se corrette, salvare in sessione i dati utili
* reindirizzare l’utente a una pagina riservata
* se errate, mostrare un messaggio di errore

Per semplicità, in questa lezione gli utenti saranno definiti in un array PHP. In un sito reale di solito si userebbe un database.

8. Definizione degli utenti di esempio

Si considerino 3 utenti:

* anna, ruolo studente
* luca, ruolo docente
* sara, ruolo amministratore

Per semplicità didattica si può scrivere:

```php
$utenti = [
    "anna" => [
        "password" => "stud123",
        "ruolo" => "studente",
        "nome" => "Anna Rossi"
    ],
    "luca" => [
        "password" => "doc123",
        "ruolo" => "docente",
        "nome" => "Luca Bianchi"
    ],
    "sara" => [
        "password" => "admin123",
        "ruolo" => "amministratore",
        "nome" => "Sara Verdi"
    ]
];
```

Attenzione importante: questo è solo un esempio didattico.

In un sito reale:

* non salvare password in chiaro
* usare password hashate con `password_hash()`
* verificare con `password_verify()`
* salvare gli utenti in database

9. Pagina iniziale pubblica

File: index.php

```php
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Sito di esempio</title>
</head>
<body>
    <h1>Home page</h1>

    <?php if (isset($_SESSION["username"])): ?>
        <p>Accesso effettuato come <?php echo htmlspecialchars($_SESSION["nome"]); ?> (<?php echo htmlspecialchars($_SESSION["ruolo"]); ?>)</p>
        <p><a href="dashboard.php">Vai alla dashboard</a></p>
        <p><a href="logout.php">Logout</a></p>
    <?php else: ?>
        <p>Utente non autenticato.</p>
        <p><a href="login.php">Vai al login</a></p>
    <?php endif; ?>
</body>
</html>
```

Questa pagina mostra già un primo comportamento dinamico:

* se l’utente ha fatto login, mostra collegamenti riservati
* altrimenti mostra il collegamento al login

10. Pagina di login

File: login.php

```php
<?php
session_start();

if (isset($_SESSION["username"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php
    if (isset($_GET["errore"])) {
        echo "<p style='color:red;'>Credenziali non valide.</p>";
    }
    ?>

    <form action="verifica_login.php" method="post">
        <p>
            <label>Username:
                <input type="text" name="username" required>
            </label>
        </p>
        <p>
            <label>Password:
                <input type="password" name="password" required>
            </label>
        </p>
        <p>
            <button type="submit">Accedere</button>
        </p>
    </form>

    <h2>Utenti di prova</h2>
    <p>anna / stud123</p>
    <p>luca / doc123</p>
    <p>sara / admin123</p>
</body>
</html>
```

Da osservare:

* se l’utente è già autenticato, viene reindirizzato alla dashboard
* il form invia i dati con `method="post"`
* in caso di errore compare un messaggio

11. Pagina che verifica il login

File: verifica_login.php

```php
<?php
session_start();

$utenti = [
    "anna" => [
        "password" => "stud123",
        "ruolo" => "studente",
        "nome" => "Anna Rossi"
    ],
    "luca" => [
        "password" => "doc123",
        "ruolo" => "docente",
        "nome" => "Luca Bianchi"
    ],
    "sara" => [
        "password" => "admin123",
        "ruolo" => "amministratore",
        "nome" => "Sara Verdi"
    ]
];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

if (isset($utenti[$username]) && $utenti[$username]["password"] === $password) {
    $_SESSION["username"] = $username;
    $_SESSION["nome"] = $utenti[$username]["nome"];
    $_SESSION["ruolo"] = $utenti[$username]["ruolo"];

    header("Location: dashboard.php");
    exit;
} else {
    header("Location: login.php?errore=1");
    exit;
}
```

Spiegazione passo per passo:

`$_SERVER["REQUEST_METHOD"]`
indica il metodo HTTP usato. Qui serve a controllare che la pagina venga chiamata tramite POST e non semplicemente digitando l’URL.

`$_POST["username"] ?? ""`
legge il valore inviato dal form. Se non esiste, usa stringa vuota.

`isset($utenti[$username])`
controlla se l’utente esiste nell’array.

`$utenti[$username]["password"] === $password`
controlla se la password coincide.

Se tutto è corretto, i dati importanti vengono copiati in sessione:

* username
* nome
* ruolo

Da questo momento l’utente è autenticato.

12. Pagina riservata con contenuti diversi per ruolo

File: dashboard.php

```php
<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$nome = $_SESSION["nome"];
$ruolo = $_SESSION["ruolo"];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard riservata</h1>

    <p>Benvenuto, <?php echo htmlspecialchars($nome); ?>.</p>
    <p>Ruolo: <?php echo htmlspecialchars($ruolo); ?></p>

    <h2>Contenuti comuni a tutti gli utenti autenticati</h2>
    <p>Questa sezione è visibile a studente, docente e amministratore.</p>

    <?php if ($ruolo === "studente"): ?>
        <h2>Area studente</h2>
        <p>Visualizzazione voti personali.</p>
        <p>Visualizzazione materiali del corso.</p>
    <?php endif; ?>

    <?php if ($ruolo === "docente"): ?>
        <h2>Area docente</h2>
        <p>Inserimento voti.</p>
        <p>Gestione materiali didattici.</p>
    <?php endif; ?>

    <?php if ($ruolo === "amministratore"): ?>
        <h2>Area amministratore</h2>
        <p>Gestione utenti.</p>
        <p>Configurazione generale del sito.</p>
    <?php endif; ?>

    <?php if ($ruolo === "docente" || $ruolo === "amministratore"): ?>
        <h2>Area personale autorizzato</h2>
        <p>Questa parte è visibile solo a docente e amministratore.</p>
    <?php endif; ?>

    <p><a href="index.php">Tornare alla home</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
```

Qui si vede il punto centrale della lezione: la pagina legge il ruolo dalla sessione e decide cosa mostrare.

Quindi non esiste una dashboard “uguale per tutti”, ma una pagina che cambia comportamento a seconda dell’utente.

13. Logout

File: logout.php

```php
<?php
session_start();

session_unset();
session_destroy();

header("Location: index.php");
exit;
```

Cosa fanno queste istruzioni:

`session_unset()`
rimuove le variabili di sessione

`session_destroy()`
distrugge la sessione

Dopo il logout il sito non deve più considerare l’utente autenticato.

14. Come proteggere più facilmente le pagine riservate

Se in molte pagine si deve controllare se l’utente è autenticato, conviene creare un file di supporto.

File: auth.php

```php
<?php
session_start();

function richiediLogin() {
    if (!isset($_SESSION["username"])) {
        header("Location: login.php");
        exit;
    }
}

function richiediRuolo($ruoliAmmessi) {
    richiediLogin();

    if (!in_array($_SESSION["ruolo"], $ruoliAmmessi)) {
        echo "Accesso negato.";
        exit;
    }
}
```

Uso in una pagina:

```php
<?php
require_once "auth.php";
richiediLogin();
?>
```

Oppure, per consentire l’accesso solo a docente e amministratore:

```php
<?php
require_once "auth.php";
richiediRuolo(["docente", "amministratore"]);
?>
```

Questo approccio è utile perché evita di ripetere sempre lo stesso codice.

15. Differenza tra “mostrare meno contenuti” e “proteggere davvero”

Questo è un punto fondamentale.

Esistono due casi diversi:

Caso A
Mostrare sezioni diverse nella stessa pagina.

Caso B
Bloccare completamente l’accesso a una pagina o funzione.

Esempio:

* uno studente entra in dashboard.php e vede solo la sezione studente
* un amministratore entra in utenti.php e può gestire gli account

Nel primo caso basta spesso un controllo `if` per mostrare o nascondere una sezione.

Nel secondo caso bisogna proprio impedire l’accesso con un controllo lato server.

Non basta nascondere un link HTML. Se una pagina è sensibile, il controllo va sempre fatto in PHP, sul server.

16. Esempio di pagina accessibile solo all’amministratore

File: gestione_utenti.php

```php
<?php
require_once "auth.php";
richiediRuolo(["amministratore"]);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione utenti</title>
</head>
<body>
    <h1>Gestione utenti</h1>
    <p>Questa pagina è accessibile solo all'amministratore.</p>
</body>
</html>
```

Anche se uno studente scrivesse manualmente l’URL della pagina, il server negherebbe l’accesso.

17. Perché usare `htmlspecialchars()`

Quando si stampa un dato proveniente dall’utente o da una sessione, è buona pratica usare:

```php
htmlspecialchars(...)
```

Esempio:

```php
echo htmlspecialchars($_SESSION["nome"]);
```

Questo serve a evitare che caratteri speciali vengano interpretati come HTML.

È una misura importante contro problemi di sicurezza, in particolare XSS.

18. Versione più realistica: password hashate

L’esempio precedente usa password in chiaro solo per semplicità.

In un’applicazione reale il flusso corretto è:

* salvare nel database l’hash della password
* al login usare `password_verify()`

Esempio di creazione hash:

```php
$passwordHash = password_hash("doc123", PASSWORD_DEFAULT);
echo $passwordHash;
```

Verifica:

```php
if (password_verify($passwordInserita, $hashSalvato)) {
    echo "Password corretta";
} else {
    echo "Password errata";
}
```

Concettualmente:

* `password_hash()` trasforma la password in una forma sicura da memorizzare
* `password_verify()` confronta la password inserita con l’hash

Non si deve quasi mai confrontare direttamente la password in chiaro con una stringa salvata.

19. Problema frequente: “headers already sent”

Molti principianti incontrano questo errore:

“Cannot modify header information - headers already sent...”

Succede spesso quando si usa:

```php
header("Location: dashboard.php");
```

dopo aver già inviato output al browser.

Esempio sbagliato:

```php
<?php
echo "Ciao";
header("Location: dashboard.php");
```

Perché è sbagliato:
il browser ha già ricevuto contenuto, quindi gli header HTTP non possono più essere modificati.

Regola pratica:
eseguire `session_start()` e `header(...)` prima di stampare HTML o testo.

20. Problema frequente: dimenticare `exit`

Dopo un redirect con `header("Location: ...")` conviene quasi sempre scrivere:

```php
exit;
```

Esempio corretto:

```php
header("Location: login.php");
exit;
```

Questo impedisce che il resto dello script continui a essere eseguito.

21. Distinzione importante: autenticazione e autorizzazione

Sono due concetti diversi.

Autenticazione
Significa verificare chi è l’utente.
Esempio: username e password corretti.

Autorizzazione
Significa verificare cosa quell’utente può fare.
Esempio: uno studente è autenticato, ma non è autorizzato a entrare nell’area amministratore.

Nel piccolo sito di esempio:

* il login realizza l’autenticazione
* il controllo sul ruolo realizza l’autorizzazione

22. Esempio completo di comportamento del sito

Scenario 1: utente non autenticato

* apre index.php
* vede il messaggio “Utente non autenticato”
* clicca su login
* inserisce credenziali

Scenario 2: login come studente

* username: anna
* password: stud123
* PHP salva in sessione:

  * username = anna
  * nome = Anna Rossi
  * ruolo = studente
* nella dashboard vede:

  * area comune
  * area studente
* non vede:

  * area docente
  * area amministratore

Scenario 3: login come docente

* username: luca
* password: doc123
* nella dashboard vede:

  * area comune
  * area docente
  * area personale autorizzato

Scenario 4: login come amministratore

* username: sara
* password: admin123
* nella dashboard vede:

  * area comune
  * area amministratore
  * area personale autorizzato

23. Miglioramento organizzativo: separare i dati utenti

Per chiarezza, i dati degli utenti possono essere spostati in un file separato.

File: utenti.php

```php
<?php
$utenti = [
    "anna" => [
        "password" => "stud123",
        "ruolo" => "studente",
        "nome" => "Anna Rossi"
    ],
    "luca" => [
        "password" => "doc123",
        "ruolo" => "docente",
        "nome" => "Luca Bianchi"
    ],
    "sara" => [
        "password" => "admin123",
        "ruolo" => "amministratore",
        "nome" => "Sara Verdi"
    ]
];
```

Poi in `verifica_login.php`:

```php
require_once "utenti.php";
```

Questo rende il codice più pulito.

24. Sicurezza: errori concettuali da evitare

Errore 1
Fidarsi solo dell’interfaccia grafica.

Nascondere un pulsante non significa proteggere una funzione. Il controllo va fatto in PHP.

Errore 2
Salvare password in chiaro.

Da evitare in applicazioni reali.

Errore 3
Non rigenerare l’ID di sessione dopo il login.

In un progetto reale conviene usare:

```php
session_regenerate_id(true);
```

subito dopo il login riuscito.

Esempio:

```php
if (isset($utenti[$username]) && $utenti[$username]["password"] === $password) {
    session_regenerate_id(true);

    $_SESSION["username"] = $username;
    $_SESSION["nome"] = $utenti[$username]["nome"];
    $_SESSION["ruolo"] = $utenti[$username]["ruolo"];

    header("Location: dashboard.php");
    exit;
}
```

Questo riduce alcuni rischi legati alla sessione.

Errore 4
Dare per scontato che tutti i dati di `$_POST` esistano sempre.

Conviene usare `??` o `isset()`.

Errore 5
Mostrare messaggi di errore troppo dettagliati.

Per esempio, non è opportuno dire:
“Username corretto ma password errata”
Meglio un messaggio generico:
“Credenziali non valide”

25. Esempio di pagina che mostra contenuti in modo molto esplicito

Se si volesse una pagina unica con sezioni molto evidenti, si potrebbe scrivere così.

```php
<?php
session_start();

if (!isset($_SESSION["username"])) {
    echo "<h1>Accesso non effettuato</h1>";
    echo "<p>Effettuare il login per vedere i contenuti riservati.</p>";
    exit;
}

$ruolo = $_SESSION["ruolo"];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pagina contenuti differenziati</title>
</head>
<body>
    <h1>Pagina con contenuti differenziati</h1>

    <p>Utente: <?php echo htmlspecialchars($_SESSION["nome"]); ?></p>
    <p>Ruolo: <?php echo htmlspecialchars($ruolo); ?></p>

    <hr>

    <h2>Sezione comune</h2>
    <p>Avvisi generali del sito.</p>

    <hr>

    <?php
    switch ($ruolo) {
        case "studente":
            echo "<h2>Sezione studente</h2>";
            echo "<p>Compiti assegnati e risultati personali.</p>";
            break;

        case "docente":
            echo "<h2>Sezione docente</h2>";
            echo "<p>Registro, valutazioni e materiali delle classi.</p>";
            break;

        case "amministratore":
            echo "<h2>Sezione amministratore</h2>";
            echo "<p>Gestione account, ruoli e configurazioni.</p>";
            break;

        default:
            echo "<p>Ruolo non riconosciuto.</p>";
            break;
    }
    ?>
</body>
</html>
```

Qui viene usato `switch`, utile quando si hanno più ruoli alternativi.

26. Quando usare `if` e quando usare `switch`

`if`
utile quando una sezione può essere visibile a più ruoli contemporaneamente

Esempio:

```php
if ($ruolo === "docente" || $ruolo === "amministratore") {
    echo "Sezione riservata al personale";
}
```

`switch`
utile quando si vuole scegliere un solo blocco tra alternative diverse

Esempio:

* se studente, mostrare solo area studente
* se docente, mostrare solo area docente
* se amministratore, mostrare solo area amministratore

27. Mini schema mentale del sistema

Il sistema può essere ricordato così:

Input:
utente inserisce username e password

Controllo:
PHP verifica le credenziali

Memorizzazione:
PHP salva i dati in `$_SESSION`

Uso:
le altre pagine leggono `$_SESSION`

Uscita:
logout distrugge la sessione

28. Esercizi utili

Per consolidare davvero l’argomento conviene provare a modificare l’esempio.

Esercizio 1
Aggiungere una quarta tipologia di utente, per esempio “genitore”.

Esercizio 2
Creare una pagina accessibile solo a docente e amministratore.

Esercizio 3
Mostrare nella dashboard data e ora dell’ultimo accesso salvandole in sessione.

Esercizio 4
Far comparire nella home un messaggio diverso per ciascun ruolo.

Esercizio 5
Sostituire l’array utenti con un database MySQL.

29. Conclusioni

I concetti fondamentali da fissare sono questi.

La sessione serve a mantenere lo stato tra richieste HTTP diverse.

`$_SESSION` contiene dati dell’utente autenticato, come nome e ruolo.

Il login autentica l’utente.

I controlli sul ruolo autorizzano oppure negano l’accesso a contenuti e pagine.

Mostrare contenuti diversi in base al profilo è semplice: basta leggere il ruolo salvato nella sessione e usare condizioni `if` o `switch`.

La sicurezza reale non si ottiene nascondendo elementi HTML, ma controllando sempre lato server.

30. Versione sintetica del progetto completo

Per comodità, ecco l’insieme essenziale dei file.

index.php
login.php
verifica_login.php
dashboard.php
logout.php

Con questi 5 file è già possibile costruire un piccolo sito funzionante con 3 profili utente e contenuti differenziati.

## session_start()

### Che cosa fa `session_start()`

`session_start()` inizializza o riprende una **sessione PHP**.

In termini precisi svolge tre operazioni principali:

1. controlla se il browser ha già inviato un **identificatore di sessione**
2. se esiste, **recupera i dati della sessione salvati sul server**
3. se non esiste, **crea una nuova sessione** e assegna un nuovo identificatore

Dopo l'esecuzione di `session_start()` diventa disponibile l'array globale:

```
$_SESSION
```

che contiene le variabili associate alla sessione.

Esempio minimale:

```
<?php
session_start();

$_SESSION["utente"] = "mario";
?>
```

In un'altra pagina:

```
<?php
session_start();

echo $_SESSION["utente"];
?>
```

Senza `session_start()` PHP **non carica i dati della sessione**, quindi `$_SESSION` non conterrà i valori memorizzati.

---

### Come funziona  

Quando viene chiamata `session_start()` PHP:

1. legge il cookie `PHPSESSID` inviato dal browser
2. usa quell'identificatore per trovare un file di sessione sul server

Tipicamente i dati della sessione sono salvati in file simili a:

```
/tmp/sess_abc123xyz
```

Dentro questo file PHP memorizza le variabili di sessione serializzate.

Se il browser **non ha ancora il cookie**, PHP:

* crea una nuova sessione
* genera un nuovo ID
* invia il cookie al browser

---

### Dove deve essere chiamata

`session_start()` deve essere chiamata:

* **prima di qualsiasi output HTML**
* in **tutte le pagine che leggono o scrivono `$_SESSION`**

Esempio corretto:

```
<?php
session_start();
?>

<!DOCTYPE html>
<html>
...
```

Esempio errato:

```
<?php
echo "ciao";
session_start();
?>
```

Questo produce spesso l'errore:

```
Cannot send session cache limiter - headers already sent
```

perché HTTP headers (cookie e sessione) devono essere inviati **prima del contenuto della pagina**.

---

### Cosa succede se si chiama più volte

Se `session_start()` viene chiamata più volte nello stesso script si ottiene generalmente un **warning** simile a:

```
session_start(): Session already started
```

Questo succede perché la sessione è già stata inizializzata.

---

## 5. Come evitare di chiamarla più volte

Esistono due metodi comuni.

### Metodo 1 (più semplice)

Controllare lo stato della sessione con `session_status()`.

Esempio:

```
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

`PHP_SESSION_NONE` significa che **nessuna sessione è attiva**.

Questo è il metodo più corretto.

---

### Metodo 2 (più vecchio ma diffuso)

Controllare se la sessione esiste già.

```
if (!isset($_SESSION)) {
    session_start();
}
```

Questo metodo funziona spesso ma è **meno preciso**, perché `$_SESSION` può esistere anche se la sessione non è stata avviata correttamente.

---

## 6. Metodo consigliato nella pratica

Nella maggior parte dei progetti si crea un **file di bootstrap** per l'autenticazione.

Esempio: `auth.php`

```
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Poi nelle altre pagine:

```
require_once "auth.php";
```

In questo modo:

* la sessione viene inizializzata una sola volta
* il codice resta pulito
* si evitano duplicazioni

---

### Differenza tra sessione non esistente e sessione vuota

È importante distinguere:

sessione non avviata

```
session_status() === PHP_SESSION_NONE
```

sessione avviata ma senza variabili

```
$_SESSION è un array vuoto
```

Esempio:

```
session_start();

if (empty($_SESSION)) {
    echo "sessione attiva ma senza dati";
}
```

---

## 8. Quando la sessione termina

Una sessione termina quando accade una di queste cose:

1. viene chiamato

   session_destroy();

2. il cookie della sessione scade

3. il server elimina i dati di sessione

4. l'utente chiude il browser (dipende dalla configurazione)

Esempio logout:

```
session_start();
session_destroy();
```

---

## 9. Schema mentale semplice

Si può ricordare così:

session_start()

significa:

```
"caricare o creare la sessione dell'utente corrente"
```

Solo dopo questa operazione PHP può:

* leggere `$_SESSION`
* scrivere `$_SESSION`

---

## 10. Regola pratica da ricordare

In quasi tutte le applicazioni PHP si applica questa regola:

Ogni pagina che usa la sessione deve iniziare con

```
session_start();
```

oppure con un file incluso che la avvia in modo controllato.

---

# errori comuni quando usano le sessioni che spesso compaiono nei piccoli progetti PHP.

Overview dei **tre problemi più comuni legati alle sessioni nei sistemi di login PHP**. Non sono errori di sintassi ma **errori concettuali di sicurezza** molto frequenti nei piccoli progetti o nei progetti didattici.

I tre problemi principali sono:

* session fixation
* session hijacking
* cattive pratiche nella gestione del login

---

# 1. Session Fixation

## Concetto

La **session fixation** è un attacco in cui l’attaccante forza la vittima a usare **un ID di sessione scelto dall’attaccante**.

Se l'applicazione non cambia l'ID di sessione dopo il login, l'attaccante può usare **lo stesso ID di sessione** per accedere all'account della vittima.

---

## Esempio semplificato

1. l’attaccante crea una sessione sul sito

il server genera un ID:

```
PHPSESSID = abc123
```

2. l’attaccante invia alla vittima un link che usa quella sessione

```
https://example.com/login.php?PHPSESSID=abc123
```

3. la vittima apre il link e fa login

se il sito **non cambia la sessione**, allora:

```
session id = abc123
```

4. l’attaccante usa lo stesso session id

```
PHPSESSID = abc123
```

e il server lo considera **lo stesso utente autenticato**.

---

## Perché succede

Succede se il sito:

* crea la sessione **prima del login**
* **non cambia l’ID di sessione dopo l’autenticazione**

---

## Soluzione

Rigenerare l’ID di sessione dopo il login.

In PHP si usa:

```
session_regenerate_id(true);
```

Esempio corretto:

```
if ($loginCorretto) {

    session_regenerate_id(true);

    $_SESSION["user"] = $username;
    $_SESSION["ruolo"] = $ruolo;

}
```

Questo genera **un nuovo session ID**.

Quindi l’ID eventualmente noto all’attaccante diventa inutile.

---

# 2. Session Hijacking

## Concetto

La **session hijacking** consiste nel rubare l’ID di sessione di un utente autenticato.

Se l’attaccante ottiene il valore del cookie di sessione, può impersonare l’utente.

---

## Esempio

cookie di sessione:

```
PHPSESSID = f83js8sjs9s
```

Se l’attaccante intercetta questo cookie e lo usa nel proprio browser, il server pensa che sia **lo stesso utente autenticato**.

---

## Come può essere rubata la sessione

I casi più comuni sono:

### 1 intercettazione su rete non sicura

se il sito usa **HTTP invece di HTTPS**, i cookie possono essere intercettati.

### 2 attacco XSS

se il sito ha una vulnerabilità XSS, uno script può leggere i cookie.

### 3 accesso al computer della vittima

se qualcuno usa lo stesso computer mentre la sessione è attiva.

---

## Contromisure

Le principali difese sono:

### usare HTTPS

sempre.

### usare cookie sicuri

in PHP:

```
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

### rigenerare l'ID di sessione dopo il login

```
session_regenerate_id(true);
```

### scadenza sessione

salvare un timestamp:

```
$_SESSION["last_activity"] = time();
```

e controllarlo:

```
if (time() - $_SESSION["last_activity"] > 1800) {
    session_destroy();
}
```

---

# 3. Errori comuni nei sistemi di login PHP

## errore 1

### salvare password in chiaro

Esempio sbagliato:

```
if ($password == "1234")
```

Problema:

* password leggibili
* database compromesso = password compromesse

Soluzione:

```
password_hash()
password_verify()
```

Esempio:

```
$hash = password_hash("1234", PASSWORD_DEFAULT);
```

verifica:

```
if (password_verify($passwordInserita, $hash))
```

---

## errore 2

### fidarsi solo dell'interfaccia grafica

Molti siti nascondono solo i link.

Esempio:

```
if ($ruolo == "admin") {
    echo "<a href='admin.php'>admin</a>";
}
```

Ma se uno studente scrive manualmente:

```
site/admin.php
```

potrebbe entrare.

Soluzione: controllare **sempre lato server**.

```
if ($_SESSION["ruolo"] != "admin") {
    exit("accesso negato");
}
```

---

## errore 3

### non controllare se l'utente è autenticato

Pagina vulnerabile:

```
echo "Benvenuto " . $_SESSION["user"];
```

Se qualcuno entra direttamente nella pagina senza login, la sessione non esiste.

Soluzione:

```
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
```

---

## errore 4

### non usare htmlspecialchars

Se si stampa direttamente input utente:

```
echo $_SESSION["username"];
```

un attaccante potrebbe inserire codice HTML o JavaScript.

Soluzione:

```
echo htmlspecialchars($_SESSION["username"]);
```

---

## errore 5

### sessioni infinite

Se la sessione non scade mai:

* computer condiviso
* utente dimentica logout

altri utenti potrebbero accedere.

Soluzione:

timeout di sessione.

---

# 4. Schema riassuntivo

Session Fixation

```
attaccante impone session ID
```

difesa:

```
session_regenerate_id()
```

---

Session Hijacking

```
attaccante ruba session ID
```

difesa:

```
HTTPS
cookie sicuri
timeout sessione
```

---

Errori comuni login

```
password in chiaro
controlli solo lato client
pagine non protette
output non sanificato
```

---

# 5. Regola di progettazione semplice

Un sistema di login corretto dovrebbe sempre:

1 verificare credenziali con password hash  
2 rigenerare la sessione dopo login  
3 salvare utente e ruolo in `$_SESSION`  
4 controllare accesso alle pagine lato server  
5 usare HTTPS  

---

 
# Dove PHP salva le sessioni

Quando si usa `session_start()`, PHP deve memorizzare i dati della sessione **da qualche parte sul server**.

Per impostazione predefinita PHP salva le sessioni **in file** nel filesystem.

Il percorso dipende dalla configurazione di PHP. Si può vedere con:

```
phpinfo()
```

oppure con:

```
echo session_save_path();
```

Su molti sistemi Linux il percorso è simile a:

```
/tmp
```

oppure

```
/var/lib/php/sessions
```

Dentro questa directory PHP crea file con nome simile a:

```
sess_a7f8b3c4d5e6f7g8h9
```

Il prefisso `sess_` è standard.

---

### Relazione tra cookie e file di sessione

Il browser non salva i dati della sessione.

Il browser salva solo **l'identificatore della sessione**.

Questo identificatore è inviato tramite cookie:

```
PHPSESSID
```

Esempio cookie:

```
PHPSESSID = a7f8b3c4d5e6f7g8h9
```

Quando il browser invia una richiesta HTTP, include il cookie.

Il server riceve:

```
PHPSESSID = a7f8b3c4d5e6f7g8h9
```

PHP quindi cerca il file:

```
sess_a7f8b3c4d5e6f7g8h9
```

Se il file esiste, carica i dati della sessione.

Se non esiste, crea una nuova sessione.

---

### Struttura di un file di sessione

Quando si salvano variabili in `$_SESSION`, PHP scrive i dati nel file di sessione usando una forma di **serializzazione**.

Esempio di codice PHP:

```
session_start();

$_SESSION["user"] = "mario";
$_SESSION["ruolo"] = "docente";
$_SESSION["id"] = 25;
```

Il file di sessione potrebbe contenere qualcosa di simile a:

```
user|s:5:"mario";
ruolo|s:7:"docente";
id|i:25;
```

Questo è un formato interno di PHP.

Significato:

```
s = string
i = integer
```

Esempio:

```
s:5:"mario";
```

significa

stringa di lunghezza 5 con valore "mario".

---

### Come PHP ricostruisce `$_SESSION`

Quando una pagina PHP esegue:

```
session_start();
```

PHP fa questi passi:

1. legge il cookie `PHPSESSID`
2. trova il file `sess_ID`
3. legge il contenuto del file
4. deserializza i dati
5. ricostruisce l'array `$_SESSION`

Quindi dopo `session_start()`:

```
$_SESSION["user"]
$_SESSION["ruolo"]
```

sono già disponibili.

---

### Cosa succede quando si modifica `$_SESSION`

Durante l'esecuzione dello script PHP le variabili sono solo in memoria.

Alla fine dello script PHP:

* serializza `$_SESSION`
* riscrive il file di sessione

Quindi il file viene aggiornato **alla fine della richiesta HTTP**.

---

## 6. Esempio completo di ciclo di una sessione

### richiesta 1

utente visita:

```
login.php
```

PHP esegue:

```
session_start();
```

non esiste cookie → nuova sessione

server crea file:

```
sess_ab12cd34
```

browser riceve cookie:

```
PHPSESSID=ab12cd34
```

---

#### richiesta 2

utente fa login

browser invia:

```
PHPSESSID=ab12cd34
```

PHP carica file:

```
sess_ab12cd34
```

codice PHP:

```
$_SESSION["user"] = "mario";
```

file aggiornato:

```
user|s:5:"mario";
```

---

#### richiesta 3

utente visita dashboard

browser invia:

```
PHPSESSID=ab12cd34
```

PHP carica file:

```
sess_ab12cd34
```

`$_SESSION` contiene:

```
user = mario
```

quindi il sito sa chi è l'utente.

---

### Come distruggere la sessione

Quando si fa logout:

```
session_destroy();
```

PHP elimina il file di sessione.

Quindi il file:

```
sess_ab12cd34
```

viene cancellato.

Alla richiesta successiva il server non troverà più la sessione.

---

### Garbage collection delle sessioni

I file di sessione non restano per sempre.

PHP ha un sistema di pulizia chiamato **garbage collection**.

Parametri principali:

```
session.gc_probability
session.gc_divisor
session.gc_maxlifetime
```

Esempio tipico:

```
session.gc_maxlifetime = 1440
```

significa:

```
sessione valida per 1440 secondi
cioè 24 minuti
```

Dopo questo tempo il file può essere eliminato automaticamente.

---

### Dove configurare le sessioni

Le impostazioni principali sono nel file:

```
php.ini
```

Parametri importanti:

```
session.save_path
session.gc_maxlifetime
session.cookie_secure
session.cookie_httponly
```

Esempio:

```
session.save_path = "/var/lib/php/sessions"
```

---

### Altri sistemi di storage delle sessioni

I file sono il metodo più semplice ma non l'unico.

Le sessioni possono essere salvate anche in:

database

```
MySQL
PostgreSQL
```

memoria distribuita

```
Redis
Memcached
```

Queste soluzioni sono usate nei siti molto grandi perché:

* più veloci
* funzionano con più server
* facilitano il bilanciamento del carico

---

### Problema nei sistemi con più server

Se un sito usa più server web (load balancing):

utente → server A
richiesta successiva → server B

Il server B potrebbe non avere il file di sessione.

Soluzioni:

1 session storage condiviso (NFS)

2 database

3 Redis

4 sticky sessions nel load balancer

---

### Schema mentale completo

Browser

salva solo:

```
PHPSESSID
```

Server

salva:

```
file sess_ID
```

session_start()

fa:

```
cookie → session ID → file → $_SESSION
```

---

### Esempio minimo completo

pagina1.php

```
<?php
session_start();
$_SESSION["contatore"] = ($_SESSION["contatore"] ?? 0) + 1;
?>

visite: <?php echo $_SESSION["contatore"]; ?>
```

Ogni richiesta aggiorna il file di sessione.

---

### Perché questo sistema è importante da capire

Molti errori nei sistemi PHP derivano dal non capire che:

* HTTP è **stateless**
* le sessioni simulano **uno stato persistente**
* lo stato è salvato **sul server**
* il browser ha solo **un identificatore**

---

# Programma di esempio

Sì. È possibile creare una **mini-applicazione completa in un solo file PHP** che permetta di osservare:

* l'ID di sessione
* il contenuto di `$_SESSION`
* creazione e distruzione della sessione
* simulazione login/logout
* modifica delle variabili di sessione

Questo è molto utile a scopo didattico perché permette di vedere **in tempo reale cosa succede alla sessione**.

Il file seguente può essere chiamato per esempio:

```
session_demo.php
```

---

# Applicazione dimostrativa completa in un solo file

```
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET["action"])) {

    if ($_GET["action"] === "login") {

        session_regenerate_id(true);

        $_SESSION["user"] = "mario";
        $_SESSION["ruolo"] = "docente";
        $_SESSION["login_time"] = time();

    }

    if ($_GET["action"] === "logout") {

        $_SESSION = [];

        session_destroy();

        header("Location: session_demo.php");
        exit;

    }

    if ($_GET["action"] === "addcounter") {

        if (!isset($_SESSION["counter"])) {
            $_SESSION["counter"] = 1;
        } else {
            $_SESSION["counter"]++;
        }

    }

    if ($_GET["action"] === "clear") {

        $_SESSION = [];

    }

}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Session Demo PHP</title>
<style>

body{
    font-family:Arial;
    margin:40px;
    background:#f5f5f5;
}

.box{
    background:white;
    padding:20px;
    margin-bottom:20px;
    border:1px solid #ccc;
}

button{
    padding:8px 12px;
    margin:5px;
}

pre{
    background:#eee;
    padding:10px;
}

</style>
</head>

<body>

<h1>Demo Sessioni PHP</h1>

<div class="box">

<h2>ID della sessione</h2>

<?php

echo session_id();

?>

</div>

<div class="box">

<h2>Cookie ricevuti dal browser</h2>

<pre>

<?php

print_r($_COOKIE);

?>

</pre>

</div>

<div class="box">

<h2>Contenuto di $_SESSION</h2>

<pre>

<?php

print_r($_SESSION);

?>

</pre>

</div>

<div class="box">

<h2>Azioni</h2>

<a href="?action=login"><button>Simula login</button></a>

<a href="?action=addcounter"><button>Aumenta contatore</button></a>

<a href="?action=clear"><button>Svuota variabili sessione</button></a>

<a href="?action=logout"><button>Logout (distruggi sessione)</button></a>

</div>

<div class="box">

<h2>Informazioni sessione</h2>

<?php

if (isset($_SESSION["login_time"])) {

    echo "Login effettuato alle: ";

    echo date("H:i:s", $_SESSION["login_time"]);

}

?>

</div>

</body>
</html>
```

---

# Come usare l'applicazione

1. salvare il file

   session_demo.php

2. aprirlo nel browser

3. aprire **Developer Tools → Application → Cookies**

4. osservare il cookie:

   PHPSESSID

---

# Esperimenti didattici da fare

## Esperimento 1

Premere

```
Simula login
```

Osservare:

* `$_SESSION` cambia
* compare user
* compare ruolo

---

## Esperimento 2

Premere

```
Aumenta contatore
```

Osservare:

```
$_SESSION["counter"]
```

che aumenta ad ogni richiesta.

---

## Esperimento 3

Aprire **Developer Tools → Network**

Ricaricare la pagina e osservare negli header:

```
Cookie: PHPSESSID=...
```

---

## Esperimento 4

Premere

```
Logout
```

Osservare:

* sessione distrutta
* `$_SESSION` vuoto
* nuovo session id alla richiesta successiva

---

## Cosa dimostra questa applicazione

Permette di osservare concretamente:

1. come nasce una sessione
2. come cambia `$_SESSION`
3. come funziona il cookie `PHPSESSID`
4. come funziona `session_regenerate_id()`
5. cosa succede quando si distrugge la sessione

---

## versione didattica che mostra nella stessa pagina:

* header HTTP ricevuti
* header HTTP inviati
* session ID prima e dopo login
* visualizzazione grafica del ciclo HTTP.

utile per esaminare  **HTTP + sessioni insieme**.

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

Notare:  
- prima del login c'è già una sessione, ma senza dati utente  
- dopo il login compaiono username, name, role  
- il session ID cambia dopo il login  
- il cookie di sessione può essere visto nei Developer Tools  
- la stessa pagina può comportarsi in modo diverso in base al ruolo  

Errori concettuali da evitare:  
- nascondere una sezione HTML non equivale a proteggere davvero una funzione  
- non controllare il ruolo lato server, il controllo del ruolo va sempre fatto lato server   
- salvare password in chiaro va bene solo in una demo didattica, non in un'applicazione reale