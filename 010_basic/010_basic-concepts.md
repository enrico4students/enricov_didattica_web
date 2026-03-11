
# Annotazioni informali <br/> complementari al libro di testo che è e rimane <br/>la fonte primaria, completa e ufficiale di studio  

---

# Introduzione alle tecnologie di base del Web

## 1. Il Web come sistema di documenti collegati

Il **World Wide Web** è un sistema distribuito di documenti collegati tra loro tramite hyperlink e accessibili tramite Internet.

Tre tecnologie fondamentali costituiscono la base del Web:

* **HTTP** – protocollo di comunicazione
* **HTML** – linguaggio di markup per descrivere documenti
* **JavaScript** – linguaggio di programmazione eseguito nel browser

Queste tecnologie svolgono ruoli diversi:

* HTTP trasporta dati tra client e server
* HTML descrive la struttura dei contenuti
* JavaScript permette comportamento dinamico lato client

---

# 2. Markup language vs programming language

## Linguaggi di markup

Un **markup language** è un linguaggio utilizzato per **descrivere la struttura e il significato di un documento**, non per eseguire calcoli o algoritmi.

Esempi:

* HTML
* XML
* SVG
* MathML

Un linguaggio di markup usa **tag** per indicare il ruolo delle parti del documento.

Esempio:

```
<p>Questo è un paragrafo</p>
```

Qui non viene eseguita alcuna logica: viene solo **descritta la struttura del contenuto**.

---

## Linguaggi di programmazione

Un **programming language** serve invece per **descrivere algoritmi e procedure computazionali**.

Caratteristiche tipiche:

* variabili
* strutture di controllo
* funzioni
* espressioni

Esempi:

* JavaScript
* Python
* Java
* C++

Questi linguaggi permettono di **eseguire operazioni e trasformazioni sui dati**.

---

## Markup come metadati

Il markup può essere interpretato come una forma di **metadati**, cioè **dati che descrivono altri dati**.

Esempio HTML:

```
<h1>Titolo</h1>
```

La parola *Titolo* è il dato.
Il tag `<h1>` è un **metadato che descrive il ruolo del testo**.

Nel Web i metadati sono fondamentali perché permettono:

* rendering corretto nel browser
* indicizzazione nei motori di ricerca
* accessibilità
* elaborazione automatica

---

# 3. HTTP: basi del protocollo

## Che cos’è HTTP

**HTTP (HyperText Transfer Protocol)** è il protocollo utilizzato per trasferire documenti web tra client e server.

È un protocollo:

* applicativo
* stateless
* basato su richiesta e risposta

Funziona sopra TCP/IP.

---

## Modello request–response

Il funzionamento base è:

1. il browser invia una **HTTP request**
2. il server risponde con una **HTTP response**

Esempio semplificato di richiesta:

```
GET /index.html HTTP/1.1
Host: example.com
```

Il server può rispondere con:

```
HTTP/1.1 200 OK
Content-Type: text/html
```

seguito dal contenuto della pagina.

---

## Metodi HTTP principali

I metodi indicano il tipo di operazione richiesta.

Metodi più comuni:

* GET – richiede una risorsa
* POST – invia dati al server
* PUT – aggiorna una risorsa
* DELETE – elimina una risorsa

Nel Web tradizionale le operazioni più comuni sono **GET e POST**.

---

## HTTP è stateless

HTTP è un protocollo **senza memoria dello stato**.

Ogni richiesta è indipendente.

Per mantenere stato si usano tecnologie come:

* cookie
* sessioni
* token

---

# 4. Serializzazione e deserializzazione

Questo è un concetto generale molto importante.

## Serializzazione

La **serializzazione** è il processo di **trasformazione di una struttura dati complessa in una sequenza lineare di dati**.

Scopi principali:

* trasmissione in rete
* salvataggio su file
* comunicazione tra sistemi

Esempi di formati serializzati:

* HTML
* JSON
* XML
* CSV

---

## Deserializzazione

La **deserializzazione** è il processo inverso:
ricostruire una struttura dati complessa a partire da una rappresentazione serializzata.

---

# 5. HTML e DOM come esempio di serializzazione

## HTML come rappresentazione serializzata

Una pagina HTML è **una rappresentazione serializzata di una struttura ad albero**.

Esempio:

```
<html>
  <body>
    <p>Hello</p>
  </body>
</html>
```

Questa è una **forma testuale lineare**.

---

## DOM: rappresentazione in memoria

Quando il browser carica la pagina:

1. riceve il testo HTML
2. lo analizza
3. costruisce una struttura dati interna

Questa struttura si chiama:

**DOM (Document Object Model)**.

Il DOM è un **albero di oggetti** che rappresenta il documento.

Processo:

HTML serializzato
→ parsing
→ DOM in memoria

