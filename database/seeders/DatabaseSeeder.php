<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer des utilisateurs spécifique
        $john = User::factory()->create([
            'name' => 'John Doe',
            'email'=> 'johndo@example.test',
            'password'=> bcrypt('password'),
            'is_admin' => true
        ]);
        $jane = User::factory()->create([
            'name' => 'jane Doe',
            'email'=> 'janedo@example.test',
            'password'=> bcrypt('password'),
        ]);

        // Créer des users additionnels

        $additionalUsers = User::factory(60)->create();

        // Création de groupes
        for ($i = 0; $i < 5; $i++) { 
            $group = Group::factory()->create([
                'owner_id' => $john->id,
            ]);

            $userIds = User::inRandomOrder()->limit(rand(2,5))->pluck('id')->toArray(); // On prend entre 2 et5 personnes 
            $group->users()->attach(array_unique([$john->id, ...$userIds])); // puis on les insère dans la table pivot group_user
        }

        // Créer des messages
        Message::factory(1000)->create();

        // Création des conversations
        $messages = Message::whereNull('group_id')->orderBy('created_at')->get();

        $conversations = $messages->groupBy(function (Message $message){
            return collect([$message->sender_id, $message->receiver_id])
                    ->sort()
                    ->implode('_');
        })->map( function ($groupedMessages){
            return [
                'user_id1' => $groupedMessages->first()->sender_id,
                'user_id2' => $groupedMessages->first()->receiver_id,
                'last_message_id' => $groupedMessages->last()->id,
                'created_at' => new \Carbon\Carbon(),
                'updated_at' => new \Carbon\Carbon(),
            ];
        })->values();

        Conversation::insertOrIgnore($conversations->toArray());

        $this->command->info("Seedage terminé avec succès !");
    }
}
