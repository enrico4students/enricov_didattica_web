Lezione: CORS nella programmazione Web

## Introduzione

A prima vista sembra naturale pensare che, se il browser riesce a raggiungere un sito, allora anche JavaScript possa chiamarlo liberamente. In realtà non è così.


CORS non è un protocollo, è invece un insieme di regole di sicurezza applicate dal browser alle richieste Web tra origini diverse.

## Il concetto di origin

Nel contesto Web, una origin è definita dalla tripletta:

* protocollo, host, porta

ex. https://www.esempio.it
ha:
* protocollo: https
* host: [www.esempio.it](http://www.esempio.it)
* porta implicita: 443

Invece: http://www.esempio.it 
è una origin diversa, perché cambia il protocollo.

Anche: https://api.esempio.it è diversa, perché cambia l’host.

E anche: https://www.esempio.it:8443 è diversa, perché cambia la porta.

Quindi due URL che sembrano molto simili possono appartenere a origini diverse.

Esempi:

```
https://www.sito.it/pagina1.html
https://www.sito.it/api/dati
```

Queste due appartengono alla stessa origin.

Invece:

```
https://www.sito.it
https://api.sito.it
```

non appartengono alla stessa origin.

##  Same-Origin Policy: il meccanismo da cui nasce CORS

La Same-Origin Policy è una regola di sicurezza del browser secondo cui **uno script** caricato da una origin non può leggere liberamente risorse provenienti da un’altra origin.

Senza questa protezione, un sito malevolo aperto nel browser potrebbe tentare di leggere dati sensibili da altri siti visitati dall’utente, per esempio:

* email
* aree riservate
* dati bancari
* informazioni sanitarie
* dati di sessione

Immaginare questo scenario:

* l’utente è autenticato su un sito bancario
* poi visita un sito malevolo
* il sito malevolo esegue JavaScript nel browser
* quel JavaScript prova a leggere dati dal sito della banca sfruttando i cookie già presenti

Se il browser non imponesse limiti severi, il danno sarebbe enorme.

La Same-Origin Policy serve proprio a impedire questo tipo di lettura non autorizzata.

## Che cosa fa CORS

CORS è il meccanismo con cui un server può dire al browser:
“Va bene, permettere a una pagina proveniente da un’altra origin di leggere questa risposta.”

Quindi CORS non elimina la Same-Origin Policy. La estende in modo controllato.

Il concetto chiave è questo:

* di default il browser blocca la lettura cross-origin da JavaScript
* il server può autorizzarla esplicitamente con intestazioni HTTP appropriate

Questa autorizzazione è decisa dal server di destinazione, non dalla pagina chiamante.

5. Un primo esempio intuitivo

Supporre di avere:

Pagina frontend:

```
https://frontend.example.com
```

API backend:

```
https://api.example.com
```

Dal JavaScript della pagina si esegue:

```
fetch("https://api.example.com/prodotti")
```

La richiesta HTTP parte davvero. Questo è importante: spesso non è la richiesta a essere bloccata in partenza, ma l’accesso alla risposta da parte del codice JavaScript.

Se il server API risponde senza header CORS adeguati, il browser riceve comunque la risposta, ma non la consegna allo script in modo utilizzabile. In console si vedrà un errore CORS.

6. Primo esempio di codice JavaScript

Pagina caricata da:

```
https://frontend.example.com
```

Codice:

```
fetch("https://api.example.com/prodotti")
    .then(response => response.json())
    .then(data => {
        console.log(data);
    })
    .catch(error => {
        console.error("Errore:", error);
    });
```

Se il server API non autorizza l’origine `https://frontend.example.com`, il browser segnalerà un errore CORS e il codice non riuscirà a leggere i dati come previsto.

7. Gli header principali di CORS

Il server che vuole autorizzare l’accesso cross-origin deve restituire uno o più header HTTP.

Il più importante è:

```
Access-Control-Allow-Origin
```

Esempio:

```
Access-Control-Allow-Origin: https://frontend.example.com
```

Questo significa: permettere a quella specifica origin di leggere la risposta.

In alcuni casi si trova:

```
Access-Control-Allow-Origin: *
```

L’asterisco significa: consentire l’accesso da qualsiasi origin.

Ma l’asterisco non è sempre adatto. Per esempio non va bene se si usano credenziali come cookie o autenticazione con sessione.

Altri header utili sono:

```
Access-Control-Allow-Methods
Access-Control-Allow-Headers
Access-Control-Allow-Credentials
```

Esempio completo:

```
Access-Control-Allow-Origin: https://frontend.example.com
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
```

8. Richieste semplici e richieste non semplici

Non tutte le richieste cross-origin vengono trattate allo stesso modo dal browser.

Esiste una distinzione importante tra:

* simple requests
* richieste che richiedono preflight

In termini pratici, una richiesta è più “semplice” quando usa metodi e header molto standard, per esempio una GET o una POST con caratteristiche limitate.

Esempio relativamente semplice:

```
fetch("https://api.example.com/utente?id=10")
```

Oppure:

```
fetch("https://api.example.com/login", {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "user=mario&pwd=1234"
});
```

Se invece si usano header particolari, JSON, token Authorization, metodi come PUT o DELETE, il browser spesso esegue prima una richiesta di controllo chiamata preflight.

9. Il preflight: la richiesta OPTIONS

Il preflight è una richiesta preliminare che il browser invia al server per chiedere se la richiesta reale è ammessa.

Per esempio, se lo script fa:

```
fetch("https://api.example.com/prodotti/5", {
    method: "PUT",
    headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer TOKEN123"
    },
    body: JSON.stringify({ nome: "Nuovo nome" })
});
```

il browser prima può inviare:

```
OPTIONS /prodotti/5 HTTP/1.1
Origin: https://frontend.example.com
Access-Control-Request-Method: PUT
Access-Control-Request-Headers: content-type, authorization
```

Il server deve rispondere in modo compatibile, per esempio:

```
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: https://frontend.example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

Solo dopo questa risposta positiva il browser invierà la vera richiesta PUT.

Se il preflight fallisce, la richiesta reale non viene effettuata oppure la risposta non viene resa disponibile allo script, a seconda del caso.

10. Esempio lato server con Node.js e Express

Un server Express minimale che abilita CORS per una origin specifica:

```
const express = require("express");
const app = express();

app.use((req, res, next) => {
    res.setHeader("Access-Control-Allow-Origin", "https://frontend.example.com");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization");
    res.setHeader("Access-Control-Allow-Credentials", "true");

    if (req.method === "OPTIONS") {
        return res.sendStatus(204);
    }

    next();
});

app.get("/prodotti", (req, res) => {
    res.json([
        { id: 1, nome: "Mouse" },
        { id: 2, nome: "Tastiera" }
    ]);
});

app.listen(3000, () => {
    console.log("Server avviato sulla porta 3000");
});
```

Questo esempio mostra bene un punto importante: CORS si gestisce lato server, non lato browser.

11. Esempio lato server con Flask

Versione molto semplice in Flask:

```
from flask import Flask, jsonify, request, make_response

app = Flask(__name__)

@app.after_request
def add_cors_headers(response):
    response.headers["Access-Control-Allow-Origin"] = "https://frontend.example.com"
    response.headers["Access-Control-Allow-Methods"] = "GET, POST, PUT, DELETE, OPTIONS"
    response.headers["Access-Control-Allow-Headers"] = "Content-Type, Authorization"
    response.headers["Access-Control-Allow-Credentials"] = "true"
    return response

@app.route("/prodotti", methods=["GET", "OPTIONS"])
def prodotti():
    if request.method == "OPTIONS":
        return make_response("", 204)

    return jsonify([
        {"id": 1, "nome": "Monitor"},
        {"id": 2, "nome": "Stampante"}
    ])

if __name__ == "__main__":
    app.run(debug=True)
```

12. CORS non blocca tutto il traffico di rete

Questo è un punto molto importante e spesso fonte di confusione.

CORS non è un firewall generale del browser. Non significa che il browser impedisca ogni contatto con altri siti.

Per esempio, una pagina HTML può includere:

* immagini da altri domini
* fogli di stile CSS da altri domini
* script JavaScript da altri domini
* iframe da altri domini, con limiti specifici

Esempio HTML:

```
<img src="https://cdn.example.com/logo.png">
<script src="https://cdn.example.com/app.js"></script>
```

Questi caricamenti possono funzionare senza che si parli necessariamente di CORS nello stesso modo in cui avviene con `fetch`.

Il punto critico di CORS riguarda in particolare la possibilità che il codice JavaScript legga programmaticamente la risposta cross-origin.

13. Un esempio concreto di rischio

Immaginare questo codice eseguito su un sito malevolo:

```
fetch("https://mail.example.com/api/messaggi")
    .then(response => response.text())
    .then(text => {
        console.log("Messaggi rubati:", text);
    });
```

Se l’utente fosse autenticato sul servizio di posta e il browser consentisse liberamente la lettura della risposta, il sito malevolo potrebbe rubare contenuti privati.

CORS serve a evitare proprio questo.

14. CORS e autenticazione con cookie

Le richieste con cookie sono particolarmente delicate.

Se si usa `fetch` con cookie o altre credenziali, bisogna specificarlo esplicitamente nel client:

```
fetch("https://api.example.com/profilo", {
    credentials: "include"
})
    .then(response => response.json())
    .then(data => console.log(data));
```

Ma non basta.

Il server deve rispondere con:

```
Access-Control-Allow-Origin: https://frontend.example.com
Access-Control-Allow-Credentials: true
```

e non con:

```
Access-Control-Allow-Origin: *
```

Perché l’uso di `*` insieme alle credenziali non è ammesso.

Inoltre, per cookie cross-site moderni, spesso serve anche:

```
SameSite=None; Secure
```

nella configurazione del cookie.

15. Perché a volte in Postman funziona ma nel browser no

Questa è una situazione molto comune.

Una chiamata API può funzionare in Postman o con `curl`, ma fallire nel browser con errore CORS.

Il motivo è semplice:

* Postman non applica la Same-Origin Policy del browser
* `curl` non applica la Same-Origin Policy del browser
* il browser sì

Quindi:

```
curl https://api.example.com/prodotti
```

può funzionare perfettamente, mentre la stessa chiamata fatta da JavaScript in una pagina Web può venire bloccata dal browser.

16. Esempio con curl

Per esempio:

```
curl -i https://api.example.com/prodotti
```

può restituire:

```
HTTP/1.1 200 OK
Content-Type: application/json

[{"id":1,"nome":"Mouse"}]
```

Ma se manca:

```
Access-Control-Allow-Origin
```

lo script eseguito nel browser può non poter leggere la risposta.

17. Errori CORS tipici

Gli errori CORS più comuni in console hanno frasi simili a queste:

* No 'Access-Control-Allow-Origin' header is present on the requested resource
* Response to preflight request doesn't pass access control check
* The value of the 'Access-Control-Allow-Origin' header must not be '*' when the request's credentials mode is 'include'

Questi messaggi indicano problemi diversi, ma tutti ruotano attorno allo stesso principio: il browser non ha ricevuto dal server un’autorizzazione CORS coerente con la richiesta.

18. CORS non è una protezione completa contro gli attacchi

È importante non sopravvalutare CORS.

CORS è una misura importante, ma non sostituisce:

* autenticazione
* autorizzazione
* protezione CSRF
* protezione XSS
* validazione degli input
* gestione sicura delle sessioni

Un server non deve pensare: “Ho CORS, quindi sono sicuro.”

CORS regola chi può leggere la risposta da JavaScript nel browser. Non è un sistema generale di controllo degli accessi.

19. Quando non bisogna usare CORS come soluzione principale

In molti casi la soluzione migliore non è “aprire CORS”.

Per esempio, nei flussi di login SSO, OAuth2, OpenID Connect, SAML, spesso non bisogna chiamare l’endpoint di login con `fetch`.

Bisogna invece fare un redirect del browser.

Esempio:

```
window.location.href = "https://iam.example.com/login?client_id=abc123";
```

Questo perché certi endpoint non sono progettati come API AJAX, ma come pagine di autenticazione.

20. Soluzione con backend intermedio

Una strategia molto comune e spesso preferibile consiste nel far parlare il frontend solo con il proprio backend.

Schema:

* il browser chiama `https://frontend.example.com/api/...`
* il backend `frontend.example.com` contatta lato server `https://api.example.com/...`
* il browser non ha più una richiesta cross-origin verso l’API esterna

Questo approccio ha diversi vantaggi:

* meno problemi CORS nel browser
* maggiore controllo sui token
* migliore sicurezza
* possibilità di filtrare e validare le richieste

21. Parte pratica: che cosa si può fare dai Developer Tools

Arrivare ora a una domanda molto importante: dai Developer Tools si possono modificare le richieste per “permettere più cose”?

La risposta corretta è:

in parte sì, ma entro limiti molto precisi.

I Developer Tools permettono di:

* osservare le richieste
* modificare e reinviare una richiesta manualmente
* cambiare header di una singola richiesta ritentata
* simulare alcune condizioni
* vedere preflight, response headers, cookie, redirect

Ma i Developer Tools non permettono di cambiare le regole di sicurezza del browser per una normale pagina Web in produzione.

Quindi:

✅ si può sperimentare e capire il problema
✅ si può modificare una richiesta manuale per test
❌ non si può forzare il browser a ignorare davvero CORS per il codice della pagina, in modo stabile e generale

22. Che cosa osservare nei Developer Tools

Aprire in Chrome o Edge:

* F12
* scheda Network

Poi ricaricare la pagina.

Se una richiesta fallisce per CORS, selezionarla e controllare:

* Request URL
* Request Method
* Status Code
* Request Headers
* Response Headers
* presenza di una richiesta OPTIONS
* eventuali redirect 302 o 303

I campi più importanti da cercare sono:

nella richiesta:

```
Origin
Access-Control-Request-Method
Access-Control-Request-Headers
Cookie
Authorization
```

nella risposta:

```
Access-Control-Allow-Origin
Access-Control-Allow-Methods
Access-Control-Allow-Headers
Access-Control-Allow-Credentials
Vary: Origin
```

23. Esempio di analisi pratica nei Developer Tools

Supporre che la pagina faccia:

```
fetch("https://api.example.com/clienti", {
    method: "GET",
    headers: {
        "Authorization": "Bearer ABC123"
    }
});
```

Nei Developer Tools si potrebbe vedere prima una richiesta OPTIONS:

Request headers:

```
Origin: https://frontend.example.com
Access-Control-Request-Method: GET
Access-Control-Request-Headers: authorization
```

Se la risposta OPTIONS non contiene:

```
Access-Control-Allow-Origin: https://frontend.example.com
Access-Control-Allow-Headers: authorization
```

allora il browser bloccherà il seguito.

24. Modificare una richiesta dai Developer Tools

Nei browser moderni spesso si può fare:

* tasto destro sulla richiesta
* “Edit and Resend” oppure voce simile

Questo permette di modificare:

* URL
* metodo
* header
* body

Esempio: togliere un header problematico, come `Authorization`, per vedere se il preflight scompare.

Prima:

```
GET /clienti
Authorization: Bearer ABC123
```

Dopo modifica:

```
GET /clienti
```

Questo può aiutare a diagnosticare il problema, perché una richiesta con meno header può richiedere meno controlli CORS.

Ma attenzione: questo non cambia il comportamento reale del codice della pagina. Si modifica solo la richiesta reinviata manualmente come test.

25. Che cosa si può modificare utilmente per fare diagnosi

Dai Developer Tools si può provare a:

* rimuovere header personalizzati
* cambiare `Content-Type`
* cambiare metodo da PUT a POST o GET per test
* cambiare body
* vedere l’effetto di una richiesta senza credenziali

Per esempio:

Prima:

```
fetch("https://api.example.com/ordini", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer ABC123"
    },
    body: JSON.stringify({ id: 10 })
});
```

Questa richiesta può generare preflight.

Per test, si può provare manualmente nei Developer Tools una variante più semplice, ad esempio senza `Authorization`, o con un body diverso, solo per capire quale elemento scatena il problema.

26. Che cosa non si può fare davvero dai Developer Tools

Non si può usare i Developer Tools per far sì che il browser consenta alla pagina di leggere una risposta cross-origin se il server non la autorizza.

In particolare non si può, in modo normale e stabile:

* aggiungere dal client un vero `Access-Control-Allow-Origin` valido alla risposta del server
* cambiare il controllo CORS effettuato dal browser sul codice della pagina
* “sbloccare” una fetch reale della pagina soltanto editando una richiesta già partita
* aggirare il fatto che il server non gestisca correttamente il preflight

Questo punto è fondamentale.

CORS è deciso dal browser sulla base della risposta del server. I Developer Tools sono eccellenti per osservare e testare, ma non trasformano una risposta non autorizzata in una risposta autorizzata per il codice JavaScript della pagina.

27. Overrides e riscrittura locale delle risposte

Alcuni browser offrono strumenti avanzati come gli Overrides locali. In pratica si può salvare e sostituire localmente una risposta o alcune risorse per scopi di test.

Questo è utile per sviluppo e debug, ma bisogna capire bene il limite:

* può aiutare a simulare una risposta diversa
* può aiutare a provare una correzione
* non rappresenta il comportamento reale del server per tutti gli utenti
* non costituisce una soluzione di produzione

Quindi è un ottimo strumento didattico e di diagnosi, ma non una correzione reale del problema.

28. Esempio di ragionamento diagnostico corretto

Supporre di vedere questo errore:

“No 'Access-Control-Allow-Origin' header is present...”

Il ragionamento corretto è:

1. La pagina ha fatto una richiesta cross-origin.

2. Il server ha risposto, ma senza autorizzare l’origine.

3. Il browser ha bloccato l’accesso allo script.

4. Nei Developer Tools bisogna verificare:

   * qual è l’origin della pagina
   * qual è l’URL richiesto
   * se c’è preflight
   * quali header ha mandato il server

5. La correzione va fatta lato server oppure cambiando architettura.

6. Esempio completo frontend + backend

Frontend HTML/JS:

```
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Esempio CORS</title>
</head>
<body>
    <button id="btn">Caricare dati</button>

    <script>
        document.getElementById("btn").addEventListener("click", () => {
            fetch("https://api.example.com/dati", {
                method: "GET"
            })
            .then(response => response.json())
            .then(data => {
                console.log("Dati ricevuti:", data);
            })
            .catch(error => {
                console.error("Errore:", error);
            });
        });
    </script>
</body>
</html>
```

Backend Express:

```
const express = require("express");
const app = express();

app.use((req, res, next) => {
    res.setHeader("Access-Control-Allow-Origin", "https://frontend.example.com");
    res.setHeader("Access-Control-Allow-Methods", "GET, OPTIONS");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type");

    if (req.method === "OPTIONS") {
        return res.sendStatus(204);
    }

    next();
});

app.get("/dati", (req, res) => {
    res.json({ messaggio: "Funziona correttamente" });
});

app.listen(3000);
```

30. Esempio con credenziali

Frontend:

```
fetch("https://api.example.com/profilo", {
    method: "GET",
    credentials: "include"
})
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(err => console.error(err));
```

Server:

```
Access-Control-Allow-Origin: https://frontend.example.com
Access-Control-Allow-Credentials: true
```

Cookie del server:

```
Set-Cookie: sessionid=abc123; Secure; SameSite=None
```

Se manca uno di questi pezzi, il comportamento può non essere quello desiderato.

31. Errori concettuali molto comuni

Errore 1.
Pensare che CORS blocchi la connessione di rete in assoluto.

Non sempre è così. Spesso la richiesta parte, ma il browser impedisce allo script di leggere la risposta.

Errore 2.
Pensare che basti modificare JavaScript per risolvere.

In molti casi no. Il problema è lato server.

Errore 3.
Pensare che basti mettere `Access-Control-Allow-Origin: *` sempre.

No. Con credenziali non va bene, e in generale può essere troppo permissivo.

Errore 4.
Pensare che una chiamata di login IAM debba essere fatta con `fetch`.

Molto spesso i sistemi di login usano redirect del browser, non fetch AJAX.

32. Come studiare CORS in modo operativo

Per comprendere davvero CORS conviene esercitarsi così:

* creare una pagina semplice su una origin
* creare una piccola API su un’altra origin o porta
* osservare cosa succede senza header CORS
* aggiungere gradualmente gli header
* provare richieste GET semplici
* provare richieste POST JSON
* provare richieste con Authorization
* osservare il preflight in Network

Questo approccio pratico rende il concetto molto più chiaro della sola teoria.

33. Conclusione

CORS è un meccanismo di sicurezza del browser che regola la condivisione di risposte HTTP tra origini diverse. Nasce come estensione controllata della Same-Origin Policy. Il suo scopo è impedire che un sito possa leggere liberamente dati provenienti da un altro sito senza autorizzazione del server di destinazione.

L’idea centrale da fissare è molto semplice:

il browser non decide da solo di fidarsi; è il server di destinazione che deve autorizzare esplicitamente la condivisione cross-origin.

I Developer Tools sono uno strumento eccellente per capire che cosa accade, ispezionare preflight e header, e fare test manuali su singole richieste. Però non sono, di per sé, un modo per “sbloccare” realmente CORS per la pagina in esecuzione. La soluzione reale passa quasi sempre da una di queste tre strade:

* configurare correttamente il server
* usare il flusso giusto, per esempio redirect per login
* passare dal backend applicativo invece di fare chiamate cross-origin dirette dal frontend


---  

# Evitare CORS

Sì, esistono diversi modi per disabilitare CORS (Cross-Origin Resource Sharing) nei browser, principalmente attraverso estensioni o opzioni di avvio. Questi strumenti sono pensati esclusivamente per lo sviluppo e il testing locale, poiché disabilitare CORS ti espone a rischi per la sicurezza navigando normalmente .

Ecco le opzioni principali divise per approccio.

### 🛠️ Usare Estensioni del Browser (Metodo Più Comune)

Le estensioni sono il metodo più semplice e controllato. Aggiungono o modificano gli header HTTP per permettere le richieste cross-origin, e molte ti permettono di attivarle solo su specifici siti per non compromettere la sicurezza generale .

*   **Per Chrome, Edge, Brave, Opera (Browser Chromium)**: Puoi installare estensioni dal Chrome Web Store. Anche su Microsoft Edge, puoi aggiungere queste estensioni andando su `Estensioni` -> `Ottieni estensioni per Microsoft Edge` e cercando nel Chrome Web Store . Alcune opzioni affidabili sono:
    *   **Moesif Origin & CORS Changer**: Un'estensione semplice e molto popolare per aggiungere gli header CORS alle risposte .
    *   **CORS Unblock**: Un'estensione open source che usa le API moderne di Chrome per funzionare in modo efficiente .
    *   **Anti-CORS, anti-CSP**: Questa estensione è particolarmente utile perché disabilita CORS e CSP solo sugli host che selezioni cliccando sull'icona, lasciando inalterati tutti gli altri siti .

*   **Per Firefox**: Firefox ha il suo store di componenti aggiuntivi.
    *   **Disable CSP and CORS**: Un'estensione minimale che disabilita queste protezioni su tutti i siti . La descrizione stessa consiglia di disabilitarla dopo l'uso per la tua sicurezza .

### 🚀 Avviare il Browser con Opzioni Specifiche (Metodo "Profondo")

Se preferisci non installare estensioni, puoi avviare il browser da terminale (o creare un collegamento) con dei flag che disabilitano le politiche di sicurezza. **Questo metodo è più drastico e disabilita la protezione su tutte le schede aperte in quella finestra.**

*   **Google Chrome / Microsoft Edge**: Devi chiudere tutte le istanze del browser e avviarlo con il flag `--disable-web-security`.
    *   **Esempio per Windows** (dopo aver chiuso Chrome): Cerca "Esegui" (Win+R) e incolla:
        `chrome.exe --disable-web-security --user-data-dir="C:/Temp/chrome_dev"`
        (La cartella `user-data-dir` è necessaria per avviare un profilo separato e non intaccare le tue impostazioni principali).
    *   **Esempio per macOS**: Apri Terminale e incolla:
        `/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --disable-web-security --user-data-dir=/tmp/chrome_dev`

*   **Firefox**: Il metodo è leggermente diverso e si basa su una preferenza nascota. La documentazione ufficiale di MDN spiega che esiste una preferenza, `content.cors.disable`, che se impostata su `true` disabilita CORS . Tuttavia, questa opzione non è facilmente accessibile dalle impostazioni grafiche e va modificata nella pagina `about:config`, con la consapevolezza che è un'opzione altamente sperimentale e sconsigliata.

### ⚠️ Una Nota sulla Sicurezza

CORS è un meccanismo di sicurezza fondamentale del web . Disabilitarlo permette a script malevoli su qualsiasi sito web di fare richieste ai tuoi altri servizi (email, social, banking) come se fossi tu. Usa questi strumenti **solo per sviluppo locale** e **ricordati di disattivarli o chiudere la finestra del browser dedicata** quando torni a navigare normalmente .

Spero che queste opzioni ti siano d'aiuto per il tuo sviluppo! Se hai bisogno di chiarimenti su come usare uno di questi metodi, fammi sapere.

