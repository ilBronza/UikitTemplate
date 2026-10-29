"""Rebuild the local catalogue from reviewed JSON and saved screenshots. No network access."""
from pathlib import Path
from collections import defaultdict,Counter
import json,re,html,io,math
from editorial import FAMILIES,GROUPS,title,notes
from PIL import Image
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import A4
from reportlab.lib.colors import HexColor,Color
from reportlab.lib.utils import ImageReader
from reportlab.platypus import Paragraph
from reportlab.lib.styles import ParagraphStyle
ROOT=Path(__file__).resolve().parents[3]
OUT=ROOT/'docs/yootheme-catalogo'
SOURCE=json.loads((OUT/'data/catalogo.json').read_text())
V=SOURCE['variants'];L=SOURCE['layouts'];T=SOURCE['themes'];TS=SOURCE['theme_examples'];MAN=[]
(ROOT/'output/pdf').mkdir(parents=True,exist_ok=True)
for folder in ('data','famiglie','temi'):(OUT/folder).mkdir(exist_ok=True)
for v in V:
 v['title']=title(v);v['description']=notes(v);v['screenshot']='screenshots/'+v['key']+'.jpg'
 v['screenshot_available']=(OUT/v['screenshot']).exists();v['usage']=FAMILIES[v['family']][1]
 v['theme']='Fuse (demo Widgetkit)' if v['group']=='widgetkit' else 'UIkit (demo elementi)'
for t in TS:
 m=next((m for m in MAN if m['key']==t['key']),{})
 t.update({k:v for k,v in m.items() if k in ('screenshot','capture_section','capture_family','capture_note')})
 t.setdefault('screenshot','screenshots/'+t['key']+'.jpg');t['screenshot_available']=(OUT/t['screenshot']).exists()
 t['title']=t['theme']+' / '+(t['section'] if t['section']!='(inizio pagina)' else t['layout_name'])
 t['description']='; '.join(filter(None,[t['family'],t['layout_name']]))+'.'
 t['description']+= ' '+t.get('capture_note','')
COMPARISON_NOTES={
 'theme-design-escapes-home':'Fotografie ampie, spazi ridotti e testo sotto il media: località in evidenza e nome della casa. Il ritaglio lascia intravedere la scheda successiva. Utile per alloggi, prodotti o contenuti con un’etichetta sempre leggibile.',
 'theme-vision-home':'Tre progetti affiancati nel desktop, distanze più ampie e navigazione a puntini. Titoli e azioni sono sovrapposti al media con transizioni; il markup imposta avanzamento per gruppi. Utile per portfolio e lavori da esplorare.',
 'theme-design-escapes-detail-46':'Carosello di immagini con slide attiva centrata e frecce laterali. Le immagini adiacenti restano parzialmente visibili.',
 'theme-vision-detail-48':'Carosello centrato di progetti con testo sovrapposto, frecce e puntini. Il markup configura avanzamento automatico ogni 6 secondi.'
}
for t in TS:
 if t['key'] in COMPARISON_NOTES:t['description']=COMPARISON_NOTES[t['key']];t['capture_note']=COMPARISON_NOTES[t['key']]
COUNTS={'variants':len(V),'core':sum(v['group'] in ('widgetkit','elements') for v in V),'base':sum(v['group'] in ('basic','system') for v in V),'demo_pages':len(set(v['page_url'] for v in V)),'themes':len(T),'layouts':len(L),'layouts_read':sum(l['ok'] for l in L),'occurrences':sum(len(l['widgets']) for l in L),'screenshots':sum(v['screenshot_available'] for v in V)+sum(t['screenshot_available'] for t in TS),'theme_screenshots':sum(t['screenshot_available'] for t in TS)}
for t in T:t['cards']=[c for c in t['cards'] if c['kind']=='layout']
DATA={'date':'2026-10-28','scope':'Demo pubbliche nominate e repertorio dei layout pubblicati; varianti di solo colore escluse. Le occorrenze dei temi sono rilevate dal markup e non equivalgono a preset distinti.','counts':COUNTS,'variants':V,'themes':T,'theme_examples':TS,'layouts':L}
(OUT/'data/catalogo.json').write_text(json.dumps(DATA,ensure_ascii=False,indent=2))
by=defaultdict(list)
for v in V:by[(v['group'],v['family'])].append(v)