Questo è un tipico caso di **deserializzazione**.

---

# 6. Il download di una pagina Web

Quando si apre una pagina Web non viene scaricato un solo file.

La sequenza è:

1. il browser scarica il documento HTML
2. analizza il contenuto
3. trova riferimenti ad altre risorse

Esempi:

```
<img src="image.png">
<script src="app.js"></script>
<link rel="stylesheet" href="style.css">
```

Ogni riferimento genera **nuove richieste HTTP automatiche**.

Una singola pagina può quindi generare decine o centinaia di download.

---

# 7. JavaScript: overview

**JavaScript** è il linguaggio di programmazione del browser.

Serve per:

* manipolare il DOM
* reagire agli eventi dell’utente
* modificare dinamicamente la pagina
* comunicare con il server

Caratteristiche principali:

* linguaggio interpretato
* tipizzazione dinamica
* orientato agli oggetti (prototipi)
* esecuzione nel browser

JavaScript non descrive la struttura del documento ma **il comportamento della pagina**.

---

# 8. Dinamismo Web lato client

Il dinamismo lato client significa che **la pagina può cambiare senza essere ricaricata dal server**.

Questo è possibile grazie alla combinazione di:

* JavaScript
* DOM

JavaScript può:

* leggere elementi della pagina
* modificarli
* crearne di nuovi
* cancellarli

Esempio concettuale:

JavaScript può cambiare il contenuto di un paragrafo oppure aggiungere elementi a una lista.

Questo meccanismo è alla base di molte applicazioni web moderne.

---

# 9. Dinamismo Web lato server

Il dinamismo può essere generato anche **sul server**.

Esistono due modelli fondamentali.

---

## Pagina HTML statica

Il server invia un file già esistente.

Esempio:

```
index.html
```

Il contenuto è identico per tutti gli utenti.

---

## Pagina generata da un programma

Un programma lato server può **generare HTML dinamicamente**.

Concettualmente funziona così:

1. il server esegue uno script
2. lo script produce HTML
3. l’HTML viene inviato al browser

Il meccanismo è spesso:

**programma → standard output → risposta HTTP**

Esempio storico: **CGI scripts**.

---

# 10. Linguaggi principali per il Web dinamico lato server

Molti linguaggi possono generare pagine HTML.

Tra i più diffusi:

* PHP
* Python
* Java
* JavaScript (Node.js)
* Ruby
* C#

Il principio è sempre lo stesso:

lo script costruisce una stringa HTML che viene inviata al browser.

---

# 11. Breve storia di JavaScript e compatibilità

## Origine

JavaScript è stato creato nel **1995 da Brendan Eich** presso Netscape.

In origine si chiamava **LiveScript**.

---

## Browser wars

Negli anni ’90 ci fu una forte competizione tra:

* Netscape Navigator
* Microsoft Internet Explorer

Ogni browser implementava JavaScript in modo leggermente diverso.

Questo generava **problemi di compatibilità**.

---

## Standardizzazione

Per risolvere il problema fu creato lo standard:

**ECMAScript**

Questo definisce il linguaggio in modo indipendente dai browser.

---

## jQuery

Negli anni 2000 nacque **jQuery**.

Scopo principale:

* semplificare la manipolazione del DOM
* ridurre i problemi di compatibilità tra browser

Per molti anni jQuery è stato uno degli strumenti più usati nello sviluppo web.

---

## Framework moderni

Oggi molte applicazioni web utilizzano framework JavaScript avanzati:

* React
* Angular
* Vue

Questi framework introducono strutture complesse per applicazioni web molto dinamiche.

Tuttavia è importante comprendere prima le **tecnologie fondamentali del Web**:

* HTTP
* HTML
* DOM
* JavaScript

---

# 12. Sintesi concettuale

Il Web può essere visto come un sistema composto da tre livelli principali.

Trasporto
HTTP trasferisce dati tra client e server.

Struttura del documento
HTML descrive i contenuti tramite markup.

Comportamento dinamico
JavaScript modifica il documento e gestisce l’interazione con l’utente.

Il browser riceve HTML serializzato, costruisce il DOM in memoria e consente a JavaScript di modificarlo.

---

## Alcuni riferimenti

Mozilla Developer Network – HTTP overview
[https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview](https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview)

Mozilla Developer Network – DOM
[https://developer.mozilla.org/en-US/docs/Web/API/Document_Object_Model](https://developer.mozilla.org/en-US/docs/Web/API/Document_Object_Model)

Mozilla Developer Network – JavaScript
[https://developer.mozilla.org/en-US/docs/Web/JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

W3C HTML specification
[https://html.spec.whatwg.org/](https://html.spec.whatwg.org/)

MITRE ATT&CK (per contesto sicurezza web)
[https://attack.mitre.org/](https://attack.mitre.org/)
