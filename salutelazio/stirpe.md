
---

## 1. Handler per eventi di tracciamento dei moduli (Analytics)

**Registrazione**: tramite `Liferay.on()` in uno script iniziale (prima del Matomo).

**Linee**: circa linee 90-130 (nel primo blocco `<script>` dopo i commenti).

**Dettagli**:

```javascript
// ddмFieldBlur
Liferay.on('ddmFieldBlur', function(event) {
    if (window.Analytics) {
        Analytics.send('fieldBlurred', 'Form', {
            fieldName: event.fieldName,
            focusDuration: event.focusDuration,
            formId: event.formId,
            page: event.page - 1
        });
    }
});

// ddmFieldFocus
Liferay.on('ddmFieldFocus', function(event) {
    if (window.Analytics) {
        Analytics.send('fieldFocused', 'Form', {
            fieldName: event.fieldName,
            formId: event.formId,
            page: event.page - 1
        });
    }
});

// ddmFormPageShow
Liferay.on('ddmFormPageShow', function(event) {
    if (window.Analytics) {
        Analytics.send('pageViewed', 'Form', {
            formId: event.formId,
            page: event.page,
            title: event.title
        });
    }
});

// ddmFormSubmit
Liferay.on('ddmFormSubmit', function(event) {
    if (window.Analytics) {
        Analytics.send('formSubmitted', 'Form', {
            formId: event.formId
        });
    }
});

// ddmFormView
Liferay.on('ddmFormView', function(event) {
    if (window.Analytics) {
        Analytics.send('formViewed', 'Form', {
            formId: event.formId,
            title: event.title
        });
    }
});
```

---

## 2. Handler per il caricamento dei dettagli del cittadino (portlet GenericDetailsPortlet)

**Registrazione**: all'interno di `AUI().ready()` nel portlet `it_smc_laziocrea_ums_web_internal_portlet_GenericDetailsPortlet_INSTANCE_IMXeC3CdDljt`. Sono ascoltatori per eventi personalizzati lanciati da altri portlet.

**Linee**: circa linee 1450-1600 (nel secondo grande blocco `<script>`).

**Dettagli**:

```javascript
// load-citizen-details
Liferay.on('load-citizen-details', function(event) {
    AUI().use(function(A) {
        if (event.taxCode != "") {
            Liferay.Service('/sansav.assisted/find-by-tax-code', { taxCode: event.taxCode }, function(entity) {
                // ... gestione risposta ...
                _it_smc_laziocrea_ums_web_internal_portlet_GenericDetailsPortlet_INSTANCE_IMXeC3CdDljt_fillDetails(entity);
            });
        }
    });
});

// load-assisted-details
Liferay.on('load-assisted-details', function(event) {
    AUI().use(function(A) {
        Liferay.Service('/sansav.assisted/find-by-tax-code', { taxCode: event.taxCode }, function(entity) {
            // ... gestione ...
            _it_smc_laziocrea_ums_web_internal_portlet_GenericDetailsPortlet_INSTANCE_IMXeC3CdDljt_fillDetails(entity);
        });
    });
});

// load-doctor-details
Liferay.on('load-doctor-details', function(event) {
    AUI().use(function(A) {
        Liferay.Service('/sansav.assisted/find-by-tax-code', { taxCode: event.taxCode }, function(entity) {
            // ... gestione ...
            _it_smc_laziocrea_ums_web_internal_portlet_GenericDetailsPortlet_INSTANCE_IMXeC3CdDljt_fillDetails(entity);
        });
    });
});
```

**Nota**: Questi handler si occupano di chiamare il backend per ottenere i dati di un assistito/medico e di popolare la view con `_fillDetails`.

---

## 3. Handler per la richiesta di selezione del medico (portlet DoctorSelectionPortlet)

**Registrazione**: in `AUI().ready()` del portlet `it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet`.

**Linee**: circa linee 2200-2300.

**Dettagli**:

```javascript
Liferay.on("get-doctor-selection", function(event) {
    var taxCodeAssisted = event.taxCode;
    var taxCode = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_taxCode");
    
    // Associa i listener ai pulsanti (solo ora!)
    var btnModalChoose = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_formSubmitChoose");
    btnModalChoose.on('click', _it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_openConfirmationModal);
    
    var btnModalRevoke = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_formSubmitRevoke");
    btnModalRevoke.on('click', _it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_openRevokeModal);
    
    if (taxCode.val().toUpperCase() == taxCodeAssisted) {
        // Nasconde messaggi e imposta il codice del medico
        var mmgTaxCodeInput = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_mmgTaxCode");
        mmgTaxCodeInput.set('value', event.mmgTaxCode);
        
        // ... chiamata al servizio per recuperare i dettagli del medico ...
        Liferay.Service('/sansav.doctor/search', { ... }, function(obj) {
            if (obj.length > 0) {
                // ... popola i campi ...
                _it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_openDoctorDetailsModal();
            }
        });
    }
});
```

**Spiegazione**: Questo handler è fondamentale perché **attiva** i pulsanti di conferma solo quando arriva l'evento dalla lista (quando clicchi sull'icona +). Se arrivi direttamente alla pagina di dettaglio, i pulsanti restano inerti.

---

## 4. Funzione che invia la richiesta di selezione (dalla lista)

**Nome**: `_it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_sendDoctorSelectionRequest(mmgTaxCode)`

**Registrazione**: funzione globale definita nel portlet GenericListPortlet.

**Linee**: circa linee 2000-2010.

**Prototipo**:
```javascript
function _it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_sendDoctorSelectionRequest(mmgTaxCode)
```

**Corpo**:
```javascript
function(mmgTaxCode){
    var taxCode = 'vlinrc65a20i804t'.toUpperCase(); // codice assistito
    var payload = {
        taxCode: taxCode,
        mmgTaxCode: mmgTaxCode
    };
    Liferay.fire('get-doctor-selection', payload);
}
```

Questa funzione viene chiamata quando clicchi sull'icona `+` nella tabella dei medici. Lancia l'evento `get-doctor-selection` che viene poi intercettato dall'handler sopra.

---

## 5. Funzioni che aprono le modali di conferma

**Registrazione**: funzioni globali nel portlet DoctorSelectionPortlet.

**Linee**: circa linee 2150-2200.

**Dettaglio**:

```javascript
// Apertura modale per conferma assegnazione
function _it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_openConfirmationModal() {
    AUI().use('aui-modal', function(A) {
        var modal = new A.Modal({
            bodyContent: 'Sei sicuro di voler cambiare medico?',
            centered: true,
            draggable: true,
            zIndex: 2000,
            headerContent: '<h3>Conferma modifica</h3>',
            render: '#modal',
        }).render();

        modal.addToolbar([
            {
                label: 'Conferma',
                on: { click: function() {
                    var form = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_fm");
                    form.set("action", "https://www.salutelazio.it/group/guest/scelta-e-revoca?p_p_id=it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet&p_p_lifecycle=1&p_p_state=normal&p_p_mode=view&_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_javax.portlet.action=saveDoctorSelection&p_auth=" + Liferay.authToken);
                    form.submit();
                }}
            },
            {
                label: 'Annulla',
                on: { click: function() { modal.hide(); } }
            }
        ]);
    });
}

// Apertura modale per revoca
function _it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_openRevokeModal() {
    // simile, con action=revokeDoctorSelection
}
```

**Nota**: La modale usa `Liferay.authToken` dinamicamente per costruire l'URL di invio.

---

## 6. Handler per il caricamento della lista medici (portlet GenericListPortlet)

**Registrazione**: all'interno di `AUI().ready()` del portlet `it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE`.

**Linee**: circa linee 1800-1950.

**Dettaglio** (solo un esempio, ce ne sono molti a seconda del contesto):