def escape_md(s):return str(s).replace('|','/').replace('\n',' ')
def block(v,base='..'):
 s=f"### {v['title']}\n\n{v['description']}\n\n"
 if v['screenshot_available']:s+=f"![{v['title']}]({base}/{v['screenshot']})\n\n"
 else:s+='Anteprima non acquisita.\n\n'
 s+=f"[Apri la demo]({v['url']}) · Riferimento: `{v['anchor']}`.\n\n"
 return s
for (g,f),variants in by.items():
 name,usage,content=FAMILIES[f]
 text=f'# {name} - {GROUPS[g]}\n\n[Indice del catalogo](../README.md) · [PDF offline](../../../output/pdf/catalogo-widget-yootheme.pdf)\n\n{usage}\n\n**Contenuti:** {content}\n\nFonte grafica: {variants[0]["theme"]}. Le demo generiche non dichiarano il tema originale di ogni preset; non viene dedotto dalle immagini.\n\n'
 text+='| Variante | Demo |\n|---|---|\n'
 for v in variants:text+=f"| {escape_md(v['title'])} | [Esempio]({v['url']}) |\n"
 text+='\n'
 for v in variants:text+=block(v)
 (OUT/f'famiglie/{g}-{f}.md').write_text(text)
for theme in T:
 layouts=[l for l in L if l['theme_slug']==theme['slug']]
 text=f"# {theme['name']}\n\n[Indice del catalogo](../README.md) · [Scheda ufficiale]({theme['url']})\n\nLayout e applicazioni dei widget nel tema. Le palette alternative sono escluse.\n\n"
 for t in TS:
  if t['theme']!=theme['name']:continue
  text+=f"## {t['section'] if t['section']!='(inizio pagina)' else t['layout_name']}\n\n**{t['family']}** · {t['layout_name']}\n\n"
  if t['screenshot_available']:text+=f"![{t['title']}](../{t['screenshot']})\n\n"
  text+=t.get('capture_note','')+'\n\n'
  text+=f"[Apri l’esempio]({t['url']})\n\n"
 text+='## Layout censiti\n\nI tipi elencati sono riconosciuti dal markup UIkit. Un componente può ripetersi in più layout o avere una versione desktop e una mobile. La tabella non conta preset univoci.\n\n| Layout | Componenti rilevati | Stato |\n|---|---|---|\n'
 for l in layouts:
  cnt=Counter(w['family'] for w in l['widgets']);kinds=', '.join(f'{k} × {n}' for k,n in cnt.items()) or 'Nessun componente delle famiglie rilevate'
  state='HTML letto' if l['ok'] else ('Pagina 404 dimostrativa' if l['name']=='Error 404 Layout' else 'Demo non disponibile alla verifica')
  text+=f"| [{escape_md(l['name'])}]({l['url']}) | {escape_md(kinds)} | {state} |\n"
 text+='\n## Riferimenti nel layout\n\n'
 for l in layouts:
  if not l['widgets']:continue
  text+=f"### {l['name']}\n\n"
  for i,w in enumerate(l['widgets'],1):text+=f"- {w['family']}: {escape_md(w['section'])}.\n"
  text+='\n'
 (OUT/f'temi/{theme["slug"]}.md').write_text(text)

