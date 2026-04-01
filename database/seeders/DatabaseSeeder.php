<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        $users = User::all();

        // Categories
        $categories = Category::factory(10)->create();

        // notes
        foreach ($users as $user) {
            $user->notes()->createMany(
                Note::factory(5)->make()->toArray()
            );
        }

        // zoberieme si notes (už existujú)
        $notes = Note::all();

        // Note -> categories (spojovačka)
        foreach ($notes as $note) {
            $note->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->all()
            );
        }

        // tasky cez note
        foreach ($notes as $note) {
            $note->tasks()->createMany(
                Task::factory(rand(2, 6))->make()->toArray()
            );
        }

        // note->comments, task->comments)
        $notes->each(function (Note $note) use ($users) {
            $comments = Comment::factory(rand(0, 2))->make([
                'user_id' => $users->random()->id,
            ]);

            $note->comments()->saveMany($comments);
        });

        $tasks = Task::all();
        $tasks->each(function (Task $task) use ($users) {
            $comments = Comment::factory(rand(0, 1))->make([
                'user_id' => $users->random()->id,
            ]);

            $task->comments()->saveMany($comments);
        });
    }
}
