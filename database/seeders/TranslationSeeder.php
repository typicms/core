<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            [
                'id' => 1,
                'translation' => '{"fr":"En savoir plus","en":"More","nl":"Meer"}',
                'key' => 'More',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 2,
                'translation' => '{"fr":"Aller au contenu","en":"Skip to content","nl":"Naar inhoud"}',
                'key' => 'Skip to content',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 6,
                'translation' => '{"fr":"Chercher","en":"Search","nl":"Zoeken"}',
                'key' => 'Search',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 7,
                'translation' => '{"fr":"Merci pour votre message. Nous vous répondrons dans les plus brefs délais.","en":"Thank you for your message. We will get back to you as soon as possible.","nl":"Bedankt voor uw bericht. We nemen zo spoedig mogelijk contact met u op."}',
                'key' => 'message when contact form is sent',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 8,
                'translation' => '{"fr":"Merci pour votre inscription. Nous nous réjouissons de vous voir lors de l’événement.","en":"Thank you for your registration. We look forward to seeing you at the event.","nl":"Bedankt voor uw inschrijving. We kijken ernaar uit u op het evenement te zien."}',
                'key' => 'event registration message',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 9,
                'translation' => '{"fr":"Ajouter au calendrier","en":"Add to calendar","nl":"Toevoegen aan Agenda"}',
                'key' => 'Add to calendar',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 10,
                'translation' => '{"fr":"Toutes les actualités","nl":"Alle nieuws","en":"All news"}',
                'key' => 'All news',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 11,
                'translation' => '{"fr":"Tous les événements","nl":"Alle evenementen","en":"All events"}',
                'key' => 'All events',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 12,
                'translation' => '{"fr":"Partenaires","nl":"Partners","en":"Partners"}',
                'key' => 'Partners',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 13,
                'translation' => '{"fr":"Dernières actualités","nl":"Laatste Nieuws","en":"Latest news"}',
                'key' => 'Latest news',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 14,
                'translation' => '{"fr":"Prochains événements","nl":"Aankomende evenementen","en":"Upcoming events"}',
                'key' => 'Upcoming events',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 16,
                'translation' => '{"fr":"Erreur :code","nl":"Error :code","en":"Error :code"}',
                'key' => 'Error :code',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 17,
                'translation' => '{"fr":"Désolé, vous n’êtes pas autorisé à voir cette page.","nl":"Sorry, u bent niet bevoegd om deze pagina te bekijken.","en":"Sorry, you are not authorized to view this page."}',
                'key' => 'Sorry, you are not authorized to view this page.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 18,
                'translation' => '{"fr":"Souhaitez-vous visiter notre :a_openpage d’accueil:a_close ?","nl":"Wilt u onze :a_openhomepage:a_close te bezoeken?","en":"Go to our :a_openhomepage:a_close?"}',
                'key' => 'Go to our homepage?',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 19,
                'translation' => '{"fr":"Désolé, cette page n’a pas été trouvée.","nl":"Sorry, deze pagina is niet gevonden.","en":"Sorry, this page was not found."}',
                'key' => 'Sorry, this page was not found.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 20,
                'translation' => '{"fr":"Désolé, une erreur serveur est survenue.","nl":"Sorry, er is een serverfout opgetreden.","en":"Sorry, a server error occurred."}',
                'key' => 'Sorry, a server error occurred.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 21,
                'translation' => '{"fr":"Aller à la navigation","nl":"Open navigatie","en":"Open navigation"}',
                'key' => 'Open navigation',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'id' => 22,
                'translation' => '{"fr":"Veuillez s’il vous plaît corriger les erreurs ci-dessous.","en":"Please correct the errors below.","nl":"Gelieve de onderstaande fouten te corrigeren."}',
                'key' => 'message on form error',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
        ];

        DB::table('translations')->insert($translations);
    }
}