READ=f'''# Catalogo dei widget YOOtheme

Riferimento per scegliere quali widget e varianti riprodurre negli helper di UikitTemplate. Edizione del 28 ottobre 2026.

**[Apri il PDF illustrato offline](../../output/pdf/catalogo-widget-yootheme.pdf)**

Il catalogo comprende **{COUNTS['variants']} schede di esempio**, di cui **{COUNTS['core']} per widget ed elementi a raccolta**, e **{COUNTS['base']} per componenti di base e di sistema**. Le varianti di solo colore sono escluse. Le anteprime sono screenshot reali salvati nel repository, non ricostruzioni.

## Come scegliere

| Esigenza | Famiglia da guardare | Distinzione utile |
|---|---|---|
| Confrontare prodotti, servizi o persone | [Grid](famiglie/elements-grid.md) | Tutte le schede restano consultabili nella pagina |
| Mostrare fotografie e portfolio | [Gallery](famiglie/elements-gallery.md) | L’immagine guida la composizione; lightbox opzionale |
| Scorrere schede con testo | [Panel Slider](famiglie/elements-panel-slider.md) | Testo separato o affiancato al media |
| Scorrere immagini con testo sopra | [Overlay Slider](famiglie/elements-overlay-slider.md) | Testo sovrapposto alla copertina |
| Una presentazione principale alla volta | [Slideshow](famiglie/elements-slideshow.md) | Hero, transizioni, miniature e media video |
| FAQ o dettagli su richiesta | [Accordion](famiglie/elements-accordion.md) | Apertura singola o multipla |
| Approfondimenti divisi per argomento | [Switcher](famiglie/elements-switcher.md) | Tab, pulsanti o miniature selezionano il pannello |
| Specifiche o listini | [Description List](famiglie/elements-description-list.md) / [Table](famiglie/elements-table.md) | Coppie etichetta-valore o confronto per colonne |
| Punti interattivi su un’immagine | [Popover](famiglie/elements-popover.md) | Hotspot con contenuti su clic o hover |
| Immagini e testo in righe narrative | [Grid Stack](famiglie/widgetkit-grid-stack.md) | Righe affiancate, alternate o senza margini |

## Il confronto fra temi

**[Confronto illustrato: Design Escapes e Vision](confronto-caroselli.md)**

[Design Escapes](temi/design-escapes.md) e [Vision](temi/vision.md) sono confrontabili tramite screenshot tratti dalle loro demo reali. Il catalogo mantiene separate la famiglia del widget, la sua composizione e il tema che la presenta.

Le demo generiche degli elementi usano UIkit e quelle Widgetkit usano Fuse. Quando la provenienza di un preset da un altro tema non è dichiarata, non viene attribuita per somiglianza visiva.

## Indice delle famiglie

'''
for g in GROUPS:
 READ+=f'### {GROUPS[g]}\n\n| Famiglia | Esempi | Uso |\n|---|---:|---|\n'
 for (group,f),vs in by.items():
  if group==g:READ+=f'| [{FAMILIES[f][0]}](famiglie/{g}-{f}.md) | {len(vs)} | {FAMILIES[f][1]} |\n'
 READ+='\n'
