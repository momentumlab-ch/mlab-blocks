=== MomentumLab Blocks ===
Contributors: momentumlab
Tags: blocks, page builder
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Seiten mit Blöcken pflegen. Jeder Block hat Felder für Bild, Titel, Text und Knopf, das Aussehen kommt aus dem Theme.

== Description ==

Die Blöcke stehen im Block-Einfüger unter «MomentumLab»:

* Seitenkopf: Titel mit farbigem Teil, Einleitung, Stichworte, Knöpfe, Hintergrundbild. Darunter Platz für weitere Blöcke, z. B. ein Formular.
* Abschnitt: Rahmen (hell, dunkel oder farbig) mit Titel, darin beliebige Blöcke.
* Text mit Bild, Menükarte, Preisliste, Angebotskarten, Kachelraster, Häufige Fragen, Galerie, Kundenstimme, Kontakt, Anfrage-Band, Auswahl-Chips, Fakten, Passende Links, Knöpfe.

Ausgewählt zeigt ein Block seine Felder, sonst die Vorschau. Knöpfe können direkt zur Anfrage führen und dort das Angebot vorwählen; die Angebote kommen aus dem Feld «angebot» eines Formulars von MomentumLab Forms.

Das Markup verwendet die Klassen des Themes. Themes können Hintergrundbilder über den Filter `mlab_blocks_background_presets` anbieten (Klasse => Bezeichnung).

Filter:

* `mlab_blocks_background_presets`: Bilder aus dem Design.
* `mlab_blocks_offers`: Angebote für die Vorauswahl.
* `mlab_blocks_request_url`: Adresse der Anfrage-Seite.

== Installation ==

1. ZIP unter «Plugins › Installieren › Plugin hochladen» hochladen und aktivieren.
2. Eine Seite öffnen, auf + klicken und unter «MomentumLab» einen Block wählen.

Updates kommen aus den GitHub-Releases von momentumlab-ch/mlab-blocks.

== Changelog ==

= 1.0.0 =
* Erste Version mit 16 Blöcken.