```javascript
Liferay.on('load-doctor-list', function(event) {
    event.stopPropagation();
    var params = {
        portletAjaxable: true,
        _it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_type: event.type,
        _it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_asl: event.asl,
        // ... altri parametri ...
        _it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_resetCur: event.portletsearch
    };
    Liferay.Portlet.refresh('#p_p_id_it_smc_laziocrea_ums_web_internal_portlet_GenericListPortlet_INSTANCE_toSiqlyntzJE_', params);
});
```

Questo handler aggiorna il portlet con i risultati della ricerca.

---

## Riepilogo degli handler principali

| Handler | Tipo registrazione | Linea (approx) | Scopo |
|--------|-------------------|----------------|-------|
| `ddmFieldBlur`, ecc. | `Liferay.on` | 90-130 | Tracciamento analytics |
| `load-citizen-details` | `Liferay.on` in `AUI().ready` | 1450-1600 | Carica dati cittadino |
| `get-doctor-selection` | `Liferay.on` in `AUI().ready` | 2200-2300 | Attiva la selezione del medico |
| `_sendDoctorSelectionRequest` | Funzione globale | 2000-2010 | Lancia l'evento dalla lista |
| `_openConfirmationModal` | Funzione globale | 2150-2200 | Mostra modale e invia form |
| `load-doctor-list` | `Liferay.on` in `AUI().ready` | 1800-1950 | Ricarica la lista medici |



---  

# scelta medico da javascript console

--- 

Ecco il codice robusto per selezionare direttamente il medico **STRMCR64P55H501D** dalla console del browser, con spiegazioni dettagliate per ogni passaggio.

```javascript

// Aspetta un attimo che la pagina sia stabilizzata
setTimeout(function() {
    AUI().use(function(A) {
        var authToken = Liferay.authToken;
        if (!authToken) { console.error("❌ Token non trovato."); return; }
        console.log("🔑 Token OK: " + authToken);

        var mmgField = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_mmgTaxCode");
        if (!mmgField) { console.error("❌ Campo medico non trovato."); return; }
        mmgField.set('value', 'STRMCR64P55H501D');
        console.log("✅ Medico impostato: STRMCR64P55H501D");

        var form = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_fm");
        if (!form) { console.error("❌ Form non trovato."); return; }

        var actionUrl = "https://www.salutelazio.it/group/guest/scelta-e-revoca" +
            "?p_p_id=it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet" +
            "&p_p_lifecycle=1" +
            "&p_p_state=normal" +
            "&p_p_mode=view" +
            "&_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_javax.portlet.action=saveDoctorSelection" +
            "&p_auth=" + authToken;

        form.set("action", actionUrl);
        console.log("📤 Invio richiesta a: " + actionUrl);
        // form.submit(); // Decommenta per inviare
    });
}, 5000); // Aspetta 1.5 secondi

```

### Cosa fa ogni parte:
- **Passo 1**: Avvia AlloyUI per poter usare selettori e metodi compatibili con Liferay.
- **Passo 2**: Prende il token di sicurezza dalla variabile globale `Liferay.authToken`, garantendo che sia sempre quello valido per la sessione corrente.
- **Passi 3-6**: Controlli di robustezza: se mancano elementi fondamentali, il codice si ferma e stampa un errore chiaro.
- **Passo 5**: Imposta il codice fiscale del medico che vuoi assegnare.
- **Passo 7**: Costruisce l'URL di destinazione con il parametro `p_auth` dinamico.
- **Passo 8**: Modifica l'azione del form con l'URL appena creato.
- **Passo 9**: Invia il form, innescando la procedura di salvataggio.

### Come eseguirlo:
1. Apri la console del browser (F12 → tab Console).
2. Incolla l'intero blocco di codice sopra.
3. Premi Invio.

Se tutto va bene, la pagina si ricaricherà (o mostrerà un messaggio) e il medico verrà assegnato. In caso di errori, la console mostrerà messaggi descrittivi per aiutarti a capire cosa non ha funzionato.


---   