READ+='## Indice dei temi\n\nOgni scheda riporta un’anteprima rappresentativa e tutti i layout collegati dalla pagina ufficiale del tema. Design Escapes e Vision hanno anche esempi aggiuntivi.\n\n| Tema | Layout indicizzati | Scheda |\n|---|---:|---|\n'
for t in T:READ+=f'| {t["name"]} | {len([l for l in L if l["theme_slug"]==t["slug"]])} | [Apri](temi/{t["slug"]}.md) |\n'
READ+=f'''
## Copertura e criteri

- **{COUNTS['demo_pages']} pagine dimostrative** collegate dalla documentazione: 15 Widgetkit, 16 elementi a raccolta, 14 componenti di base e 5 elementi di sistema.
- **{COUNTS['themes']} temi e {COUNTS['layouts']} riferimenti a layout** pubblicati nell’indice Joomla ufficiale; i collegamenti sono deduplicati all’interno di ogni tema.
- **{COUNTS['layouts_read']} layout leggibili** e **{COUNTS['occurrences']} occorrenze** riconosciute dal markup. Le occorrenze non sono varianti univoche: includono riuso fra pagine e composizioni responsive.
- **{COUNTS['screenshots']} screenshot**, inclusi **{COUNTS['theme_screenshots']} esempi dei temi**. Le anteprime dei temi sono rappresentative; i 535 layout non hanno ciascuno una recensione visuale completa.
- Sono escluse le palette alternative dei temi e 31 varianti dimostrative identificate come differenze di solo colore.
- Gli screenshot mostrano uno stato desktop della demo; per i blocchi alti è riportato il ritaglio visibile. Hover, passaggi fra slide, filtri e comportamento mobile vanno verificati attraverso il collegamento originale prima di definire un helper.
- Dodici riferimenti sono pagine 404 dimostrative. Tre ulteriori demo hanno restituito 404: la ricerca di Union Dental, il profilo Mike Marinelli di DevStack e la ricerca di Design Escapes.
- Questo è un censimento degli esempi pubblici e dei layout pubblicati, non un’esportazione della libreria privata di YOOtheme Pro né di tutte le combinazioni possibili del builder.

## Convenzioni per il lavoro sugli helper

Le chiavi nelle schede e in `data/catalogo.json` servono a ritrovare gli esempi. Non sono ancora nomi di classi, metodi o preset approvati per il package. Prima dell’implementazione possiamo selezionare le varianti utili e separare contenuto, struttura, comportamento e stile del tema.

## Fonti e aggiornamento

- [Widgetkit e relativi widget](https://yootheme.com/widgetkit/docs/widget-plugins)
- [Elementi a raccolta](https://yootheme.com/docs/wordpress/multiple-items-elements)
- [Componenti di base](https://yootheme.com/docs/wordpress/basic-elements)
- [Elementi di sistema](https://yootheme.com/docs/wordpress/system-elements)
- [Indice ufficiale dei temi](https://yootheme.com/joomla-templates)

Ogni scheda contiene il collegamento preciso alla demo. Per rigenerare README, schede e PDF dagli stessi dati e screenshot: `python3 docs/yootheme-catalogo/tools/build_catalogue.py` dalla radice del repository (richiede `reportlab` e `Pillow`). La rigenerazione non aggiorna il censimento online. Il file [catalogo.json](data/catalogo.json) conserva riferimenti, opzioni UIkit osservate, classi responsive, copertura e immagini associate. Per aggiornare il catalogo occorre rileggere gli indici e riacquisire gli screenshot delle demo modificate. I nomi e le immagini delle demo appartengono ai rispettivi titolari; gli screenshot sono riferimenti per questo lavoro di analisi.
'''
(OUT/'README.md').write_text(READ)
comparison='# Caroselli: Design Escapes e Vision\n\n[Indice del catalogo](README.md)\n\nLa differenza riguarda la composizione e il comportamento oltre allo stile. Le osservazioni qui sotto derivano dalle anteprime desktop e dalle opzioni UIkit presenti nelle demo.\n\n'
for key in ['theme-design-escapes-home','theme-vision-home','theme-design-escapes-detail-46','theme-vision-detail-48']:
 t=next(t for t in TS if t['key']==key)
 comparison+=f"## {t['title']}\n\n{t['description']}\n\n![{t['title']}]({t['screenshot']})\n\n[Apri la demo]({t['url']})\n\n"
comparison+='## Cosa comporta per gli helper\n\nQuesti esempi suggeriscono due composizioni da valutare separatamente: scheda con testo sotto il media e scheda con testo sovrapposto. Centratura, avanzamento per gruppi, puntini, frecce e autoplay sono opzioni di comportamento. Spaziature, proporzioni e tipografia possono diventare preset del tema. I nomi e i parametri dell’API restano da concordare.\n'
(OUT/'confronto-caroselli.md').write_text(comparison)


# PDF: two illustrated examples per page, clickable source links and outline.
PDF=ROOT/'output/pdf/catalogo-widget-yootheme.pdf'; W,H=A4; M=42; CW=W-2*M
C=canvas.Canvas(str(PDF),pagesize=A4,pageCompression=1)
C.setTitle('Catalogo dei widget YOOtheme');C.setAuthor('UikitTemplate');C.setSubject('Varianti strutturali, anteprime e riferimenti alle demo pubbliche')
INK=HexColor('#1b2930');MUTED=HexColor('#51636c');ACCENT=HexColor('#12675a');PALE=HexColor('#f0f5f4');LINE=HexColor('#dbe4e3')
styles={k:ParagraphStyle(k,fontName='Helvetica',fontSize=size,leading=lead,textColor=INK) for k,size,lead in [('body',10,14),('small',8.5,11),('caption',9,12),('title',13,16)]}
page=0;family_pages={};theme_pages={};bookmarks=[]
def clean(s):return str(s).replace('–','-').replace('—','-').replace('‑','-')
def para(s,x,y,width,style='body'):
 p=Paragraph(html.escape(clean(s)).replace('\n','<br/>'),styles[style]);pw,ph=p.wrap(width,H);p.drawOn(C,x,y-ph);return ph

