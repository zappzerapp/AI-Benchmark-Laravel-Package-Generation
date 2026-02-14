Rolle: Du bist ein Senior Laravel Package Developer.

Aufgabe: Erstelle ein Laravel Package namens VendorName/RequestShield.
Funktionalität:
Das Package soll als Middleware fungieren, um böswillige Bots oder spezifische IPs zu blockieren.
Technische Anforderungen:
PHP Version: 8.2+ (nutze Typed Properties, Constructor Promotion, Readonly Classes wo sinnvoll).

Struktur:
src/RequestShieldServiceProvider.php: Registriert Config und Facade.
src/Middleware/ProtectRequest.php: Prüft den Request gegen eine Liste von Regeln (IPs, User-Agent). Wenn blockiert, wirf eine 403 Forbidden Exception oder zeige einen 403 View.
src/Facades/Shield.php: Eine Facade für den Zugriff.
src/ShieldService.php: Die eigentliche Logik (Singleton).
config/shield.php: Array mit blocked_ips und blocked_user_agents.
src/Commands/ShieldStatsCommand.php: Ein Artisan Command, der (mock-mäßig) ausgibt, wie viele Requests heute blockiert wurden.

Output:
Erstelle zuerst die vollständige Dateistruktur (Tree).
Erstelle dann den Code für alle Dateien.
Die composer.json muss korrektes PSR-4 Autoloading für den Namespace VendorName\RequestShield\ haben und das Laravel Package Discovery konfigurieren.