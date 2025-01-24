<?php

require_once './JsonActiveRecord.php';

class User extends JsonActiveRecord
{
    protected static string $primaryKey = 'user_id';

    protected static string $filePath = 'users.json';

    public function posts(): array
    {
        // Will return posts's user (array of Post objects)
        // based on author_user_id foreign key (from posts)
        // third parameter is local key, default to $primaryKey
        return $this->hasMany(Post::class, 'author_user_id');
    }
}

class Post extends JsonActiveRecord
{
    protected static string $primaryKey = 'post_id';

    protected static string $filePath = 'posts.json';
    public function user(): ?self
    {
        // Will return author (User object)
        // based on user_id foreign key (from posts)
        // third parameter is local key, default to $primaryKey
        return $this->belongsTo(User::class, 'user_id', 'author_user_id');
    }
}

@unlink('users.json');
@unlink('posts.json');

// Add a new user
$user = new User(['name' => 'John Doe', 'email' => 'john@example.com']);
$user->_tempPassword = 'secret123'; // Attribut will not be saved cause "_"
$user->avatar = 'https://example.com/avatar.jpg'; // Attribut will be saved
$user->save();

$user2 = new User(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
$user2->save();

// Add a new post
$post = new Post(['title' => 'Hello World', 'author_user_id' => $user->user_id]);
$post->save();

$post2 = new Post(['title' => 'Hello World 2', 'author_user_id' => $user->user_id]);
$post2->save();

$post2 = new Post(['title' => 'Hello World 3', 'author_user_id' => $user2->user_id]);
$post2->save();

$user = User::find('user_id', 1);
$posts = $user->posts();

echo "Posts from $user->name:<br/>";
foreach ($posts as $post) {
    echo $post->title . "<br/>";
}

/*
Posts from John Doe:
Hello World
Hello World 2
*/

$users = User::queryBuilder()
    ->with(['posts'])
    ->get();

foreach ($users as $user) {
    echo $user->name . " - id : {$user->user_id}<br/>";
    foreach ($user->posts as $post) {
        echo "Post : {$post->title}<br/>";
    }
}

/*
John Doe - id : 1
Post : Hello World
Post : Hello World 2
Jane Doe - id : 2
Post : Hello World 3
*/

$filteredPosts = Post::queryBuilder()
    ->where('title', '=', 'Hello World')
    ->orWhere('author_user_id', '=', 1)
    ->limit(5)
    ->offset(0)
    ->get();
echo 'Posts count: ' . count($filteredPosts) . "<br/>";

foreach ($filteredPosts as $post) {
    echo $post->title . "<br/>";
}

/*
Posts count: 2
Hello World
Hello World 2
*/

// Get oldest post saved
$post = Post::first();
echo "{$post->title}<br/>";

// Get latest post saved
$lastPost = Post::last();
echo "{$lastPost->title}<br/>";

$userToDelete = User::findPK(1);
// = User::find('user_id', 1);

if ($userToDelete) {
    $userToDelete->delete();
    echo "User deleted: " . $userToDelete->name . "<br/>";
} 

(new User(['name' => 'Alice', 'email' => 'alice@example.com']))->save();
(new User(['name' => 'Bob', 'email' => 'bob@example.com']))->save();
(new User(['name' => 'Charlie', 'email' => 'charlie@example.com']))->save();
(new User(['name' => 'David', 'email' => 'david@example.com']))->save();

$users = User::queryBuilder()
    ->startGroup() // AND group
        ->where('name', '=', 'Alice')
        ->orWhere('email', '=', 'bob@example.com')
    ->endGroup()
    ->startGroup('or')
        ->where('name', '=', 'Jane Doe')
    ->endGroup()
    ->get();

    // Query = where (name === 'Alice' || email === 'bob@example.com') || ( name === 'Jane Doe')

foreach ($users as $user) {
    echo $user->name . ' - ' . $user->email . "<br/>";
}

/*
Jane Doe - jane@example.com
Alice - alice@example.com
Bob - bob@example.com
*/

$users = User::queryBuilder()
    ->startGroup('and')
        ->where('name', '!=', 'Alice')
        ->where('email', '=', 'bob@example.com')
    ->endGroup()
    ->startGroup('or')
        ->where('user_id', '<', 3)
        //->orWhere('email', '#', '#(.*@example\.com)#')
    ->endGroup()
    ->get();

    // Query = where (name !== 'Alice' && email === 'bob@example.com') || ( user_id < 3)

    foreach ($users as $user) {
        echo $user->name . ' - ' . $user->email . "<br/>";
    }

/*
Jane Doe - jane@example.com
Bob - bob@example.com
*/

$users = User::queryBuilder()
->where('name', '#', '#(.*)e$#i')
->get();

// Regex for find string end with 'e'

foreach ($users as $user) {
    echo $user->name . "<br/>";
}

/*
Jane Doe
Alice
Charlie
*/