def newpage(section,bookmark=None):
 global page
 if page:C.showPage()
 page+=1;C.setFillColor(INK);C.setFont('Helvetica',8);C.drawString(M,H-26,'UIKIT TEMPLATE  /  CATALOGO YOOTHEME');C.setFillColor(MUTED);C.drawRightString(W-M,H-26,clean(section))
 C.setStrokeColor(LINE);C.line(M,35,W-M,35);C.setFont('Helvetica',8);C.drawString(M,23,'28 ottobre 2026  |  Esempi pubblici  |  Varianti colore escluse');C.drawRightString(W-M,23,str(page))
 if bookmark:C.bookmarkPage(bookmark)
 return H-57

def heading(s,y,size=24):
 C.setFillColor(INK);C.setFont('Helvetica-Bold',size);C.drawString(M,y,clean(s));return y-size-10

def image_box(path,x,y,w,h):
 im=Image.open(path).convert('RGB');iw,ih=im.size;scale=min(w/iw,h/ih,1.0);dw,dh=iw*scale,ih*scale
 C.drawImage(ImageReader(str(path)),x+(w-dw)/2,y-dh,width=dw,height=dh,mask='auto')
 return dh

def source_link(label,url,x,y):
 C.setFillColor(ACCENT);C.setFont('Helvetica',8.5);C.drawString(x,y,clean(label));C.linkURL(url,(x,y-3,x+C.stringWidth(clean(label),'Helvetica',8.5),y+10),relative=0,thickness=0)

def card(v,y,height=310):
 C.setFillColor(INK);used=para(v['title'],M,y,CW,'title');y2=y-used-6
 d=v['description']; used=para(d,M,y2,CW,'caption');y2-=used+8
 maximg=min(215,y2-(y-height+40))
 p=OUT/v['screenshot']
 if p.exists(): image_box(p,M,y2,CW,maximg)
 else:para('Anteprima non disponibile: consulta la demo collegata.',M,y2,CW,'caption')
 bottom=y-height+16
 source_link('Apri la demo originale',v['url'],M,bottom)
 if 'anchor' in v:
  C.setFillColor(MUTED);C.setFont('Helvetica',6.9);ref=clean(v['anchor']);C.drawRightString(W-M,bottom,ref[:90])
 C.setStrokeColor(LINE);C.line(M,y-height+5,W-M,y-height+5)

# Cover
newpage('Guida alla scelta','cover');C.setFillColor(ACCENT);C.rect(M,H-142,58,5,fill=1,stroke=0)
C.setFillColor(INK);C.setFont('Helvetica-Bold',35);C.drawString(M,H-196,'Widget e varianti');C.drawString(M,H-239,'YOOtheme')
para('Catalogo illustrato per scegliere i componenti da riprodurre negli helper di UikitTemplate.',M,H-280,CW,'title')
C.setFillColor(PALE);C.rect(M,H-450,CW,96,fill=1,stroke=0)
C.setFillColor(INK);C.setFont('Helvetica-Bold',25);C.drawString(M+18,H-390,str(COUNTS['variants']));C.drawString(M+190,H-390,str(COUNTS['themes']));C.drawString(M+335,H-390,str(COUNTS['screenshots']))
C.setFont('Helvetica',10);C.drawString(M+18,H-414,'schede di esempio');C.drawString(M+190,H-414,'temi indicizzati');C.drawString(M+335,H-414,'screenshot reali')
para('Le differenze considerate riguardano struttura, contenuti, navigazione ed effetti. Le sole variazioni di colore sono escluse.',M,H-480,CW)
para('Le schede sono divise in widget ed elementi a raccolta, componenti di base, elementi di sistema e applicazioni nei temi. Ogni esempio rimanda alla demo pubblica originale.',M,H-545,CW)
para('Edizione del 28 ottobre 2026. Gli screenshot sono stati acquisiti da un browser desktop; mostrano uno stato dell’interfaccia, non tutte le interazioni o i breakpoint.',M,H-625,CW,'small')