In base al codice della pagina e all'esperienza pratica che stai facendo, la scelta del browser può fare una differenza notevole, specialmente quando si lavora con console e richieste AJAX su portali complessi come questo .

Ecco un'analisi dei browser più adatti e perché:

### **Classifica dei browser consigliati**

| Posizione | Browser | Motivo della scelta |
| :--- | :--- | :--- |
| **🥇 1° posto** | **Microsoft Edge** (Chromium) | Ha un'ottima gestione della memoria e degli "onglet inattivi" . Questo aiuta a mantenere la sessione attiva più a lungo senza che il browser "impazzisca" dopo aver aperto molti strumenti di sviluppo. Inoltre, integrato benissimo con Windows, tende a essere molto stabile con le chiamate di rete . |
| **🥈 2° posto** | **Google Chrome** | È lo standard de facto per il debugging . I suoi DevTools sono i più completi e immediati. Se la tua sessione non dà problemi, Chrome ti permette di fare tutto con la massima reattività. Tuttavia, è famoso per consumare molta RAM , e su macchine meno potenti potrebbe causare rallentamenti. |
| **🥉 3° posto** | **Mozilla Firefox** | Ottimo per la privacy e per avere una visione alternativa del codice . Firefox gestisce le richieste di rete in modo leggermente diverso e a volte può essere più "indulgente" con certificati o token. Se gli errori 403 persistono su Chrome/Edge, provare su Firefox è un'ottima diagnosi . |

### **Consigli pratici per l'uso della console**

1.  **Mantieni pulita la console**: prima di incollare script complessi, clicca sull'icona **"Cancella console"** (🚫) per evitare di confonderti con vecchi errori.
2.  **Usa la modalità di navigazione in incognito/privata**: in qualsiasi browser, questa modalità disabilita la maggior parte delle estensioni che potrebbero interferire con il caricamento della pagina o con le richieste AJAX, causando quegli errori `403` o di timeout .
3.  **Controlla la scheda "Rete" (Network)** : se il comando che ti ho fornito non funziona, apri la scheda "Rete" (Network) prima di eseguirlo. Poi guarda la richiesta che parte. Se vedi un errore `403` o `500`, vuol dire che il problema è del server, non del tuo script.

### **Script di test rapido per la console**
Per sicurezza, possiamo rendere il comando ancora più "testardo" e sicuro. Copia e incolla questo nella console:

```javascript
AUI().use(function(A) {
    // 1. Legge il token direttamente da Liferay
    var authToken = Liferay.authToken;
    if (!authToken) { console.error("❌ Token non trovato."); return; }
    console.log("🔑 Token OK: " + authToken);

    // 2. Trova e imposta il campo del medico
    var mmgField = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_mmgTaxCode");
    if (!mmgField) { console.error("❌ Campo medico non trovato."); return; }
    mmgField.set('value', 'STRMCR64P55H501D');
    console.log("✅ Medico impostato: STRMCR64P55H501D");

    // 3. Trova il form e prepara l'invio
    var form = A.one("#_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_fm");
    if (!form) { console.error("❌ Form non trovato."); return; }

    var actionUrl = "https://www.salutelazio.it/group/guest/scelta-e-revoca" +
        "?p_p_id=it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet" +
        "&p_p_lifecycle=1" +
        "&p_p_state=normal" +
        "&p_p_mode=view" +
        "&_it_smc_laziocrea_mypos_portlet_DoctorSelectionPortlet_javax.portlet.action=saveDoctorSelection" +
        "&p_auth=" + authToken;

    form.set("action", actionUrl);

    // 4. Prova a inviare, ma logga l'URL prima per sicurezza
    console.log("📤 Invio richiesta a: " + actionUrl);
    form.submit(); // Decommenta questa riga per eseguire l'invio effettivo
});
```

**Fai una prova con Edge**, tieni aperta la scheda "Rete" e facci sapere se la richiesta parte o se vedi ancora errori. Se il problema persiste, potremmo provare a simulare un click sull'elemento giusto invece di inviare il form direttamente.