# Pagemanager\_XH

Pagemanager\_XH vereinfacht die Verwaltung von Seiten in einer
CMSimple\_XH-Installation. Dieses Plugin ist vom Funktionsumfang
vergleichbar mit dem Menumanager-Plugin, ist aber durch die
Baumdarstellung auch bei umfangreichen Homepages übersichtlicher.
Darüber hinaus können beim Pagemanager\_XH ganze Menüstrukturen auf
einmal verschoben werden.

## Inhaltsverzeichnis

  - [Voraussetzungen](#voraussetzungen)
  - [Download](#download)
  - [Installation](#installation)
  - [Einstellungen](#einstellungen)
  - [Verwendung](#verwendung)
  - [Beschränkungen](#beschränkungen)
  - [Fehlerbehebung](#fehlerbehebung)
  - [Lizenz](#lizenz)
  - [Danksagung](#danksagung)

## Voraussetzungen

Pagemanager\_XH ist ein Plugin für CMSimple\_XH ≥ 1.7. Zusätzlich
benötigt es noch das jQuery4CMSimple und das Fa\_XH Plugin, die in
allen Standarddownloads von CMSimple\_XH bereits enthalten sind. Es
benötigt ebenfalls PHP ≥ 5.3.0 mit der JSON Extension.

## Download

Das [aktuelle Release](https://github.com/cmb69/pagemanager_xh/releases/latest) kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple\_XH-Plugins
auch. Im [CMSimple\_XH
Wiki](https://wiki.cmsimple-xh.org/doku.php/de:installation) finden
sie ausführliche Hinweise.

1.  Sichern Sie die Daten auf Ihrem Server.
2.  Entpacken Sie die ZIP-Datei auf Ihrem Computer.
3.  Laden Sie das gesamte Verzeichnis pagemanager/ auf Ihren Server in
    das plugins/ Verzeichnis von CMSimple\_XH hoch.
4.  Vergeben Sie Schreibrechte für die Unterverzeichnisse css/, config/
    und languages/.
5.  Navigieren Sie zu Administration des Pagemanagers (*Plugins* →
    *Pagemanager*), und prüfen Sie, ob alle Voraussetzungen für den
    Betrieb erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple\_XH-Plugins auch im Administrationsbereich der Homepage. Wählen
Sie unter *Plugins* → *Pagemanager* aus.

Sie können die Original-Einstellungen von Pagemanager\_XH in der
*Konfiguration* ändern. Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.

Die Lokalisierung wird unter *Sprache* vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen, falls keine
entsprechende Sprachdatei zur Verfügung steht, oder sie entsprechend
Ihren Anforderungen anpassen.

Das Aussehen von Pagemanager\_XH kann unter *Stylesheet* angepasst
werden.

## Verwendung

Dieses Plugin ist ausschließlich für den Administrationsbereich von
CMSimple\_XH gedacht. Es wird über *Seiten* im Administrationsmenü
aufgerufen. Nun sehen Sie die Struktur Ihrer Homepage in einer
Darstellung, die prinzipiell dem Inhaltsverzeichnis entspricht.
Allerdings können Sie hier einzelne Seitenzweige öffnen und schließen,
um auch bei umfangreichen Homepages nicht die Übersicht zu verlieren.
Das Öffnen und Schließen erfolgt über die kleinen Markierungen links von
der Seite, oder durch Doppel-Klicken auf den Titel.

Oberhalb der Baumansich der Seiten Ihrer Homepage befindet sich
Pagemanager\_XHs **Werkzeugleiste**. Die Werkzeugleiste ist besonders
nützlich, wenn Ihr Browser verhindert, dass sich das Kontext-Menü
öffnet. Es ist zu beachten, dass die Toolbar in der
Plugin-Konfiguration deaktiviert werden kann.

Das Umorganisieren der Seitenstruktur erfolgt per **Drag & Drop**; beim
Ziehen einer Seite werden entsprechende Markierungen eingeblendet, damit
Sie sehen können, wo die Seite beim Ablegen eingeordnet wird. Sollte es
nicht möglich sein auf eine bestimmte Seite zu ziehen, weil das z.B. in
einer rekursiven Seitenstruktur resultieren würde, oder die
resultierende Verschachtelungstiefe zu groß würde, wird das durch ein
Kreuz angezeigt, oder es wird einfach nicht möglich sein, die Seite hier
abzulegen. *Also beachten sie genau die Markierungen*, bis Sie ein
Gefühl dafür bekommen, wie das Drag & Drop funktioniert. Wenn Sie beim
Ziehen einer Seite STRG gedrückt halten, wird die Seite kopiert statt
verschoben.

Weitere Funktionen sind über die Werkzeugleiste und das **Kontextmenü**
(klicken Sie mit der rechten Maustaste auf eine Seite) verfügbar. Es
können neue Seiten hinzugefügt, bestehende umbenannt und gelöscht
werden (*wenn die gewählte Seite gelöscht wird, werden alle ihre
Unterseiten ebenfalls gelöscht*), es stehen die üblichen
Zwischenablagefunktionen zur Verfügung, die alternativ zu Drag & Drop
verwendet werden können, und es kann direkt zu einer Seite navigiert
werden, entweder im *Bearbeitungs-* oder *Ansichtsmodus*. Es ist zu
beachten, dass aktuell nicht verfügbare Funktionalität deaktiviert ist.
Beispielsweise erfordern die meisten Funktionen, dass eine Seite
ausgewählt ist; ist dies nicht der Fall, dann sind die entsprechenden
Funktionen deaktiviert (ausgegraut). Ein weiteres Beispiel ist die
*Einfügen* → *hinein* Funktionalität, die nicht verfügbar ist, wenn die
Seite im Clipboard in sich selbst eingefügt werden soll.

Die Checkboxen links von den Seiten erlauben es, deren
**Veröffentlichungsstatus** anzusehen und zu ändern. Es kann
eingestellt werden, ob sie sich auf 'Veröffentlicht?' oder 'In der
Navigation anzeigen' beziehen. Wenn diese Einstellung leer ist
(Voreinstellung), dann werden keine Checkboxen angezeigt.

**Doppelte Überschriften** werden mit einem Warn-Icon markiert, und
sollten am besten gleich umbenannt werden. **Neu erstellte Seiten**
werden mit einem ausgefüllten Ordner-Icon angezeigt bis zum nächsten mal
gespeichert wird, damit sie besser von bereits bestehenden unterschieden
werden können. **Seiten, die nicht umbenannt werden können**, weil ihre
Überschriften zusätzliches Markup enthalten, werden mit einem
*Marke*-Icon markiert; solche Seiten sind erlaubt, aber wenn das
zusätzliche Markup entfernt werden soll, muss dies im Editor erfolgen.

Die Möglichkeit ganze Unterstrukturen zu kopieren mag auf den ersten
Blick nicht einleuchtend sein, aber sie könnte nützlich werden, wenn
z.B. eine Galerie auf diesen Seiten anzeigt wird, denn der gesamte
Inhalt *und* die Metadaten werden ebenso mit kopiert. Danach ist es
möglich, einzelne Details nachträglich anzupassen.

Es ist zu beachten, dass es keine Rückgängig- oder Abbrechen-Funktion
gibt. Wurde die Seitenstruktur total durcheinander gebracht, kann
einfach die Ansicht des Browsers aktualisiert werden *ohne* vorher zu
speichern. Die alte Seitenstruktur wird dann wieder angezeigt.

## Beschränkungen

### Unregelmäßige Seitenstruktur

Es is möglich, dass die bestehende Homepage eine unregelmäßige
Seitenstruktur aufweist. Zum Beispiel kann nach einem `<h1>` Überschrift
direkt eine `<h3>` Überschrift folgen, ohne eine `<h2>` Überschrift
dazwischen. Solche Unregelmäßigkeiten in der Seitenstruktur könnten sich
versehentlich eingeschlichen haben, während die Seitenstruktur manuell
im Editor verändert wurde (z.B. durch Änderung von Titel-Formatierungen,
oder Löschen von Seiten), aber es ist denkbar, dass diese Möglichkeit
von Ihrem System zu einem speziellen Zweck verwendet wird.

Jedenfalls kann Pagemanager nicht mit solchen unregelmäßigen
Seitenstrukturen umgehen, und wenn eine solche entdeckt wird, wird eine
Meldung angezeigt und angeboten die Struktur zu korrigieren. Wenn die
Unregelmäßigkeit versehentlich entstanden ist, kann beruhigt bestätigt
und fortgefahren werden. Andernfalls (oder wenn Sie sich nicht sicher
sind), erstellen Sie eine Sicherungskopie der Inhaltsdatei bevor Sie
fortfahren, speichern dann die korrigierte Struktur im Pagemanager, und
prüfen Sie sorgfältig, ob alles immer noch wie gewünscht funktioniert.

### jQuery

Pagemanager\_XH *könnte* in Installationen mit jQuery abhängigen
Plugins/Addons/Templates, die jQuery4CMSimple nicht verwenden, sondern
ihre eigene jQuery Bibliothek importieren, nicht funktionieren. Dieses
Problem wird nicht behoben werden (es ist ohnehin nicht möglich, es für
alle Fälle zu beheben), weil allen Entwicklern geraten wird,
ausschließlich jQuery4CMSimple in Verbindung mit ihrem jQuery basierten
Code für CMSimple\_XH zu verwenden. Daher sollten diejenigen
Erweiterungen aktualisiert werden, die sich nicht daran halten\!

### Drag & Drop im Internet Explorer

Es gibt bekannte Probleme bezüglich Drag & Drop im Internet Explorer.
Insbesondere funktioniert das Verschieben oder Kopieren einer Seite in
eine andere Seite hinein nicht in Verbindung mit dem
Standard-Admin-Menü. Die Ursache ist eine [Einschränkung in
jQuery](https://github.com/jquery/jquery/issues/3676). Verwenden Sie
entweder die "Zwischenablage"-Funktionalität, aktualisieren Sie auf Edge
(oder einen anderen Browser) oder verwenden Sie ein alternatives
Admin-Menü.

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf [Github](https://github.com/cmb69/pagemanager_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Pagemanager\_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Pagemanager\_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Pagemanager\_XH erhalten haben. Falls nicht, siehe
<http://www.gnu.org/licenses/>.

© 2011-2019 Christoph M. Becker

Danish Übersetzung © 2011-2014 Jens Maegaard  
Estnische Übersetzung © 2014 Alo Tanavots  
Französische Übersetzung © 2011-2014 Patrick Varlet  
Italienische Übersetzung © 2014 Milko Dalla Battista  
Niederländische Übersetzung © 2014 Emile Bastings  
Slovakische Übersetzung © 2011-2014 Dr. Martin Sereday  
Tschechische Übersetzung © 2011-2014 Josef Němec

## Danksagung

Dieses Plugin verwendet [jsTree](http://www.jstree.com/). Vielen Dank an
Ivan Bozhanov, den Entwickler dieser Bibliothek. jsTree verwendet
[jQuery](http://jQuery.com). Vielen Dank allen Entwicklern dieses
JavaScript Frameworks. jQuery wird CMSimple\_XH durch
[jQuery4CMSimple](http://www.cmsimple-xh.org/wiki/doku.php/extend:jquery4cmsimple)
zur Verfügung gestellt. Vielen Dank an Holger Irmler, den Entwickler
dieses Plugins.

Das *proton* Theme für jsTree ist eine leicht angepasste Version des
[jsTree Bootstrap
Themes](https://github.com/orangehill/jstree-bootstrap-theme). Danke für
die Veröffentlichung dieses schönen Themes unter MIT Lizenz.

Dieses Plugin verwendet [Font Awesome von Dave
Gandy](http://fontawesome.io/). Vielen Dank für die Bereitstellung
dieses großartigen ikonischen Schriftarten- und CSS-Toolkits unter einer
GPL freundlichen Lizenz.

Das Plugin-Icon wurde von [Everaldo Coelho](http://www.everaldo.com/)
entworfen. Vielen Dank für die Veröffentlichung unter GPL.

Vielen Dank an die Gemeinschaft im
[CMSimple\_XH-Forum](http://www.cmsimpleforum.com/) für Tipps,
Anregungen und das Testen. Besonders möchte ich *snafu* danken, dessen
frühe Rückmeldungen mich ermutigt haben, Pagemanager\_XH weiter zu
entwickeln. Vielen Dank an *Ulrich* der einen schweren Fehler (und
mehrere kleinere Probleme) fand, und der bei der Behebung desselben
half, indem er eine detailierte Beschreibung gab, was geschehen war. Und
vielen Dank an *Gert*, der einige Fehlerkorrekturen und Übersetzungen
und viele wertvolle Tipps zur Verfügung gestellt hat. Und ich möchte
ebenfalls *Martin* danken, dessen Bericht über Probleme mit dem
Kontextmenü das Hinzufügen der Werkzeugleiste inspiriert hat, sowie
*Tata*, der die "scrollende" Toolbar inspiriert hat.

Zu guter Letzt vielen Dank an [Peter Harteg](http://harteg.dk/), den
"Vater" von CMSimple, und allen Entwicklern von
[CMSimple\_XH](http://www.cmsimple-xh.org/), ohne die dieses
fantastische CMS nicht existieren würde.