# Choice guide
newpage('Guida alla scelta','choice');C.addOutlineEntry('Guida alla scelta','choice',level=0,closed=False);y=heading('Da quale widget partire',H-65)
choices=[('Schede da confrontare','Grid','Prodotti, servizi, articoli e persone.'),('Immagini e portfolio','Gallery','Immagini, filtri e lightbox.'),('Schede che scorrono','Panel Slider','Testo leggibile separato dal media.'),('Immagini con testo sopra','Overlay Slider','Categorie, progetti e destinazioni.'),('Una slide principale','Slideshow','Hero e presentazioni multimediali.'),('FAQ e dettagli su richiesta','Accordion','Apertura singola o multipla.'),('Argomenti selezionabili','Switcher','Tab, pulsanti e miniature.'),('Specifiche o listini','Description List / Table','Coppie etichetta-valore o colonne.'),('Hotspot su una figura','Popover','Punti interattivi su immagini e planimetrie.'),('Righe di testo e immagine','Grid Stack','Racconto editoriale e righe alternate.')]
for need,family,use in choices:
 C.setFillColor(ACCENT);C.setFont('Helvetica-Bold',11);C.drawString(M,y,family);para(need+' - '+use,M+165,y+2,CW-165,'small');y-=51;C.setStrokeColor(LINE);C.line(M,y+13,W-M,y+13)
para('Per scegliere la variante, confronta posizione del testo, proporzioni delle immagini, numero di elementi visibili, navigazione e comportamento. Per hover, filtri e animazioni apri la demo.',M,155,CW)

# Direct visual comparison requested by the user.
newpage('Confronto fra temi','comparison');C.addOutlineEntry('Caroselli: Design Escapes e Vision','comparison',level=0,closed=False)
heading('Due modi di costruire un carosello',H-65,21)
para('Testo separato dal media oppure sovrapposto: cambia la lettura della scheda e il comportamento da riprodurre.',M,H-98,CW,'small')
for i,key in enumerate(['theme-design-escapes-home','theme-vision-home']):
 t=next(t for t in TS if t['key']==key);card(t,H-145-i*315,305)

# Scope and sources
newpage('Copertura','scope');y=heading('Che cosa contiene il catalogo',H-65)
paragraphs=[f"Sono censite {COUNTS['demo_pages']} pagine demo collegate dalla documentazione ufficiale: 15 Widgetkit, 16 elementi a raccolta, 14 componenti di base e 5 elementi di sistema. Le {COUNTS['variants']} schede conservano gli identificativi pubblici quando disponibili.",f"Il repertorio dei temi comprende {COUNTS['themes']} temi e {COUNTS['layouts']} collegamenti a layout. Di questi, {COUNTS['layouts_read']} sono stati letti e hanno prodotto {COUNTS['occurrences']} occorrenze riconosciute dal markup. Non sono altrettanti preset: includono riuso e varianti responsive.",f"Le applicazioni nei temi sono illustrate da {COUNTS['theme_screenshots']} screenshot rappresentativi. I dettagli e i collegamenti di tutti i layout sono consultabili nelle schede dei temi del README. Non tutti i 535 layout hanno una recensione visuale completa.","Le demo generiche usano Fuse o UIkit. Non viene attribuita a un tema la provenienza di un preset quando YOOtheme non la dichiara. Il confronto fra temi usa invece le demo del tema indicato.","Le palette alternative e 31 varianti di solo colore sono escluse. La libreria privata installabile di YOOtheme Pro e tutte le combinazioni possibili del builder non sono esportate.","Gli screenshot sono riferimenti desktop. Prima di implementare una variante, verifica nella demo interazioni, accessibilità e comportamento ai breakpoint necessari. Le chiavi del catalogo non sono ancora convenzioni approvate per gli helper."]
for s in paragraphs:y-=para(s,M,y,CW)+18
for label,url in [('Documentazione Widgetkit','https://yootheme.com/widgetkit/docs/widget-plugins'),('Elementi a raccolta','https://yootheme.com/docs/wordpress/multiple-items-elements'),('Indice dei temi','https://yootheme.com/joomla-templates')]:source_link(label,url,M,y);y-=22

# Reserve outline summary after full compilation through link destinations (not page numbers).
newpage('Indice','index');C.addOutlineEntry('Indice delle famiglie','index',level=0,closed=False);y=heading('Indice delle famiglie',H-65)
for g in GROUPS:
 C.setFillColor(ACCENT);C.setFont('Helvetica-Bold',12);C.drawString(M,y,GROUPS[g]);y-=22
 for (group,f),vs in by.items():
  if group!=g:continue
  if y<70:newpage('Indice');y=H-65
  label=FAMILIES[f][0]+'  ('+str(len(vs))+')';C.setFillColor(INK);C.setFont('Helvetica',9);C.drawString(M+8,y,label);C.linkRect('',g+'-'+f,(M+8,y-3,W-M,y+10),relative=0,thickness=0);y-=15
 y-=13

for (g,f),vs in by.items():
 for offset in range(0,len(vs),2):
  bookmark=g+'-'+f if offset==0 else None
  newpage(GROUPS[g],bookmark)
  if offset==0:
   family_pages[g+'-'+f]=page;C.addOutlineEntry(GROUPS[g]+' / '+FAMILIES[f][0],bookmark,level=0,closed=False)
  y=heading(FAMILIES[f][0],H-64,22)
  para(FAMILIES[f][1],M,y,CW,'small')
  for i,v in enumerate(vs[offset:offset+2]):card(v,H-130-i*315,305)

for offset in range(0,len(TS),2):
 newpage('Varianti nei temi','theme-examples' if offset==0 else None)
 if offset==0:C.addOutlineEntry('Applicazioni nei temi','theme-examples',level=0,closed=False)
 heading('Applicazioni nei temi',H-64,22)
 para('Uno screenshot rappresentativo per tema e confronti aggiuntivi di Design Escapes e Vision.',M,H-95,CW,'small')
 for i,t in enumerate(TS[offset:offset+2]):
  if t['theme'] not in theme_pages:
   theme_pages[t['theme']]=page
   slug=next(theme['slug'] for theme in T if theme['name']==t['theme'])
   C.bookmarkPage('theme-'+slug);C.addOutlineEntry(t['theme'],'theme-'+slug,level=1,closed=True)
  card(t,H-130-i*315,305)

newpage('Temi e fonti','theme-index');C.addOutlineEntry('Indice dei temi','theme-index',level=0,closed=False);heading('Indice dei temi',H-65);y=H-108
for t in T:
 if y<70:newpage('Temi e fonti');y=H-65
 C.setFillColor(INK);C.setFont('Helvetica',9);label=f"{t['name']}  -  {len(t['cards'])} layout";C.drawString(M,y,label);C.linkRect('', 'theme-'+t['slug'],(M,y-3,W-M-115,y+10),relative=0,thickness=0)
 source_link('Scheda ufficiale',t['url'],W-M-105,y);y-=15
newpage('Note di consultazione');y=heading('Riferimenti e verifiche',H-65)
for s in ["Tutte le anteprime di questo PDF sono screenshot reali delle demo pubbliche YOOtheme. Immagini, nomi e contenuti dimostrativi appartengono ai rispettivi titolari. I collegamenti in ogni scheda consentono di verificare l’esempio originale.","Per consultare tutti i layout di un tema e le famiglie rilevate, apri docs/yootheme-catalogo/README.md nel repository. La cartella famiglie contiene le schede con immagini a risoluzione originale; temi contiene il repertorio dei layout; data/catalogo.json contiene i metadati del censimento.","Dodici riferimenti del repertorio sono pagine 404 dimostrative. Tre ulteriori demo hanno restituito 404: ricerca Union Dental, profilo Mike Marinelli di DevStack e ricerca Design Escapes. Questi casi sono segnalati nelle schede dei temi.","Il materiale serve alla scelta e alla progettazione degli helper. L’implementazione nel package, i nomi dell’API e le convenzioni dei parametri restano da definire."]:
 y-=para(s,M,y,CW)+20
C.save()
(OUT/'data/pdf-index.json').write_text(json.dumps({'pages':page,'families':family_pages,'themes':theme_pages},ensure_ascii=False,indent=2))
print(json.dumps({'counts':COUNTS,'pdf_pages':page,'pdf_bytes':PDF.stat().st_size},ensure_ascii=False))
